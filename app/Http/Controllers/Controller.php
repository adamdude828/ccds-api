<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success($data): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => 'success'
        ], 200);
    }

    public function fail(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => 0
        ], 400);
    }

    public function successWithModels($collection): JsonResponse
    {
        return response()->json([
            'data' => $collection->toArray(),
            'message' => 'success',
            'status' => 1
        ], 200);
    }

    public function model($model): JsonResponse
    {
        return response()->json([
            'data' => $model,
            'message' => 'success',
            'status' => 1
        ], 200);
    }
}
