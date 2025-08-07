<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __construct()
    {
        // Don't apply auth middleware to health check
    }

    public function check()
    {
        $status = 'healthy';
        $checks = [];

        // Database check
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $checks['database'] = 'failed';
        }

        // Add more checks as needed
        $checks['application'] = 'ok';

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks
        ], $status === 'healthy' ? 200 : 503);
    }
}