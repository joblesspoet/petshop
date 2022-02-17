<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Dispatcher;
use Tymon\JWTAuth\Token;
use Tymon\JWTAuth\Manager;
use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Blacklist;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Events\Logout;
use Tymon\JWTAuth\Validators\PayloadValidator;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Arr;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    //
    use ThrottlesLogins;

    /**
     * @param \Illuminate\Contracts\Auth\Factory $auth
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param \Tymon\JWTAuth\Manager $jwt
     * @param \Tymon\JWTAuth\Blacklist $blacklist
     * @param \Tymon\JWTAuth\Validators\PayloadValidator $validator
     */
    public function __construct(
        Auth $auth,
        Config $config,
        Dispatcher $events,
        Manager $jwt,
        Blacklist $blacklist,
        PayloadValidator $validator
    ) {
        $this->config = $config;
        $this->events = $events;
        $this->jwt = $jwt;
        $this->blacklist = $blacklist;
        $this->validator = $validator;
        $this->guardName = $config->get('auth.guard', 'api');
        $this->guestRefreshTtl = $config->get('auth.guest_refresh_ttl');
        $this->refreshTtl = $config->get('jwt.refresh_ttl');
        $this->guard = $auth->guard($this->guardName);
        $this->setupMiddleware($auth);
    }

    /**
     * Register a new user.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param \Modules\ApplicationAuth\Http\Requests\Auth\RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function register(Hasher $hasher, RegisterRequest $request): JsonResponse
    {

        /** @var \Modules\ApplicationAuth\Entities\ApplicationUser $user */
        $user = $this->guard->user();

        /** @var \Modules\ApplicationAuth\Entities\ApplicationUser $model */
        $model = User::class;

        $attributes = Arr::except($request->validated(), ['password']);

        $user = $user ?? new $model();
        $user->fill(array_merge($attributes));

        $user->password = $hasher->make($request->input('password'));
        $user->save();

        // assign role as user...
        $user->assign(User::ROLE_USER);

        // wasRecentlyCreated property is true if it was created instead of updated
        if (!$user->wasRecentlyCreated) {
            $this->logoutExistingToken();
        } else {
            $this->events->dispatch(new Registered($user));
        }

        /** \Illuminate\Http\JsonResponse */
        return new JsonResponse(
            [
                'message' => "User created successfully.",
                'data' => UserResource::make($user)
            ],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * @param \Illuminate\Contracts\Auth\Factory $auth
     */
    protected function setupMiddleware(Auth $auth): void
    {
        $this->middleware('auth:' . $this->guardName)
            ->only('logout', 'changePassword');

        $this->middleware(
            function (Request $request, \Closure $next) use ($auth) {
                $auth->shouldUse($this->guardName);

                return $next($request);
            }
        )->except('logout', 'changePassword');
    }


    /**
     * @throws \Exception
     */
    protected function logoutExistingToken(): void
    {
        try {
            $this->setTokenRefresh();

            $originalTokenId = $this->getTokenId();

            /** @var \Modules\ApplicationAuth\Entities\ApplicationUser $user */
            $user = $this->guard->user();

            try {
                $this->guard->logout();

                if ($user && $originalTokenId) {
                    $this->removeToken($user, $originalTokenId);
                }
            }

            /** @noinspection PhpRedundantCatchClauseInspection */
            catch (JWTException $e) {
            }

            if ($user) {
                $this->cleanupTokens($user);
            }
        } finally {
            $this->setTokenRefresh(true);
        }
    }
}
