<?php

/**
 * Unit tests for the ManifestParser class.
 */

namespace Gin0115\ViteManifestParser\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Gin0115\ViteManifestParser\ManifestParser;

class TestManifestParser extends TestCase
{

    /** 
     * Creates a temp file with the passed contents.
     * 
     * @param string $name The name to the temp file.
     * @param string $contents The contents of the temp file.
     * @return string The name to the temp file.
     */
    private function createTempFile(string $name, string $content): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), $name);
        file_put_contents($tempFile, $content);
        return $tempFile;
    }

    /**
     * Removes a temp file based on its name.
     * 
     * @param string $name The name of the temp file.
     */
    private function removeTempFile(string $name): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), $name);
        unlink($tempFile);
    }

    /** @testdox It should be possible to create a parser and get access to the build uri */
    public function testCreateParser()
    {
        $parser = new ManifestParser('http://example.com/assets', 'tests/fixtures/manifest.json');
        $this->assertEquals('http://example.com/assets', $parser->getAssetsUri());
    }

    /** @testdox When passing the assets path, any trailing slash should be removed. */
    public function testRemoveTrailingSlash()
    {
        $parser = new ManifestParser('http://example.com/assets/', 'tests/fixtures/manifest.json');
        $this->assertEquals('http://example.com/assets', $parser->getAssetsUri());
    }

    /** @testdox An exception should be thrown if attempting to get asset details from a file which doesn't exist. */
    public function testGetAssetDetailsFromNonExistentFile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Manifest file does not exist.');
        $parser = new ManifestParser('http://example.com/assets/', 'tests/fixtures/non-existent-manifest.json');
        $parser->getAssetsForVueFile('non-existent-file.js');
    }

    /** @testdox An exception should be thrown if any errors occurs decoding file contents from JSON. */
    public function testDecodeJsonError()
    {
        $file = $this->createTempFile('manifest-invalid.json', '{');
        $parser = new ManifestParser('http://example.com/assets/', $file);

        try {
            $parser->getAssetsForVueFile('main.js');
        } catch (\Throwable $th) {
            $this->assertInstanceOf(Exception::class, $th);
            $this->assertEquals('Manifest file is not valid JSON.', $th->getMessage());
        }

        $this->removeTempFile('manifest-invalid.json');
    }

    /** @testdox An exception should be thrown if the manifest file is empty or invalid. */
    public function testEmptyManifest()
    {
        $file = $this->createTempFile('manifest-empty.json', '{}');
        $parser = new ManifestParser('http://example.com/assets/', $file);

        try {
            $parser->getAssetsForVueFile('main.js');
        } catch (\Throwable $th) {
            $this->assertInstanceOf(Exception::class, $th);
            $this->assertEquals('Manifest file is empty or invalid.', $th->getMessage());
        }

        $this->removeTempFile('manifest-empty.json');
    }

    /** @testdox An exception should be thrown if the decoded manifest is not an array. */
    public function testManifestNotArray()
    {
        $file = $this->createTempFile('manifest-not-array.json', '"main.js"');
        $parser = new ManifestParser('http://example.com/assets/', $file);

        try {
            $parser->getAssetsForVueFile('main.js');
        } catch (\Throwable $th) {
            $this->assertInstanceOf(Exception::class, $th);
            $this->assertEquals('Manifest file is empty or invalid.', $th->getMessage());
        }

        $this->removeTempFile('manifest-not-array.json');
    }

    /** @testdox An exception be thrown attempting to get the assets for a file which doesn't exist on a valid manifest file. */
    public function testGetAssetDetailsForNonExistentFile()
    {
        $file = $this->createTempFile('manifest-valid.json', '{"main.js": "main.js"}');
        $parser = new ManifestParser('http://example.com/assets/', $file);

        try {
            $parser->getAssetsForVueFile('non-existent-file.js');
        } catch (\Throwable $th) {
            $this->assertInstanceOf(Exception::class, $th);
            $this->assertEquals('File does not exist in manifest.', $th->getMessage());
        }

        $this->removeTempFile('manifest-valid.json');
    }

    /** @testdox An exception be thrown if a files assets are not an array */
    public function testFileAssetsNotArray()
    {
        $file = $this->createTempFile('manifest-valid.json', '{"main.js": "main.js"}');
        $parser = new ManifestParser('http://example.com/assets/', $file);

        try {
            $parser->getAssetsForVueFile('main.js');
        } catch (\Throwable $th) {
            $this->assertInstanceOf(Exception::class, $th);
            $this->assertEquals('File assets are empty or invalid.', $th->getMessage());
        }

        $this->removeTempFile('manifest-valid.json');
    }

    /** @testdox An exception be thrown if a files assets are empty */
    public function testFileAssetsEmpty()
    {
        $file = $this->createTempFile('manifest-valid.json', '{"main.js": []}');
        $parser = new ManifestParser('http://example.com/assets/', $file);

        try {
            $parser->getAssetsForVueFile('main.js');
        } catch (\Throwable $th) {
            $this->assertInstanceOf(Exception::class, $th);
            $this->assertEquals('File assets are empty or invalid.', $th->getMessage());
        }

        $this->removeTempFile('manifest-valid.json');
    }

    /** @testdox It should be possible to access the file details for any file which is included in the manifest */
    public function testGetAssetDetails()
    {
        $manifest = [
            'file.js' => [
                'file' => 'some/path.file',
                'css' => [
                    'assets/file.css',
                    'assets/file2.css',
                ]
            ]
        ];


        $file = $this->createTempFile('testGetAssetDetails.json', \json_encode($manifest));

        $parser = new ManifestParser('http://example.com/', $file);
        $details = $parser->getAssetsForVueFile('file.js');

        $this->assertEquals('some/path.file', $details['file']);
        $this->assertContains('assets/file.css', $details['css']);
        $this->assertContains('assets/file2.css', $details['css']);

        $this->removeTempFile('testGetAssetDetails.json');
    }

    /** @testdox it should be possible to get the entry scripts URI which takes into account the asset uri passed. */
    public function testGetEntryScriptsUri()
    {
        $manifest = [
            'file.js' => [
                'file' => 'some/path.file',
                'css' => [
                    'assets/file.css',
                    'assets/file2.css',
                ]
            ]
        ];

        $file = $this->createTempFile('testGetEntryScriptsUri.json', \json_encode($manifest));
        $parser = new ManifestParser('http://example.com/assets/', $file);
        $this->assertEquals('http://example.com/assets/some/path.file', $parser->getEntryScriptUri('file.js'));

        $this->removeTempFile('testGetEntryScriptsUri.json');
    }

    /** @testdox When attempting to get the entry file uri, if the manifest doesn't contain a file property, null should be returned */
    public function testGetEntryFilePathNull()
    {
        $manifest = [
            'file.js' => [
                'css' => [
                    'assets/file.css',
                    'assets/file2.css',
                ]
            ]
        ];

        $file = $this->createTempFile('testGetEntryFilePathNull.json', \json_encode($manifest));
        $parser = new ManifestParser('http://example.com/assets/', $file);
        $this->assertNull($parser->getEntryScriptUri('file.js'));

        $this->removeTempFile('testGetEntryFilePathNull.json');
    }

    /** @testdox When attempting to get the entry file uri, if any exception is thrown, null should be returned */
    public function testGetEntryFilePathException()
    {
        $parser = new ManifestParser('http://example.com/assets/', 'i dont exist');
        $this->assertNull($parser->getEntryScriptUri('file.js'));
    }

    /** @testdox it should be possible to get the array of css URI's which takes into account the asset uri passed. */
    public function testGetCssUris()
    {
        $manifest = [
            'file.js' => [
                'file' => 'some/path.file',
                'css' => [
                    'assets/file.css',
                    'assets/file2.css',
                ]
            ]
        ];

        $file = $this->createTempFile('testGetCssUris.json', \json_encode($manifest));
        $parser = new ManifestParser('http://example.com', $file);
        $this->assertContains('http://example.com/assets/file.css', $parser->getEntryScriptCssUris('file.js'));
        $this->assertContains('http://example.com/assets/file2.css', $parser->getEntryScriptCssUris('file.js'));

        $this->removeTempFile('testGetCssUris.json');
    }

    /** @testdox When attempting to get the array of css URI's, if the manifest doesn't contain a file property, an empty should be returned */
    public function testGetCssUrisEmpty()
    {
        $manifest = [
            'file.js' => [
                'css' => [
                    'assets/file.css',
                    'assets/file2.css',
                ]
            ]
        ];

        $file = $this->createTempFile('testGetCssUrisEmpty.json', \json_encode($manifest));
        $parser = new ManifestParser('http://example.com', $file);
        $this->assertEmpty($parser->getEntryScriptCssUris('not.js'));

        $this->removeTempFile('testGetCssUrisEmpty.json');
    }

    /** @testdox When attempting to get the array of css URI's, if any exception is thrown, empty array should be returned */
    public function testGetCssUrisException()
    {
        $parser = new ManifestParser('http://example.com', 'i dont exist');
        $this->assertEmpty($parser->getEntryScriptCssUris('file.js'));
    }


}
