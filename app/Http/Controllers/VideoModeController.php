<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\VideoMode;
use Illuminate\Http\Request;

class VideoModeController extends Controller{

    public function index() {
        return $this->successWithModels(
            VideoMode::all()
        );
    }
    //
}
