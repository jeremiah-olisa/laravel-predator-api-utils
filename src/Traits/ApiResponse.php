<?php

namespace LaravelPredatorApiUtils\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    public function api_response(string $message, array|int|string|null $data = null, int $code = 200): JsonResponse
    {
        $statusText = Response::$statusTexts[$code] ?? 'Unknown status';
        $status = $code . " " . $statusText;
        $data = array_merge(['status' => $status, 'message' => $message], $data ?? []);

        // return response()->json(, $code);

        return new JsonResponse($data, $code);
    }

    public function paginated_response($response): array
    {
        $res = json_decode(json_encode($response));
        $data = data_get($res, 'data');
        $paginated = data_forget($res, 'data');

        return [$data, $paginated];
    }
}
