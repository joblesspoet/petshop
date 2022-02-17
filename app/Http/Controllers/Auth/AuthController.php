<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
use Carbon\Carbon;
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
     * Perform user login.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function login(Hasher $hasher, LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);
        // Verify if there have been too many attempts
        $this->verifyThrottle($request);

        try {
            // Perform login attempt
            if (!($token = $this->guard->attempt($credentials))) {
                // Throw failed login response
                throw ValidationException::withMessages(['email' => 'These credentials do not match our records.'])
                    ->status(JsonResponse::HTTP_UNAUTHORIZED);
            }

            /** @var User $user */
            $user = $this->guard->user();

            if (!$user->hasVerifiedEmail()) {

                return new JsonResponse([
                    'error' => 'Please verify your account in order to login.',
                ], JsonResponse::HTTP_NOT_ACCEPTABLE);
            }

            if ($hasher->needsRehash($user->password)) {
                $user->password = $hasher->make($request['password']);
                $user->save();
            }

            // Reset the throttle
            $this->clearLoginAttempts($request);

            $this->events->dispatch(new Login($this->guardName, $user, false));

            // Return the token
            return $this->tokenResponse($token, $user);
        } catch (\Throwable $t) {
            // On any error, increment the login attempts (this catches both validation and verification errors)
            $this->incrementLoginAttempts($request);

            // Rethrow the exception so client can handle it
            throw $t;
        }
    }

    /**
     * Invalidate the token of the currently logged in user.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function logout(): JsonResponse
    {
        /** @var \Modules\ApplicationAuth\Entities\ApplicationUser $user */
        $user = $this->guard->user();

        try {
            $this->guard->logout();

            $this->events->dispatch(new Logout($this->guardName, $user));
        }
        /** @noinspection PhpRedundantCatchClauseInspection */
        catch (JWTException $exception) {
            // Any JWT exception means that the token is invalid and thus cannot be used anymore.
        }

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


    /**
     * The email to use for the authentication throttle.
     *
     * @return string
     */
    protected function username()
    {
        return 'email';
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


    /**
     * Return the token and additional info as JSON response.
     *
     * @param string $token
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    protected function tokenResponse(string $token, User $user): JsonResponse
    {
        return new JsonResponse($this->getTokenResponseData($token, $user));
    }

    /**
     * Return the token and additional info.
     *
     * @param string $token
     * @param User $user
     * @return array
     */
    protected function getTokenResponseData(string $token, User $user): array
    {
        if (!($payload = $this->getPayload($token, false))) {
            abort(500, "Failed decoding fresh token.");
        }

        $expiresAt = $payload->get('exp');

        $refreshExpiresAt = Carbon::createFromTimestampUTC($payload->get('iat'))
            ->addMinutes($this->refreshTtl);

        $refreshExpiresAtTimestamp = $refreshExpiresAt->timestamp;

        $now = Carbon::now()->timestamp;

        /** @var \Modules\ApplicationAuth\Entities\ApplicationUser $model */
        $model = User::class;
        $user = $model::findOrFail($payload->get('sub'));


        return [
            'access_token' => $token,
            'expires_at' => $expiresAt,
            'expires_in' => $expiresAt - $now,
            'refresh_expires_at' => $refreshExpiresAtTimestamp,
            'refresh_expires_in' => $refreshExpiresAtTimestamp - $now,
            'scope' => $payload->get('scope'),

        ];
    }

    /**
     * Extract payload from token so we can use it.
     *
     * @param \Tymon\JWTAuth\Token|string $token
     * @param bool $isRefresh
     * @return \Tymon\JWTAuth\Payload|null
     */
    protected function getPayload($token, bool $isRefresh = false): ?Payload
    {
        try {
            $token = is_string($token) ? new Token($token) : $token;

            if (!($token instanceof Token)) {
                throw new \InvalidArgumentException("Expected token to be a string or an instance of " . Token::class);
            }

            return $this->jwt
                ->setRefreshFlow($isRefresh)
                ->decode($token, $isRefresh);
        } catch (JWTException $e) {
            return null;
        } finally {
            // Make sure to reset the refresh flow.
            $this->jwt->setRefreshFlow(false);
        }
    }

    /**
     * Throttle the authentication requests and lock out the user if they exceed the threshold.
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function verifyThrottle(Request $request): void
    {
        // Verify if there have been too many attempts
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }
    }
}
