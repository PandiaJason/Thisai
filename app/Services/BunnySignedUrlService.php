<?php

namespace App\Services;

class BunnySignedUrlService
{
    protected string $tokenKey;
    protected string $libraryId;

    public function __construct()
    {
        $this->tokenKey = config('bunny.token_security_key', '');
        $this->libraryId = config('bunny.library_id', '');
    }

    public function generateSignedEmbedUrl(string $videoId, int $expiresInSeconds = 3600): string
    {
        $expires = time() + $expiresInSeconds;
        
        // Bunny signature: sha256(token_key + video_id + expiration)
        // Note: some configurations also support binding to IP. We will support basic token/expiration signature.
        $hash = hash('sha256', $this->tokenKey . $videoId . $expires);

        return "https://iframe.mediadelivery.net/embed/{$this->libraryId}/{$videoId}?token={$hash}&expires={$expires}";
    }
}
