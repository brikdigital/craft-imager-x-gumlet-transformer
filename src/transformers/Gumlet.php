<?php

namespace brikdigital\gumlettransformer\transformers;

use brikdigital\gumlettransformer\GumletTransformer;
use brikdigital\gumlettransformer\models\GumletTransformedImageModel;
use brikdigital\gumlettransformer\models\Settings;
use craft\base\Component;
use craft\elements\Asset;
use spacecatninja\imagerx\exceptions\ImagerException;
use spacecatninja\imagerx\models\BaseTransformedImageModel;
use spacecatninja\imagerx\services\ImagerService;
use spacecatninja\imagerx\transformers\TransformerInterface;

class Gumlet extends Component implements TransformerInterface
{

    /**
     * @return BaseTransformedImageModel[]
     */
    public function transform(Asset|string $image, array $transforms): ?array
    {
        # We don't support URL images because they have to exist on AWS
        if (is_string($image)) return [];

        $transformedImages = [];

        foreach ($transforms as $transform) {
            $transformedImages[] = $this->getTransformedImage($image, $transform);
        }

        return $transformedImages;
    }

    private function getTransformedImage(Asset|string $image, array $transform): BaseTransformedImageModel
    {
        /** @var Settings $settings */
        $settings = GumletTransformer::$plugin->getSettings();
        $config = ImagerService::getConfig();

        if (empty($settings->subdomain)) throw new ImagerException("No subdomain defined for Gumlet transformer");

        $urlSegments = [
            $settings->customDomain !== ''
                ? "https://$settings->customDomain"
                : "https://$settings->subdomain.gumlet.io",
            $image->fs->subfolder,
            $image->path
        ];
        $url = implode('/', $urlSegments);

        $query = [];

        if (isset($transform['format'])) {
            $query['format'] = $transform['format'];
            if ($transform['format'] === 'jpg' && isset($transform['jpegQuality'])) {
                $query['quality'] = $transform['jpegQuality'];
            }
        }

        if (isset($transform['mode'])) {
            if ($transform['mode'] === 'letterbox') {
                $query['mode'] = 'fill';
                if (isset($transform['letterbox']['color'])) {
                    $query['fill'] = 'solid';
                    $query['fill-color'] = $transform['letterbox']['color'];
                }
            } else {
                $query['mode'] = $transform['mode'];
            }
        }

        if (isset($transform['ratio'])) {
            $query['mode'] = 'crop';
            $query['ar'] = $transform['ratio'];
        }
        if (isset($transform['width'])) {
            $query['width'] = $transform['width'];
        }
        if (isset($transform['height'])) {
            $query['height'] = $transform['height'];
        }
        if (isset($transform['position']) && is_string($transform['position'])) {
            [$x, $y] = explode(' ', $transform['position']);
            $query['mode'] = 'crop';
            $query['crop'] = 'focalpoint';
            $query['fp-x'] = $x;
            $query['fp-y'] = $y;
        }

        if ($settings->signingKey !== '') {
            $unsigned = implode('/', [$settings->signingKey, $image->fs->subfolder, $image->path]);
            $params = http_build_query($query);
            $hash = md5($unsigned . '?' . $params);
            $url .= '?' . $params . '&s=' . $hash;
        } else {
            $url .= '?' . http_build_query($query);
        }

        return new GumletTransformedImageModel($url, $image, $transform);
    }
}