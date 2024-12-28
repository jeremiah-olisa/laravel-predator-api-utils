<?php

namespace LaravelPredatorApiUtils\Middlewares;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class DecryptJWToken
{
    /**
     * The function handles JWT token validation and decoding in a PHP application.
     * 
     * @param Request request The `handle` function you provided is a middleware function in a PHP
     * application that handles JWT token validation. It extracts the JWT token from the Authorization
     * header of the incoming request, decodes and verifies the token using a secret key, and then
     * merges the decoded token data into the request object.
     * @param Closure next The `` parameter in the `handle` function is a Closure that represents
     * the next middleware or the controller action that should be called in the request-response
     * cycle. When you call `()` in the function, you are essentially passing the request
     * to the next middleware or controller action in the
     * 
     * @return JsonResponse|RedirectResponse|Response The `handle` function is returning either a
     * `JsonResponse` with a specific message based on the exception caught during token validation, or
     * it returns the result of the next middleware in the pipeline (`()`). The possible
     * return types are `JsonResponse`, `RedirectResponse`, or `Response`.
     */
    public function handle(Request $request, Closure $next): JsonResponse|RedirectResponse|Response
    {
        $token = $request->header('Authorization');

        if (!$token)
            return new JsonResponse(['error' => 'Authentication credentials are missing.'], 401);

        $token = str_replace('Bearer ', '', $token);

        try {

            $secretKey = Config::get('decrypted_jwt_token.jwt_secret_key');
            $jwtAlgorithm = Config::get('decrypted_jwt_token.jwt_algorithm', 'HS256');

            $decryptedToken = (array)JWT::decode($token, new Key($secretKey, $jwtAlgorithm));

            $request->merge(['auth_user' => $decryptedToken]);
        } catch (SignatureInvalidException $e) {
            return new JsonResponse(['token' => 'We could not verify your session. Please contact an administrator or try again later.'], 500);
        } catch (ExpiredException $e) {
            return new JsonResponse(['token' => 'Your login session has expired. Please log in again.'], 401);
        } catch (BeforeValidException $e) {
            return new JsonResponse(['token' => 'Your login session is not yet active.'], 401);
        } catch (\Exception $e) {
            return new JsonResponse(['token' => 'An error occurred while validating your session. Please try again later.'], 500);
        }

        return $next($request);
    }
}
