<?php

namespace App\Http\Controllers;

use App\Models\CdnPurge;
use App\Services\FrontDoorService;

class PurgeController extends Controller
{
    public function show(string $id, FrontDoorService $fd)
    {
        $purge = CdnPurge::findOrFail($id);
        if ($purge->status === 'in_progress') {
            $fd->refreshStatus($purge);
        }
        return $purge;
    }
}