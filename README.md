# Imager X Gumlet Transformer

## Installation

Simply run:

```
composer require brikdigital/craft-imager-x-gumlet-transformer && php craft plugin/install imager-x-gumlet-transformer
```

## Setting up

Set your Imager X transformer to `gumlet` in `imager-x.php`.

Then configure the plugin from the settings page in the control panel.
You'll be required to retrieve some stuff from Gumlet's dashboard:
- Gumlet subdomain (can be found in `Images > Sources`)
- API key (can be found under `Developers > API Keys`)

Optionally, you can enable image URL signing by entering a signing key. (found in `Images > [source] > Security`)

Custom domain support can be enabled by providing the domain you've set in Gumlet (under `Images > [source] > Custom Domain`).
Note that this domain must **not** contain `https://` or anything of the like. Just something like `example.com`.
