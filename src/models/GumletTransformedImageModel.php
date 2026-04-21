<?php

namespace brikdigital\gumlettransformer\models;

use craft\elements\Asset;
use spacecatninja\imagerx\models\BaseTransformedImageModel;
use spacecatninja\imagerx\models\TransformedImageInterface;

class GumletTransformedImageModel extends BaseTransformedImageModel implements TransformedImageInterface
{
    public function __construct(string $imageUrl = null, Asset $source = null, array $transform = [])
    {
        if ($imageUrl !== null) {
            $this->url = $imageUrl;
        }
    }
}