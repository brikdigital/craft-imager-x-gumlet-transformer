<?php

use brikdigital\gumlettransformer\models\Settings;
use craft\base\Event;
use craft\base\Model;
use craft\base\Plugin;
use craft\log\MonologTarget;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use spacecatninja\imagerx\events\RegisterTransformersEvent;
use spacecatninja\imagerx\ImagerX;

use brikdigital\gumlettransformer\transformers\Gumlet;

class GumletTransformer extends Plugin {
    public static GumletTransformer $plugin;

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
            'name' => 'gumlet-transformer',
            'categories' => ['gumlet-transformer'],
            'level' => LogLevel::ERROR,
            'logContext' => false,
            'formatter' => new LineFormatter(
                "%datetime% [%channel%.%level_name%] [%extra.yii_category%] %message%\n\n",
                allowInlineLineBreaks: true,
            )
        ]);

        Event::on(
            ImagerX::class,
            ImagerX::EVENT_REGISTER_TRANSFORMERS,
            static function (RegisterTransformersEvent $event) {
                $event->transformers['gumlet'] = Gumlet::class;
            }
        );
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }
}
