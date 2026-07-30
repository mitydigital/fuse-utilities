<?php

namespace MityDigital\FuseUtilities\Support;

use InvalidArgumentException;
use MityDigital\FuseUtilities\Data\ImageData;
use MityDigital\FuseUtilities\Data\ImageSourceData;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Image as ImageAPI;

class Image
{
    /**
     * @var array<string, string>
     */
    protected array $urlCache = [];

    protected ?string $resolvedFit = null;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        protected Asset $asset,
        protected array $options = [],
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public static function make(Asset $asset, array $options = []): ImageData
    {
        return (new self($asset, $options))->toData();
    }

    public function toData(): ImageData
    {
        $originalUrl = $this->asset->url();
        $width = $this->asset->width();
        $height = $this->asset->height();

        if (! $originalUrl || ! $this->canManipulate()) {
            return new ImageData(
                fallback: $originalUrl ?? '',
                fallback_srcset: null,
                sizes: null,
                alt: $this->alt(),
                width: $width,
                height: $height,
                formats: [],
                sources: [],
            );
        }

        $rules = $this->responsiveRules();
        $formats = $this->formats();
        $sources = [];
        $fallbackCandidates = [];
        $largestRule = collect($rules)->filter(fn (array $rule): bool => ! $rule['native'])->sortByDesc('width')->first()
            ?? collect($rules)->sortByDesc('width')->first();

        foreach (array_reverse($rules) as $rule) {
            $sourceCandidates = [];

            foreach ($formats as $format => $mimeType) {
                $sourceCandidates[$format] = $this->candidates($rule, $format);
            }

            $sources[] = new ImageSourceData(
                media: $rule['media'],
                sizes: $rule['sizes'],
                srcset: $sourceCandidates,
            );
        }

        if (! $this->hasSize() && ! $this->hasAspectRatio()) {
            return new ImageData(
                fallback: $originalUrl,
                fallback_srcset: null,
                sizes: null,
                alt: $this->alt(),
                width: $width,
                height: $height,
                formats: $formats,
                sources: $sources,
            );
        }

        if ($this->hasSize()) {
            foreach ($rules as $rule) {
                if ($rule['native']) {
                    continue;
                }

                foreach ($this->densities() as $density) {
                    $candidateWidth = min(
                        $width,
                        (int) round($rule['width'] * $density),
                    );

                    if ($candidateWidth < 1) {
                        continue;
                    }

                    $candidate = [
                        'url' => $this->url($candidateWidth, $rule['ratio'], $rule['quality'], $this->originalFormat(), $rule['native']),
                        'descriptor' => $candidateWidth.'w',
                    ];

                    $fallbackCandidates[$candidateWidth] = $candidate;
                }
            }
        }

        if ($this->hasSize()) {
            ksort($fallbackCandidates, SORT_NUMERIC);
        }

        $fallbackWidth = min($width, $largestRule['width']);
        $fallbackHeight = $this->heightFor($fallbackWidth, $largestRule['ratio']);

        return new ImageData(
            fallback: $this->url($fallbackWidth, $largestRule['ratio'], $largestRule['quality'], $this->originalFormat(), $largestRule['native']),
            fallback_srcset: $this->hasSize() ? $this->srcsetString(array_values($fallbackCandidates)) : null,
            sizes: $this->hasSize() ? $this->sizesAttribute($rules) : null,
            alt: $this->alt(),
            width: $fallbackWidth,
            height: $fallbackHeight,
            formats: $formats,
            sources: $sources,
        );
    }

    protected function canManipulate(): bool
    {
        return in_array(strtolower($this->asset->extension()), ['jpg', 'jpeg', 'png', 'webp'], true)
            && $this->asset->width() > 0
            && $this->asset->height() > 0;
    }

    protected function hasSize(): bool
    {
        return array_key_exists('width', $this->options)
            && $this->options['width'] !== null
            && $this->options['width'] !== '';
    }

    protected function hasAspectRatio(): bool
    {
        return array_key_exists('aspect_ratio', $this->options)
            && $this->options['aspect_ratio'] !== null
            && $this->options['aspect_ratio'] !== '';
    }

    /**
     * @return array<string, string>
     */
    protected function formats(): array
    {
        $originalFormat = $this->originalFormat();
        $originalMimeType = $this->asset->mimeType() ?: $this->mimeTypeFor($originalFormat);
        $formats = ['webp' => 'image/webp'];

        if ($originalFormat !== 'webp') {
            $formats[$originalFormat] = $originalMimeType;
        }

        return $formats;
    }

    protected function originalFormat(): string
    {
        return match (strtolower($this->asset->extension())) {
            'jpeg' => 'jpg',
            default => strtolower($this->asset->extension()),
        };
    }

    protected function mimeTypeFor(string $format): string
    {
        return match ($format) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }

    /**
     * @return array<int, array{media: ?string, width: int|null, ratio: float, quality: ?int, sizes: ?string, native: bool}>
     */
    protected function responsiveRules(): array
    {
        $breakpoints = config('fuse-utilities.image.breakpoints', []);
        if (! $this->hasSize()) {
            $ratioValues = $this->responsiveRatios();
            $rules = [$this->nativeRule(null, $ratioValues['base'])];

            foreach ($breakpoints as $name => $breakpoint) {
                if (! array_key_exists($name, $ratioValues)) {
                    continue;
                }

                $rules[] = $this->nativeRule('(min-width: '.$breakpoint.'px)', $ratioValues[$name]);
            }

            return $rules;
        }

        $widthValues = $this->responsiveValues('width', null);
        $ratioValues = $this->responsiveRatios();
        $qualityValues = $this->responsiveValues('quality', null);
        $rules = [];
        $current = [
            'width' => $widthValues['base'],
            'ratio' => $ratioValues['base'],
            'quality' => $qualityValues['base'] ?? null,
        ];

        $rules[] = $widthValues['base'] === null
            ? $this->nativeRule(null, $current['ratio'])
            : [
                ...$current,
                'media' => null,
                'sizes' => $this->customSizes() ?? $current['width'].'px',
                'native' => false,
            ];

        foreach ($breakpoints as $name => $breakpoint) {
            $current['width'] = $widthValues[$name] ?? $current['width'];
            $current['ratio'] = $ratioValues[$name] ?? $current['ratio'];
            $current['quality'] = $qualityValues[$name] ?? $current['quality'];

            if (! array_key_exists($name, $widthValues)
                && ! array_key_exists($name, $ratioValues)
                && ! array_key_exists($name, $qualityValues)) {
                continue;
            }

            $rules[] = [
                ...$current,
                'media' => '(min-width: '.$breakpoint.'px)',
                'sizes' => $this->customSizes() ?? $current['width'].'px',
                'native' => false,
            ];
        }

        return $rules;
    }

    /**
     * @return array{media: ?string, width: int, ratio: float, quality: ?int, sizes: ?string, native: bool}
     */
    protected function nativeRule(?string $media, float $ratio): array
    {
        $dimensions = $this->nativeCropDimensions($ratio);

        return [
            'media' => $media,
            'width' => $dimensions['width'],
            'ratio' => $ratio,
            'quality' => null,
            'sizes' => null,
            'native' => true,
        ];
    }

    /**
     * @return array{width: int, height: int}
     */
    protected function nativeCropDimensions(float $ratio): array
    {
        $assetWidth = $this->asset->width();
        $assetHeight = $this->asset->height();
        $assetRatio = $assetHeight / $assetWidth;

        if ($ratio > $assetRatio) {
            $height = $assetHeight;
            $width = max(1, (int) round($height / $ratio));
        } else {
            $width = $assetWidth;
            $height = max(1, (int) round($width * $ratio));
        }

        return compact('width', 'height');
    }

    /**
     * @return array<string, int|null>
     */
    protected function responsiveValues(string $key, ?int $default): array
    {
        $value = $this->options[$key] ?? null;
        $values = ['base' => $default];

        if ($value === null || $value === '') {
            return $values;
        }

        foreach ($this->tokens($value) as $token) {
            [$breakpoint, $rawValue] = $this->splitResponsiveToken($token);
            $this->validateBreakpoint($breakpoint);
            $parsed = filter_var($rawValue, FILTER_VALIDATE_INT);

            if ($parsed === false || $parsed < 1) {
                throw new InvalidArgumentException("Invalid {$key} value [{$rawValue}].");
            }

            $values[$breakpoint] = $parsed;
        }

        if ($values['base'] === null && ! isset($values['base'])) {
            $values['base'] = $default;
        }

        return $values;
    }

    /**
     * @return array<string, float>
     */
    protected function responsiveRatios(): array
    {
        $value = $this->options['aspect_ratio'] ?? null;
        $assetRatio = $this->asset->width() > 0
            ? $this->asset->height() / $this->asset->width()
            : 1;
        $values = ['base' => $assetRatio];

        if ($value === null || $value === '') {
            return $values;
        }

        foreach ($this->tokens($value) as $token) {
            [$breakpoint, $rawValue] = $this->splitResponsiveToken($token);
            $this->validateBreakpoint($breakpoint);
            $parts = explode('/', $rawValue);

            if (count($parts) !== 2 || ! is_numeric($parts[0]) || ! is_numeric($parts[1]) || (float) $parts[0] <= 0 || (float) $parts[1] <= 0) {
                throw new InvalidArgumentException("Invalid aspect_ratio value [{$rawValue}].");
            }

            $values[$breakpoint] = (float) $parts[1] / (float) $parts[0];
        }

        return $values;
    }

    /**
     * @return array<int, string>
     */
    protected function tokens(mixed $value): array
    {
        return is_string($value) ? preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY) : (array) $value;
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitResponsiveToken(string $token): array
    {
        if (! str_contains($token, ':')) {
            return ['base', $token];
        }

        return explode(':', $token, 2);
    }

    protected function validateBreakpoint(string $breakpoint): void
    {
        if ($breakpoint !== 'base' && ! array_key_exists($breakpoint, config('fuse-utilities.image.breakpoints', []))) {
            throw new InvalidArgumentException("Unknown image breakpoint [{$breakpoint}].");
        }
    }

    /**
     * @return array<int, int>
     */
    protected function densities(): array
    {
        return collect($this->options['densities'] ?? config('fuse-utilities.image.densities', [1, 2]))
            ->map(fn (mixed $density): int => (int) $density)
            ->filter(fn (int $density): bool => $density >= 1)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array{width: int, ratio: float, quality: ?int, native: bool}  $rule
     * @return array<int, array{url: string, descriptor: string}>
     */
    protected function candidates(array $rule, string $format): array
    {
        if (! $this->hasSize()) {
            return [[
                'url' => $this->url($rule['width'], $rule['ratio'], null, $format, true),
                'descriptor' => '',
            ]];
        }

        if ($rule['native']) {
            return [[
                'url' => $this->url($rule['width'], $rule['ratio'], $rule['quality'], $format, true),
                'descriptor' => '',
            ]];
        }

        $candidates = [];

        foreach ($this->densities() as $density) {
            $candidateWidth = min($this->asset->width(), (int) round($rule['width'] * $density));

            if ($candidateWidth < 1 || isset($candidates[$candidateWidth])) {
                continue;
            }

            $candidates[$candidateWidth] = [
                'url' => $this->url($candidateWidth, $rule['ratio'], $rule['quality'], $format),
                'descriptor' => $candidateWidth.'w',
            ];
        }

        ksort($candidates, SORT_NUMERIC);

        return array_values($candidates);
    }

    protected function url(int $width, float $ratio, ?int $quality, string $format, bool $native = false): string
    {
        if ($native && ! $this->hasAspectRatio() && $format === $this->originalFormat()) {
            return $this->asset->url();
        }

        $params = ['fm' => $format];

        if (! $native || $this->hasAspectRatio()) {
            $params = [
                'w' => $width,
                'h' => $this->hasSize()
                    ? $this->heightFor($width, $ratio)
                    : $this->nativeCropDimensions($ratio)['height'],
                'fit' => $this->fit(),
                'fm' => $format,
            ];
        }

        if ($quality !== null) {
            $params['q'] = $quality;
        }

        $cacheKey = $format.'|'.http_build_query($params);

        return $this->urlCache[$cacheKey] ??= ImageAPI::manipulate($this->asset, $params);
    }

    protected function fit(): string
    {
        if ($this->resolvedFit !== null) {
            return $this->resolvedFit;
        }

        $fit = $this->options['fit'] ?? config('fuse-utilities.image.default_fit', 'crop_focal');

        if ($fit !== 'crop_focal') {
            return $this->resolvedFit = $fit;
        }

        $focus = $this->asset->get('focus');

        return $this->resolvedFit = $focus ? 'crop-'.$focus : 'crop';
    }

    protected function heightFor(int $width, float $ratio): int
    {
        return max(1, (int) round($width * $ratio));
    }

    protected function customSizes(): ?string
    {
        $sizes = $this->options['sizes'] ?? null;

        return is_string($sizes) && trim($sizes) !== '' ? trim($sizes) : null;
    }

    /**
     * @param  array<int, array{media: ?string, width: int|null, ratio: float, quality: ?int, sizes: ?string, native: bool}>  $rules
     */
    protected function sizesAttribute(array $rules): string
    {
        if ($this->customSizes()) {
            return $this->customSizes();
        }

        $parts = [];

        foreach (array_reverse($rules) as $rule) {
            if ($rule['media'] && ! $rule['native']) {
                $parts[] = $rule['media'].' '.$rule['width'].'px';
            }
        }

        $defaultRule = collect($rules)->first(fn (array $rule): bool => ! $rule['native']);

        if ($defaultRule) {
            $parts[] = $defaultRule['width'].'px';
        }

        return implode(', ', $parts);
    }

    /**
     * @param  array<int, array{url: string, descriptor: string}>  $candidates
     */
    protected function srcsetString(array $candidates): string
    {
        return collect($candidates)
            ->map(fn (array $candidate): string => $candidate['url'].' '.$candidate['descriptor'])
            ->implode(', ');
    }

    protected function alt(): ?string
    {
        $alt = $this->asset->get('alt');

        return is_string($alt) && $alt !== '' ? $alt : null;
    }
}
