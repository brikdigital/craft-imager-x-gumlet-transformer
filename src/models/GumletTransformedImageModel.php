<?php

namespace brikdigital\gumlettransformer\models;

use craft\elements\Asset;
use spacecatninja\imagerx\models\BaseTransformedImageModel;
use spacecatninja\imagerx\models\TransformedImageInterface;

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
}