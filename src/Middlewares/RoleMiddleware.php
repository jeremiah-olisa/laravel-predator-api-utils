<?php

namespace LaravelPredatorApiUtils\Middlewares;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelPredatorApiUtils\Services\DecryptedJWToken;
use LaravelPredatorApiUtils\Traits\ApiResponse;

class RoleMiddleware
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|array  $roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles, DecryptedJWToken $decryptedJWToken): JsonResponse|RedirectResponse|Response
    {
        // Get the user's role (replace this with your actual logic to get the user's role)
        $userRole = $decryptedJWToken->getUserRole();

        // Check if the user has one of the required roles
        if ($userRole && in_array($userRole, $roles)) {
            return $next($request);
        }

        // If the user doesn't have the required role
        return $this->api_response(
            "You do not have the authorized role to access this route",
            null,
            403
        );
    }
}
