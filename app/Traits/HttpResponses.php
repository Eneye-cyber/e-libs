<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HttpResponses {
    protected function success($data, int $code = 200) : JsonResponse
    {
        return response()->json(['data' => $data], $code);
    }

    protected function error(string $message, int $code) : JsonResponse
    {
        return response()->json([ 'message' => $message ], $code);
    }
}
?>