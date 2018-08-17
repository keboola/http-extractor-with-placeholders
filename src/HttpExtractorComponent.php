<?php

declare(strict_types=1);

namespace Keboola\HttpExtractor;

use GuzzleHttp\Psr7\Uri;
use Keboola\Code\Exception\UserScriptException;
use Keboola\Component\BaseComponent;
use Keboola\Component\UserException;
use Keboola\HttpExtractor\Code\Builder;
use Keboola\HttpExtractor\Config\ConfigDefinition;
use Psr\Http\Message\UriInterface;

class HttpExtractorComponent extends BaseComponent
{
    public const ALLOWED_PLACEHOLDER_METHODS = [
        'md5',
        'sha1',
        'time',
        'date',
        'strtotime',
        'strtodate',
        'base64_encode',
        'hash_hmac',
        'sprintf',
        'concat',
    ];

    public static function joinPathSegments(string $firstPart, string $secondPart): string
    {
        $separator = '/';

        $baseWithoutTrailing = rtrim($firstPart, $separator);
        $pathWithoutLeading = ltrim($secondPart, $separator);

        return $baseWithoutTrailing . $separator . $pathWithoutLeading;
    }

    protected function getConfigDefinitionClass(): string
    {
        return ConfigDefinition::class;
    }

    protected function getConfigClass(): string
    {
        return Config::class;
    }

    public function run(): void
    {
        /** @var Config $config */
        $config = $this->getConfig();
        $httpExtractor = new HttpExtractor(new Client(), $config->getClientOptions());

        $uri = new Uri($this->getDownloadUrl());
        $httpExtractor->extract($uri, $this->getDestination($uri));
    }

    private function getDestination(UriInterface $uri): string
    {
        return $this->getDataDir() . '/out/files/' . basename($uri->getPath());
    }

    private function getDownloadUrl(): string
    {
        /** @var Config $config */
        $config = $this->getConfig();

        $url = self::joinPathSegments($config->getBaseUrl(), $config->getPath());

        $builder = new Builder(self::ALLOWED_PLACEHOLDER_METHODS);
        foreach ($config->getPlaceholders() as $placeholder => $builderConfig) {
            try {
                $url = str_replace($placeholder, $builder->run((object) $builderConfig), $url);
            } catch (UserScriptException $e) {
                throw new UserException(sprintf(
                    "Error on processing placeholder '%s': %s",
                    $placeholder,
                    $e->getMessage()
                ));
            }
        }

        return $url;
    }
}
