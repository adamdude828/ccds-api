<?php

namespace App\Jobs\FrontDoor;

use App\Models\CdnPurge;
use App\Services\FrontDoorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InitiatePurge implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public CdnPurge $purge;

    public function __construct(CdnPurge $purge)
    {
        $this->purge = $purge;
    }

    public function handle(FrontDoorService $service): void
    {
        $purge = CdnPurge::find($this->purge->id);
        if (!$purge) {
            return;
        }
        if ($purge->status === 'pending') {
            $service->initiate($purge);
        }
        if ($purge->status === 'in_progress') {
            TrackPurgeStatus::dispatch($purge)->delay(now()->addSeconds(10));
        }
    }
}