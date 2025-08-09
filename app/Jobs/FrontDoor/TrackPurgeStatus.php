<?php

namespace App\Jobs\FrontDoor;

use App\Models\CdnPurge;
use App\Services\FrontDoorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackPurgeStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public CdnPurge $purge;

    public int $tries = 30; // up to ~15 minutes with 30s delay

    public function __construct(CdnPurge $purge)
    {
        $this->purge = $purge;
    }

    public function handle(FrontDoorService $service): void
    {
        $purge = CdnPurge::find($this->purge->id);
        if (!$purge || in_array($purge->status, ['succeeded', 'failed'])) {
            return;
        }

        $service->refreshStatus($purge);

        if ($purge->status === 'in_progress') {
            self::dispatch($purge)->delay(now()->addSeconds(30));
        }
    }
}