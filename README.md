[![GitHub_CI](https://github.com/gin0115/vite-manifest-parser/actions/workflows/cli.yaml/badge.svg)](https://github.com/gin0115/vite-manifest-parser/actions/workflows/cli.yaml)
![GitHub release (latest SemVer including pre-releases)](https://img.shields.io/github/v/release/gin0115/vite-manifest-parser?include_prereleases)
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

## getAssetsForVueFile  

> @param string $fileName The name of the vue file.  
> @return array<string, string|string[]> The assets for the vue file.  
> @throws \Exception - File does not exist in manifest.  
> @throws \Exception - File assets are empty or invalid.  

<details>
<summary> File Asset properties </summary>

* **file**: *string*  
* **src**: *string*  
* **isEntry**?: *bool*  (optional)  
* **isDynamicEntry**?: *bool*  (optional)
* **dynamicImports**?: *string[]*   (optional)
* **css**?: *string[]*   (optional)
* **assets**?: *string[]*   (optional)
* **imports**?: *string[]*  (optional)
   
</details>

Returns an array of all details defined in the manifest for the given vue file.

```php

$parser = new ViteManifestParser('https://www.url.tld/dist', 'path/to/project/vite.json');

$fileDetails = $parser->getAssetsForVueFile('main.js');

/* 
 * "file => "assets/main.4889e940.js",
 * "src => "main.js",
 * "isEntry => true,
 * "dynamicImports => ["views/foo.js"],
 * "css => ["assets/main.b82dbe22.css"],
 * "assets => ["assets/asset.0ab0f9cd.png"]
 */

```

> This will throw exceptions if there is an issue with the manifest file it self or the required file from manifest doesn't exist.

## getEntryScriptUri  

> @param string $fileName - The filename of the asset  
> @return string|null - The url of the asset or null if file doesn't exist.  

This will return just the main JS file uri, this will be prepended with the assetUri.

```php
$parser = new ViteManifestParser('https://www.url.tld/dist', 'path/to/project/vite.json');

$mainJsUrl = $parser->getEntryScriptUri('main.js');

// Returns https://www.url.tld/dist/assets/main.4889e940.js
```

> Unlike `getAssetsForVueFile()` , this will not throw exceptions if the file doesn't exist and will just return null.

## getEntryCssUris

> @param string $fileName - The filename of the asset  
> @return  string[] - The urls of the css assets.  

This will return all css files that are defined for the entry file. This will be prepended with the assetUri.

```php
$parser = new ViteManifestParser('https://www.url.tld/dist', 'path/to/project/vite.json');

$cssFiles = $parser->getEntryCssUris('main.js');

// Returns [
//   'https://www.url.tld/dist/assets/main.b82dbe22.css'
// ];
```

> Unlike `getAssetsForVueFile()` , this will not throw exceptions if the file doesn't exist and will just return an empty array.

# Change log
* 0.1.0 - Initial release.
# Contributing

If you would like to contribute to this project, please open an issue or pull request. All pull requests must pass the testing suite of PHPUnit, PHPStan and PHP Code Sniffer. To run these tests please run the following.
  
* `composer coverage` - Runs the PHPUnit test cases and will create a HTML coverage report (if you have a valid coverage driver installed)
* `composer test` - Runs the PHPUnit test cases without the coverage report.
* `composer sniff` - Runs PHP Code Sniffer on the project.
* `composer fixer` - Runs PHPCBF against the files to the defined rules (PSR12).
* `composer analyse` - Runs the PHPStan ruleset for the project
* `composer all` - Runs all of the above and is what is run as part of the GH Action pipeline.

All code must pass all of these suites against php versions `7.2` , `7.3` , `7.4` , `8.0` & `8.1` . On both windows and linux operating systems. Please note the Windows version runs less tests.
