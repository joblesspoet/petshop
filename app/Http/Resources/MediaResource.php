<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

/**
 * @mixin \Spatie\MediaLibrary\MediaCollections\Models\Media
 */
class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => $this->getUrlFor(),
            // 'responsive' => $this->generateResponsiveImageUrls(''),
            'conversions' => $this->generateConversions(),
            'expires_at' => $this->when($this->useTemporaryUrl(), fn() => Carbon::now()->addHour()->timestamp),
            'created_at' => optional($this->created_at)->toISO8601ZuluString(),
            'updated_at' => optional($this->updated_at)->toISO8601ZuluString(),
        ];
    }

    /**
     * @return array<string, array<string, string|array<int,string>|\Illuminate\Http\Resources\MissingValue>>|\Illuminate\Http\Resources\MissingValue
     */
    protected function generateConversions()
    {
        $conversions = Collection::make($this->getMediaConversionNames())
            ->filter(function (string $name) { return $this->hasGeneratedConversion($name); })
            ->mapWithKeys(
                function (string $name) {
                    return [
                        $name => [
                            'url' => $this->getUrlFor($name),
                            // 'responsive' => $this->generateResponsiveImageUrls($name),
                        ],
                    ];
                }
            )
            ->all();

        if (empty($conversions)) {
            return new MissingValue();
        }

        return $conversions;
    }

    /**
     * @param string $conversion
     * @return array<int, string>|\Illuminate\Http\Resources\MissingValue
     */
    protected function generateResponsiveImageUrls(string $conversion)
    {
        if (!$this->hasResponsiveImages($conversion)) {
            return new MissingValue();
        }

        $images = $this->responsiveImages($conversion);

        $result = collect($images->files)
            ->map(
                fn(ResponsiveImage $image) => [
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'url' => $this->getResponsiveUrlFor($image),
                ]
            );

        return [
            'placeholder' => $this->when($placeholder = $images->getPlaceholderSvg(), fn() => $placeholder),
            'sizes' => $result->all(),
        ];
    }

    /**
     * @param string|null $conversion
     * @return string
     */
    protected function getUrlFor(string $conversion = ''): string
    {
        if ($this->useTemporaryUrl($conversion)) {
            return $this->getTemporaryUrl(Carbon::now()->addHour(), $conversion);
        }

        return $this->getUrl($conversion);
    }

    /**
     * @param \Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage $image
     * @return string
     */
    protected function getResponsiveUrlFor(ResponsiveImage $image): string
    {
        $conversionName = '';
        if ($image->generatedFor() !== 'media_library_original') {
            $conversionName = $image->generatedFor();
        }

        if (!$this->useTemporaryUrl($conversionName)) {
            return $image->url();
        }

        $diskName = $conversionName ? ($this->conversions_disk ?? $this->disk) : $this->disk;

        $prefix = PathGeneratorFactory::create()
            ->getPathForResponsiveImages($this->resource);

        return Storage::disk($diskName)
            ->temporaryUrl(
                rtrim($prefix, '/') . '/' . $image->fileName,
                Carbon::now()->addHour()
            );
    }

    protected function useTemporaryUrl(string $conversion = ''): bool
    {
        $diskDriverName = $conversion
            ? $this->getConversionsDiskDriverName()
            : $this->getDiskDriverName();

        return $diskDriverName === 's3';
    }
}
