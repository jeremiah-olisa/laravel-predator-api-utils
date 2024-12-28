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
     * The function checks if a user has one of the required roles to access a route and returns an
     * appropriate response.
     * 
     * @param Request request The `` parameter in the `handle` function represents the incoming
     * HTTP request. It contains all the data and information about the request made to the server,
     * such as headers, parameters, and body content. This parameter is typically used to extract data
     * from the request, validate inputs, and perform necessary
     * @param Closure next The `` parameter in the `handle` function is a Closure that represents
     * the next middleware or the controller action that should be called in the request-response
     * cycle. When you call `()`, you are essentially passing the request to the next
     * middleware or controller action in the pipeline.
     * @param DecryptedJWToken decryptedJWToken The `DecryptedJWToken` parameter in the `handle`
     * function represents a dependency injection of a service or class that is responsible for
     * decrypting a JSON Web Token (JWT) and extracting information from it, such as the user's role.
     * 
     * @return JsonResponse|RedirectResponse|Response The `handle` function is returning either a
     * `JsonResponse`, `RedirectResponse`, or `Response` based on the conditions within the function.
     * If the user has one of the required roles, the function will return the result of calling the
     * `` closure with the given request. If the user does not have the required role, it will
     * return a JSON response with a message indicating that the user
     */
    public function handle(Request $request, Closure $next, ...$roles): JsonResponse|RedirectResponse|Response
    {
        $decryptedJWToken = new DecryptedJWToken($request);

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
