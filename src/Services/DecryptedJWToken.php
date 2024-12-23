<?php

namespace LaravelPredatorApiUtils\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class DecryptedJWToken
{
    private array $data;

    public function __construct(Request $request)
    {
        $this->data = $request->auth_user ?? [];
    }

    public function getData()
    {
        return $this->data ?? [];
    }

    public function getUserId(string $prop = null): int|string|null
    {
        $prop = $prop ?: Config::get('decrypted_jwt_token.user_id_prop');
        return $this->getData()[$prop] ?? null;
    }

    public function getUserName(string $prop = null): ?string
    {
        $prop = $prop ?: Config::get('decrypted_jwt_token.user_name_prop');
        return $this->getData()[$prop] ?? null;
    }

    public function getUserRole(string $prop = null): ?string
    {
        $prop = $prop ?: Config::get('decrypted_jwt_token.user_role_prop');
        return $this->getData()[$prop] ?? null;
    }

    public function getUserRoles(string $prop = null): ?array
    {
        $prop = $prop ?: Config::get('decrypted_jwt_token.user_roles_prop');
        return $this->getData()[$prop] ?? [];
    }

    public function getTokenExpiryTime(string $prop = null): ?int
    {
        $prop = $prop ?: Config::get('decrypted_jwt_token.token_expiry_prop');
        return $this->getData()[$prop] ?? null;
    }
}
