<?php

namespace LaravelPredatorApiUtils\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * The function `api_response` generates a JSON response with a message, data, and status code.
     * 
     * @param string message The `message` parameter in the `api_response` function is a string that
     * represents the message you want to include in the API response. It could be a success message,
     * an error message, or any other relevant information you want to communicate back to the API
     * consumer.
     * @param array data The `data` parameter in the `api_response` function is a variable that can
     * accept an array, integer, string, or null value. It is used to provide additional data to be
     * included in the API response along with the message and status code. This data can be any
     * information that you want
     * @param int code The `code` parameter in the `api_response` function is used to specify the HTTP
     * status code for the response. It defaults to `200` if not provided explicitly. This code is used
     * to indicate the status of the API response, such as success, error, or other status types based
     * on
     * 
     * @return JsonResponse The `api_response` function is returning a `JsonResponse` object with the
     * provided data, status code, and message. The data includes the status code, status text,
     * message, and any additional data passed to the function.
     */
    public function api_response(string $message, array|int|string|null $data = null, int $code = 200): JsonResponse
    {
        $statusText = Response::$statusTexts[$code] ?? 'Unknown status';
        $status = $code . " " . $statusText;
        $data = array_merge(['status' => $status, 'message' => $message], $data ?? []);

        return new JsonResponse($data, $code);
    }

    
    /**
     * The function paginated_response takes a response, extracts the 'data' field, and separates it
     * from the rest of the response data.
     * 
     * @param response The `paginated_response` function takes a response object as a parameter. This
     * response object typically contains paginated data, where the actual data is nested under a
     * 'data' key along with pagination information.
     * 
     * @return array An array is being returned, containing two elements. The first element is the
     * 'data' extracted from the input response after converting it to JSON and then back to an array.
     * The second element is the paginated information extracted from the response after removing the
     * 'data' key.
     */
    public function paginated_response($response): array
    {
        $res = json_decode(json_encode($response));
        $data = data_get($res, 'data');
        $paginated = data_forget($res, 'data');

        return [$data, $paginated];
    }
}
