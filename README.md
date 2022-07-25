[![GitHub_CI](https://github.com/gin0115/vite-manifest-parser/actions/workflows/cli.yaml/badge.svg)](https://github.com/gin0115/vite-manifest-parser/actions/workflows/cli.yaml)
[![GitHub current release](https://img.shields.io/github/release/gin0115/vite-manifest-parser)](https://github.com/gin0115/vite-manifest-parser/releases)
![](https://github.com/vite-manifest-parser/workflows/GitHub_CI/badge.svg " ")
[![codecov](https://codecov.io/gh/gin0115/vite-manifest-parser/branch/main/graph/badge.svg?token=1I2UJW717H)](https://codecov.io/gh/gin0115/vite-manifest-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gin0115/vite-manifest-parser/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/gin0115/vite-manifest-parser/?branch=main)
[![GitHub issues](https://img.shields.io/github/issues/gin0115/vite-manifest-parser)](https://github.com/gin0115/vite-manifest-parser/issues)
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)]()

A basic parser for vite manifest file, which allows for the including of vue3-cli/vite projects in php.

# Install

```bash
composer require gin0115/vite-manifest-parser
```

# Usage

To accommodate the random hash which is added to assets compiled for `vue 3` using `vite` , this library allows for the easy parsing of the required assets.

> Example Vite Manifest

```json
{
  "main.js": {
    "file": "assets/main.4889e940.js",
    "src": "main.js",
    "isEntry": true,
    "dynamicImports": ["views/foo.js"],
    "css": ["assets/main.b82dbe22.css"],
    "assets": ["assets/asset.0ab0f9cd.png"]
  },
  "views/foo.js": {
    "file": "assets/foo.869aea0d.js",
    "src": "views/foo.js",
    "isDynamicEntry": true,
    "imports": ["_shared.83069a53.js"]
  },
  "_shared.83069a53.js": {
    "file": "assets/shared.83069a53.js"
  }
}

```

You can then access the assets using the following:

```php
$manifest = new ViteManifestParser('https://www.url.tld/dist', 'path/to/project/vite.json');

// To access the main.js file url
// Just pass in the file name.
$mainJsUrl = $manifest->getEntryScriptUri('main.js');

// Returns https://www.url.tld/dist/assets/main.4889e940.js

// To access all CSS files.
$cssFiles = $manifest->getEntryCssUris('main.js');

// Returns [
//   'https://www.url.tld/dist/assets/main.b82dbe22.css'
// ];

```

# API

## ViteManifestParser()

The constructor takes 2 properties:
* assetUri - The base url of the assets.
* manifestPath - Path to the vite manifest file.

```php
$parser = new ViteManifestParser('https://www.url.tld/dist', 'path/to/project/vite.json');

// This can also be used to set the base path, based on the environment.
$assetUrl = App::environment('local')
    ? 'http://localhost:8080/dist'
    : 'https://www.url.tld/dist';

$parser = new ViteManifestParser($assetUrl, 'path/to/project/vite.json');
```

## getAssetsUri  
> @return string The base url of the assets.  

Returns the defined assetUri with any trailing slash removed.

```php
$parser = new ViteManifestParser('https://www.url.tld/dist/', 'path/to/project/vite.json');

$parser->getAssetsUri(); // Returns 'https://www.url.tld/dist'
```




## getEntryScriptUri  
> @param string $fileName - The filename of the asset  
> @return string|null - The url of the asset or null if file doesn't exist.  
