<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Image\Manipulations;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\HttpFoundation\File\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data;

trait MediaManagerTrait
{
    /**
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
     * @param string|null $conversion
     * @return string
     */
    protected function getUrlForMedia(?Media $media, string $conversion = ''): ?string
    {
        if ($media === null) {
            return null;
        }

        $diskDriverName = $conversion
            ? $media->getConversionsDiskDriverName()
            : $media->getDiskDriverName();

        if ($diskDriverName === 's3') {
            return $media->getTemporaryUrl(Carbon::now()->addHour(), $conversion);
        }

        return $media->getUrl($conversion);
    }

    /**
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|\SplFileInfo|string|null $value
     * @param string $collectionName
     * @param string|null $currentUrl
     * @return $this
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data
     */
    public function processMedia($value, string $collectionName, ?string $currentUrl)
    {
        if (is_array($value) && count($value) === 0) {
            return;
        }

        if ($value === $currentUrl) {
            return $this;
        }

        if ($value === null) {
            optional($this->getMedia($collectionName))->delete();

            return $this;
        }

        if (is_array($value) && count($value) > 0) {
            foreach ($value as $object) {
                $this->manageMedia($object, $collectionName);
            }
            return;
        }

        $this->manageMedia($value, $collectionName);
    }

    /**
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|\SplFileInfo|string|null $value
     * @param string $collectionName
     * @param string|null $currentUrl
     * @return $this
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data
     */
    private function manageMedia($value, string $collectionName)
    {

        if ($value instanceof Media) {
            $this->addMediaFromDisk($value->getPath(), $value->getDiskDriverName())
                ->preservingOriginal()
                ->withProperties($value->toArray())
                ->toMediaCollection($collectionName);

            return $this;
        }


        //Move file from temp to permanent in case of presigned url s3
        if ((!$value instanceof File || !$value instanceof UploadedFile) &&
            !blank($value) && Str::contains($value, 'tmp') && !is_string($value)
        ) {
            // if (!file_exists($value)) {
            //     abort(404, __('image path not exist'));
            // }
            $this->addMediaFromDisk($value, config('filesystems.cloud'))
                ->preservingOriginal()
                ->toMediaCollection($collectionName);

            return $this;
        }

        if (is_string($value)) {

            if (Str::startsWith($value, ['http://', 'https://'])) {
                $this->addMediaFromUrl($value, ['image/jpeg', 'image/png', 'image/webp'])
                    ->toMediaCollection($collectionName);
                return $this;
            }

            if (Str::startsWith($value, 'data:')) {
                if (!Str::contains($value, ';base64')) {
                    if (!preg_match('#^data:([^,]+),(.*)$#', $value, $matches)) {
                        throw InvalidBase64Data::create();
                    }

                    $value = 'data:' . $matches[1] . ';base64,' . base64_encode($matches[2]);
                }

                if (!preg_match('#^data:([^;]+);base64,[-A-Za-z0-9+/]+={0,2}$#', $value, $matches)) {
                    throw InvalidBase64Data::create();
                }

                $mime = $matches[1];
                $extension = Arr::first(
                    MimeTypes::getDefault()
                        ->getExtensions($mime)
                ) ?: 'bin';

                $this->addMediaFromBase64($value, ['image/jpeg', 'image/png', 'image/webp'])
                    ->usingFileName(sha1($value) . '.' . $extension)
                    ->toMediaCollection($collectionName);

                return $this;
            }

            throw new \InvalidArgumentException("Expected a url, data uri or file");
        }

        if ($value instanceof \SplFileInfo && !($value instanceof File || $value instanceof UploadedFile)) {
            $value = new File($value->getRealPath());
        }

        if ($value instanceof File || $value instanceof UploadedFile) {
            $adder = $this->addMedia($value);

            if (!($value instanceof UploadedFile)) {
                $adder->preservingOriginal();
            }

            $adder->toMediaCollection($collectionName);

            return $this;
        }

        throw new \InvalidArgumentException("Expected another media record, a url, a data uri or a file");
    }

    /**
     * @param array<string, bool>|string[] $mediaCollection
     */
    protected function handleRegisterMediaCollections(array $mediaCollection): void
    {
        if (blank($mediaCollection)) return;

        foreach ($mediaCollection as $key => $value) {

            $collection = $this->addMediaCollection($value['collection'])
                ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->withResponsiveImages();

            if ($value['limit'] === 1) {
                $collection->singleFile();
            } else {
                $collection->onlyKeepLatest($value['limit']);
            }
        }
    }

    /**
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
     */
    protected function handleRegisterMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Manipulations::FIT_CROP, 168, 105)
            ->keepOriginalImageFormat()
            ->quality(80)
            ->orientation(Manipulations::ORIENTATION_AUTO)
            ->optimize();
        // ->withResponsiveImages();

        $this->addMediaConversion('large')
            ->fit(Manipulations::FIT_CROP, 560, 350)
            ->keepOriginalImageFormat()
            ->quality(80)
            ->orientation(Manipulations::ORIENTATION_AUTO)
            ->optimize();
        // ->withResponsiveImages();

        $this->addMediaConversion('xlarge')
            ->fit(Manipulations::FIT_CROP, 1120, 700)
            ->keepOriginalImageFormat()
            ->quality(80)
            ->orientation(Manipulations::ORIENTATION_AUTO)
            ->optimize();
        // ->withResponsiveImages();

        $this->addMediaConversion('full')
            ->keepOriginalImageFormat()
            ->quality(80)
            ->orientation(Manipulations::ORIENTATION_AUTO)
            ->optimize();
        // ->withResponsiveImages();
    }

    /**
     * Delete media for model
     *
     * @param Request $request
     *
     * @return void
     */
    public function mediaToDelete(Request $request): void
    {
        if ($request->has('delete_media') && !blank($deleteMedia = $request->input('delete_media'))) {
            if (is_numeric($deleteMedia) && $deleteMedia > 0) {
                $this->deleteMedia($deleteMedia);
            }

            if (is_array($deleteMedia) && count($deleteMedia) > 0) {
                collect($deleteMedia)->map(fn (int $item) => $this->deleteMedia($item));
            }
        }
    }

    /**
     * Delete media for model
     *
     * @param Request $request
     *
     * @return void
     */
    public function modelMediaToDelete($deleteMediaID, $currentMedia): void
    {

        if (count($deleteMediaID) > 0) {

            foreach ($currentMedia as $SingleMedia) {
                if (in_array($SingleMedia->id, $deleteMediaID)) {
                    $this->deleteMedia($SingleMedia);
                };
            }
        }
    }
}
