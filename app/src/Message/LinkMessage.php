<?php

namespace App\Message;

class LinkMessage
{
    private string $original_url;

    public function __construct(string $original_url)
    {
        $this->original_url = $original_url;
    }

    public function getOriginalUrl(): string
    {
        return $this->original_url;
    }
}