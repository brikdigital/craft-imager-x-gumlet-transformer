<?php

namespace brikdigital\gumlettransformer\models;

use craft\base\Model;

class Settings extends Model
{
    public string $baseUrl = 'https://api.gumlet.com/v1';
    public string $apiKey = '';
    public string $defaultProfile = '';
    public array $profiles = [];
    public bool $enableCompression = true;
}