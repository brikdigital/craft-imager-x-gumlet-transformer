<?php

namespace brikdigital\gumlettransformer\transformers;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use Psr\Log\LogLevel;
use spacecatninja\imagerx\models\BaseTransformedImageModel;
use spacecatninja\imagerx\transformers\TransformerInterface;

class Gumlet extends Component implements TransformerInterface
{

    /**
     * @return BaseTransformedImageModel[]
     */
    public function transform(Asset|string $image, array $transforms): ?array
    {
        Craft::getLogger()->log("Asset caught: $image->filename ($image->id)", LogLevel::INFO, 'gumlet-transformer');
    }
}