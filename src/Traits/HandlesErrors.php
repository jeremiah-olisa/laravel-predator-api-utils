<?php

namespace LaravelPredatorApiUtils\Traits;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

trait HandlesErrors
{
    /**
     * The function `handleErrors` in PHP handles errors by executing a callback function and returning
     * an API response with an error message and status code if an exception occurs.
     * 
     * @param callable callback The `callback` parameter in the `handleErrors` function is a callable
     * function or method that you want to execute within a try-catch block. It is the code that you
     * want to run and handle any potential errors that may occur during its execution.
     * @param string errorMessage The `` parameter in the `handleErrors` function is a
     * string that allows you to specify a custom error message to be returned if an exception is
     * caught during the execution of the provided callback. If no custom error message is provided, a
     * default message "Something went wrong" will be used.
     * 
     * @return The `handleErrors` function returns the result of the provided callback function if no
     * exception is thrown. If an exception of type `\Exception`, `Throwable`, or `HttpException` is
     * caught, it returns an API response with the specified error message or a default message
     * ("Something went wrong"), the error message from the caught exception, and the status code
     * extracted from the exception (or 500 if not available
     */
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
