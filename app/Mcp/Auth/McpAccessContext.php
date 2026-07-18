<?php

namespace App\Mcp\Auth;

final readonly class McpAccessContext
{
    public function __construct(public McpAccessToken $token) {}

    public function authorize(string $ability): bool
    {
        return $this->token->allows($ability);
    }
}
