<?php

namespace brikdigital\gumlettransformer\models;

use craft\base\Model;

class Settings extends Model
{
    public string $subdomain = '';
    public string $apiKey = '';
    public string $defaultProfile = '';
    public array $profiles = [];
    public bool $enableCompression = true;
    public bool $hookCpImages = false;
}