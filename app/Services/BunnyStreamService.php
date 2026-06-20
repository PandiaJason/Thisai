<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BunnyStreamService
{
    protected string $apiKey;
    protected string $libraryId;
    protected string $cdnHostname;
    protected string $baseUrl = 'https://video.bunnycdn.com';

    public function __construct()
    {
        $this->apiKey = config('bunny.api_key', '');
        $this->libraryId = config('bunny.library_id', '');
        $this->cdnHostname = config('bunny.cdn_hostname', '');
    }

    protected function client()
    {
        return Http::withHeaders([
            'AccessKey' => $this->apiKey,
            'Accept' => 'application/json',
        ]);
    }

    public function createVideo(string $title): ?array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/library/{$this->libraryId}/videos", [
                'title' => $title,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Bunny Stream createVideo failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Bunny Stream createVideo exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function uploadVideo(string $videoId, string $filePath): bool
    {
        try {
            if (!file_exists($filePath)) {
                Log::error('Bunny Stream uploadVideo file does not exist', ['path' => $filePath]);
                return false;
            }

            $response = Http::withHeaders([
                'AccessKey' => $this->apiKey,
                'Content-Type' => 'application/octet-stream',
            ])->withBody(file_get_contents($filePath), 'application/octet-stream')
              ->put("{$this->baseUrl}/library/{$this->libraryId}/videos/{$videoId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Bunny Stream uploadVideo exception', ['error' => $e->getMessage()]);
        }

        return false;
    }

    public function getVideo(string $videoId): ?array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/library/{$this->libraryId}/videos/{$videoId}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Bunny Stream getVideo exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function deleteVideo(string $videoId): bool
    {
        try {
            $response = $this->client()->delete("{$this->baseUrl}/library/{$this->libraryId}/videos/{$videoId}");
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Bunny Stream deleteVideo exception', ['error' => $e->getMessage()]);
        }

        return false;
    }

    public function listVideos(int $page = 1, int $perPage = 10): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/library/{$this->libraryId}/videos", [
                'page' => $page,
                'itemsPerPage' => $perPage,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Bunny Stream listVideos exception', ['error' => $e->getMessage()]);
        }

        return [];
    }
}
