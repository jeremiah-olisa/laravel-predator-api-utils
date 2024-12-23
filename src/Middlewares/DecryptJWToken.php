<?php

namespace LaravelPredatorApiUtils\Middlewares;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;

class DecryptJWToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return JsonResponse|RedirectResponse|Response
     */
    public function handle(Request $request, Closure $next): JsonResponse|RedirectResponse|Response
    {
        $token = $request->header('Authorization');

        if ($token) {
            $token = str_replace('Bearer ', '', $token);

            try {
                $decryptedToken = Crypt::decryptString($token);
                $request->merge(['auth_user' => json_decode($decryptedToken, true)]);
            } catch (DecryptException $e) {
                // Handle token decryption failure (e.g., invalid token, decryption key mismatch)
                return new JsonResponse(['error' => 'Invalid token'], 401);
            }
        }

        return $next($request);
    }
}
