<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct() {
        $this->middleware('auth:api');
    }

    public function success($data) {
        return response()->json([
            'data'=>$data,
            'message'=>'success'
        ]);
    }

    public function fail($message) {

        return response()
                    ->json([
                        'message'=>$message,
                        'status'=>0
                    ], 400);
    }

    public function successWithModels($collection) {
        return response()->json([
                'data'=>$collection->toArray(),
                'message'=>'success',
                'status'=>1
            ],

        );
    }

    public function model($model)
    {
        return response()->json([
            'data'=>$model,
            'message'=>'success',
            'status'=>1
        ]);
    }
}
