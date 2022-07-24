<?php

declare(strict_types=1);

/**
 * Vite Manifest Parser
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Glynn Quelch <glynn@pinkcrab.co.uk>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package Gin0115\Vite Manifest Parser
 * @since 0.0.1
 */

namespace Gin0115\ViteManifestParser;

/**
 * @template ManifestFile f array{
 *     file: string,
 *     src: string,
 *     isEntry?: bool,
 *     isDynamicEntry?: bool,
 *     dynamicImports?: string[],
 *     css?: string[],
 *     assets?: string[],
 *     imports?: string[],
 *  }
 */
class ManifestParser
{
    /**
     * The URI to where the built assets will be stored.
     *
     * @var string
     */
    private $assetsUri;

    /**
     * The PATH to where the manifest file is located.
     *
     * @var string
     */
    private $manifestPath;

    /**
     * Create an instance of the parser
     *
     * @param string $assetsUri The URI to where the built assets will be stored.
     * @param string $manifestPath The PATH to where the manifest file is located.
     */
    public function __construct(string $assetsUri, string $manifestPath)
    {
        // Set the assets URI, but remove any trailing slashes.
        $this->assetsUri = rtrim($assetsUri, \DIRECTORY_SEPARATOR);
        $this->manifestPath = $manifestPath;
    }

    /**
     * Returns the base uri for the compiled assets.
     *
     * @return string
     */
    public function getAssetsUri(): string
    {
        return $this->assetsUri;
    }

    /**
     * Parse the manifest file and return an object of assets.
     *
     * @return array<string, ManifestFile>
     * @throws \Exception - Manifest file does not exist.
     * @throws \Exception - Manifest file is not valid JSON.
     * @throws \Exception - Manifest file is empty or invalid.
     */
    private function decodeManifest(): array
    {
        // Ensure manifest file exists.
        if (!file_exists($this->manifestPath)) {
            throw new \Exception('Manifest file does not exist.');
        }

        // Decode manifest file.
        $manifest = json_decode(file_get_contents($this->manifestPath), true); // @phpstan-ignore-line, checked in following if statement.

        // Ensure manifest file is valid.
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Manifest file is not valid JSON.');
        }

        // Ensure manifest file is an array.
        if (!is_array($manifest) || empty($manifest)) {
            throw new \Exception('Manifest file is empty or invalid.');
        }

        return $manifest;
    }

    /**
     * Get the assets for a vue file.
     *
     * @param string $file The path to the vue file.
     * @return array<string, ManifestFile>
     * @throws \Exception - File does not exist in manifest.
     * @throws \Exception - File assets are empty or invalid.
     */
    public function getAssetsForVueFile(string $file): array
    {
        // Get the manifest.
        $manifest = $this->decodeManifest();

        // Check the file exists.
        if (!isset($manifest[$file])) {
            throw new \Exception('File does not exist in manifest.');
        }

        // Get the assets for the file.
        $assets = $manifest[$file];

        // Ensure assets are valid.
        if (!is_array($assets) || empty($assets)) {
            throw new \Exception('File assets are empty or invalid.');
        }

        return $assets;
    }

    /**
     * Returns the entry script URI based on the assets url and file name.
     *
     * @param string $file
     * @return string|null
     */
    public function getEntryScriptUri(string $file): ?string
    {
        try {
            $assets = $this->getAssetsForVueFile($file);
        } catch (\Exception $e) {
            return null;
        }

        return \array_key_exists('file', $assets)
            ? sprintf('%s%s%s', $this->assetsUri, \DIRECTORY_SEPARATOR, $assets['file'])
            : null;
    }

    /**
     * Returns the entry scripts css files URI based on the assets url and file name.
     *
     * @param string $file
     * @return string[]
     */
    public function getEntryScriptCssUris(string $file): array
    {
        try {
            $assets = $this->getAssetsForVueFile($file);
        } catch (\Exception $e) {
            return [];
        }

        return \array_key_exists('css', $assets)
            ? \array_map(function ($css) {
                return sprintf('%s%s%s', $this->assetsUri, \DIRECTORY_SEPARATOR, $css);
            }, $assets['css'])
            : [];
    }
}
