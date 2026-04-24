<?php

namespace brikdigital\gumlettransformer\models;

use craft\base\Model;

class Settings extends Model
{
    public string $subdomain = '';
    public string $customDomain = '';
    public string $apiKey = '';
    public string $signingKey = '';
    public string $defaultProfile = '';
    public array $profiles = [];
    public bool $enableCompression = true;
    public bool $hookCpImages = false;

    protected function defineRules(): array
    {
        return [
            ['customDomain', 'match', 'not' => true, 'pattern' => '/:\/\//']
        ];
    }
}