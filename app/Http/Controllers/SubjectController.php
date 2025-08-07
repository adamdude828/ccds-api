<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{

    public function index(Request $request) {
        return $this->successWithModels(
            Subject::all()
        );
    }

    //
}
