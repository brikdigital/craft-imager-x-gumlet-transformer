<?php

namespace brikdigital\gumlettransformer\models;

use craft\elements\Asset;
use craft\models\ImageTransform;
use ReflectionClass;
use ReflectionProperty;
use spacecatninja\imagerx\models\BaseTransformedImageModel;
use spacecatninja\imagerx\models\TransformedImageInterface;
use spacecatninja\imagerx\services\ImagerService;

class GumletTransformedImageModel extends BaseTransformedImageModel implements TransformedImageInterface
{
    public array $transform = [];

    public function __construct(string $imageUrl = null, Asset $source = null, array $transform = [])
    {
        if ($imageUrl !== null) {
            $this->url = $imageUrl;
        }

        if ($source !== null) {
            $this->source = $source;
            $this->width = $source->getWidth();
            $this->height = $source->getHeight();
        }

        if ($transform !== []) {
            // Set quality
            if (!isset($transform['quality'])) {
                $ext = null;

                if ($source) {
                    $ext = $source->getExtension();
                } else if ($imageUrl) {
                    $pathParts = pathinfo($source);
                    $ext = $pathParts['extension'] ?? '';
                }

                $transform['quality'] = $this->getQualityFromExtension($ext, $transform);
            }

            // unset unsupported properties
            $reflection = new ReflectionClass(ImageTransform::class);
            $allowedKeys = array_flip(
                array_map(fn(ReflectionProperty $property) => $property->getName(), $reflection->getProperties())
            );
            $transform = array_intersect_key($transform, $allowedKeys);

            $this->transform = $transform;
            $this->width = $source->getWidth($transform);
            $this->height = $source->getHeight($transform);
        }
    }

    public function getWidth(): int
    {
        return !!$this->source ? $this->source->getWidth($this->transform) : 0;
    }

    public function getHeight(): int
    {
        return !!$this->source ? $this->source->getHeight($this->transform) : 0;
    }

    private function getQualityFromExtension(string $ext, array $transform = null): string
    {
        $config = ImagerService::getConfig();

        if ($ext == 'png') {
            $pngCompression = $config->getSetting('pngCompressionLevel', $transform);
            return max(100 - ($pngCompression * 10), 1);
        } elseif ($ext == 'webp') {
            return $config->getSetting('webpQuality', $transform);
        }

        return $config->getSetting('jpegQuality', $transform);
    }
}