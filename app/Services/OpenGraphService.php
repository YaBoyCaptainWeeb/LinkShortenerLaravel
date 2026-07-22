<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class OpenGraphService
{
    public function fetchMetadata(string $url) : array
    {
        $default = [
            'title' => null,
            'description' => null,
            'image' => null,
            'url' => $url
        ];

        try {
            $client = new Client([
                'timeout' => 10,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; LinkShortenerBot/1.0)',
                ]
            ]);

            $response = $client->get($url);

            $html = (string) $response->getBody();

            return $this->parseMetadata($html, $url);
        } catch (Exception | GuzzleException)
        {
            return $default;
        }
    }

    private function parseMetadata(string $html, string $baseUrl): array
    {
        $dom = new DOMDocument();

        @$dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        $metadata = [
            'title' => null,
            'description' => null,
            'image' => null,
            'url' => $baseUrl
        ];

        $ogTags = $xpath->query('//meta[@property and starts-with(@property, "og:")]');

        foreach($ogTags as $ogTag) {
            $property = $ogTag->getAttribute('property');
            $content = $ogTag->getAttribute('content');

            match ($property)
            {
                'og:title' => $metadata['title'] = $content,
                'og:description' => $metadata['description'] = $content,
                'og:image' => $metadata['image'] = $this->resolveUrl($content, $baseUrl),
                'og:url' => $metadata['url'] = $content,
                default => null,
            };
        }

        if (empty($metadata['title']))
        {
            $titleTag = $xpath->query('//title')->item(0);
            if ($titleTag)
            {
                $metadata['title'] = $titleTag->textContent;
            }
        }

        if (empty($metadata['description']))
        {
            $descTag = $xpath->query('//meta[@name="description"]')->item(0);
            if ($descTag)
            {
                $metadata['description'] = $descTag->textContent;
            }
        }

        return $metadata;
    }

    private function resolveUrl(string $url, string $baseUrl) : string
    {
        if (str_starts_with($url, 'http'))
        {
            return $url;
        }

        $parsedUrl = parse_url($baseUrl);
        $scheme = $parsedUrl['scheme'] ?? 'https';
        $host = $parsedUrl['host'] ?? '';

        if (str_starts_with($url, '/'))
        {
            return "$scheme://$host$url}";
        }

        return "$scheme://$host/" . trim ($url, '/');
    }
}
