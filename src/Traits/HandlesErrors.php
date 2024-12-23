<?php

namespace LaravelPredatorApiUtils\Traits;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

trait HandlesErrors
{
    public function handleErrors(callable $callback, string|null $errorMessage = null)
    {
        try {
            return $callback();
        } catch (\Exception | Throwable | HttpException $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            return $this->api_response(
                $errorMessage ?? "Something went wrong",
                ['error' => $e->getMessage()],
                $statusCode
            );
        }
    }
}
