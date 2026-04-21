<?php

namespace brikdigital\gumlettransformer\jobs;

use Craft;
use craft\queue\BaseJob;
use GuzzleHttp\Client;
use yii\base\InvalidConfigException;
use yii\queue\RetryableJobInterface;

class PurgeGumletCacheJob extends BaseJob implements RetryableJobInterface
{
    public string $gumletSubdomain;
    public string $apiKey;
    public string $imagePath;

    /**
     * @inheritDoc
     */
    public function execute($queue): void
    {
        if (empty($this->apiKey)) {
            throw new InvalidConfigException("Gumlet API key missing!");
        }

        $client = new Client();
        $client->post("https://api.gumlet.com/v1/purge/$this->gumletSubdomain", [
            'body' => json_encode(['paths' => [$this->imagePath]]),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
        ]);
    }

    protected function defaultDescription(): ?string
    {
        return "Purging Gumlet cache for $this->imagePath";
    }

    public function getTtr()
    {
        return 60;
    }

    public function canRetry($attempt, $error)
    {
        return $attempt < 5;
    }
}