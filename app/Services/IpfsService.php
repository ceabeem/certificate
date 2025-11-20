<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class IpfsService
{
    public function uploadFile(string $filePath): array
    {
        $provider = env('IPFS_PROVIDER', 'pinata');

        if ($provider === 'pinata') {
            return $this->uploadToPinata($filePath);
        }

        throw new \Exception('Unsupported IPFS provider: ' . $provider);
    }

    protected function uploadToPinata(string $filePath): array
    {
        $client = new Client(['base_uri' => 'https://api.pinata.cloud/']);

        $jwt = env('PINATA_JWT');
        $headers = [];
        if ($jwt) {
            $headers['Authorization'] = 'Bearer ' . $jwt;
        } else {
            $key = env('PINATA_API_KEY');
            $secret = env('PINATA_API_SECRET');
            if (! $key || ! $secret) {
                if (app()->environment('production') || env('IPFS_REQUIRE_CREDENTIALS', false)) {
                    throw new \Exception('Pinata credentials not set in env (PINATA_JWT or PINATA_API_KEY & PINATA_API_SECRET)');
                }

                return $this->localFallback($filePath);
            }
            $headers['pinata_api_key'] = $key;
            $headers['pinata_secret_api_key'] = $secret;
        }

        $response = $client->request('POST', 'pinning/pinFileToIPFS', [
            'headers' => $headers,
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                ],
            ],
            'timeout' => 120,
        ]);

        $body = json_decode((string) $response->getBody(), true);
        if (isset($body['IpfsHash'])) {
            return ['cid' => $body['IpfsHash'], 'response' => $body];
        }

        throw new \Exception('Unexpected Pinata response: ' . substr((string)$response->getBody(), 0, 500));
    }

    protected function localFallback(string $filePath): array
    {
        $contents = file_get_contents($filePath);
        $sha = sha1($contents);

        $ext = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'bin';
        $storagePath = 'ipfs_mock/' . $sha . '.' . $ext;

        Storage::put($storagePath, $contents);

        return [
            'cid' => 'local:' . $sha,
            'response' => [
                'mock' => true,
                'path' => Storage::path($storagePath),
            ],
        ];
    }
}
