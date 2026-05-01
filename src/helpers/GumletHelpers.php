<?php

namespace brikdigital\gumlettransformer\helpers;

use brikdigital\gumlettransformer\GumletTransformer;
use brikdigital\gumlettransformer\models\Settings;
use craft\elements\Asset;
use craft\models\ImageTransform;

class GumletHelpers
{
    public static function getSignedUrl(string $url, Asset $image, array $query): string
    {
        /** @var Settings $settings */
        $settings = GumletTransformer::$plugin->getSettings();

        $unsigned = implode('/', [$settings->signingKey, $image->fs->subfolder, $image->path]);
        $params = http_build_query($query);
        $hash = md5($unsigned . '?' . $params);
        return $url . '?' . $params . '&s=' . $hash;
    }

    /**
     * @see https://github.com/akbansa/craft-gumlet-imagetransformer/blob/15e40984ce22ddbb701ea40e833f25f20816dc14/src/services/Gumlet.php#L185
     */
    public static function buildParams(ImageTransform|array|null $transform, array $additionalParams = []): array
    {
        $params = [];

        // Normalize array transforms into ImageTransform objects and merge extra params
        if (is_array($transform)) {
            $validTransformProps = [
                'width',
                'height',
                'quality',
                'format',
            ];

            $transformProps = [];
            $extractedParams = [];

            foreach ($transform as $key => $value) {
                if (in_array($key, $validTransformProps, true)) {
                    $transformProps[$key] = $value;
                } else {
                    $extractedParams[$key] = $value;
                }
            }

            $additionalParams = array_merge($extractedParams, $additionalParams);
            $transform = new ImageTransform($transformProps);
        }

        if ($transform) {
            // Width
            if (!empty($transform->width)) {
                $params['w'] = (int)$transform->width;
            }

            // Height
            if (!empty($transform->height)) {
                $params['h'] = (int)$transform->height;
            }

            // Quality
            if (!empty($transform->quality)) {
                $params['q'] = (int)$transform->quality;
            }

            // Format
            if (!empty($transform->format)) {
                $mappedFormat = self::mapFormat($transform->format);
                if ($mappedFormat !== null) {
                    $params['f'] = $mappedFormat;
                }
            }

            // Mode
            if (!empty($transform->mode)) {
                $params['mode'] = (string)$transform->mode;
            }
        }

        // Merge additional Gumlet-specific parameters
        if (!empty($additionalParams)) {
            $params = array_merge($params, $additionalParams);
        }

        // Filter out null and empty string values
        return array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * @see https://github.com/akbansa/craft-gumlet-imagetransformer/blob/15e40984ce22ddbb701ea40e833f25f20816dc14/src/services/Gumlet.php#L271
     */
    protected static function mapFormat(?string $format): ?string
    {
        if (!$format) {
            return null;
        }

        $map = [
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            'avif' => 'avif',
        ];

        $format = strtolower($format);

        return $map[$format] ?? $format;
    }
}