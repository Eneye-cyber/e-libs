<?php
namespace App\Traits;

trait HttpResponses {
    protected function success($data, int $code = 200) : string
    {
        return response()->json(['data' => $data], $code);
    }

    protected function error(string $message, int $code) : string
    {
        return response()->json([ 'message' => $message ], $code);
    }
}
?>