<?php

namespace brikdigital\gumlettransformer;

use brikdigital\gumlettransformer\helpers\GumletHelpers;
use brikdigital\gumlettransformer\jobs\PurgeGumletCacheJob;
use brikdigital\gumlettransformer\models\Settings;
use Craft;
use craft\base\Event;
use craft\base\Model;
use craft\base\Plugin;
use craft\elements\Asset;
use craft\events\DefineAssetUrlEvent;
use craft\events\ModelEvent;
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

        Craft::$app->log->targets[] = new MonologTarget([
            'name' => 'gumlet-transformer',
            'categories' => ['gumlet-transformer'],
            'level' => LogLevel::INFO,
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

        if ($this->getSettings()->hookCpImages) {
            Event::on(
                Asset::class,
                Asset::EVENT_BEFORE_DEFINE_URL,
                function (DefineAssetUrlEvent $event) {
                    if (!Craft::$app->request->isCpRequest) return;

                    /** @var Asset $asset */
                    $asset = $event->sender;
                    $transform = $event->transform;

                    $baseUrl = $asset->volume->fs->subfolder . '/' . $asset->path;
                    $params = GumletHelpers::buildParams($transform);

                    if (empty($params)) {
                        $event->url = $baseUrl;
                        return;
                    }

                    $event->url =  "https://{$this->getSettings()->subdomain}.gumlet.io/$baseUrl?" . http_build_query($params);
                    $event->handled = true;
                }
            );
        }

        Event::on(
            Asset::class,
            Asset::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                /** @var Asset $asset */
                $asset = $event->sender;
                /** @var Settings $settings */
                $settings = $this->getSettings();

                if ($asset->kind !== 'image') {
                    return;
                }

                $imagePath = $asset->volume->fs->subfolder . '/' . $asset->path;
                Craft::$app->queue->push(new PurgeGumletCacheJob([
                    'gumletSubdomain' => $settings->subdomain,
                    'apiKey' => $settings->apiKey,
                    'imagePath' => $imagePath,
                ]));
            }
        );
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->rendertemplate(
            "$this->handle/settings",
            ['settings' => $this->getSettings()]
        );
    }
}
