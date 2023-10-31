<?php

namespace App\Observers;

use App\Models\BoardItem;
use Illuminate\Support\Facades\Log;

class BoardItemObserver
{
    /**
     * Handle the BoardItem "created" event.
     *
     * @param \App\Models\BoardItem $boardItem
     * @return void
     */
    public function created(BoardItem $boardItem)
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/created.log'),
        ])->info(__('Issue :code created', ['code' => $boardItem->code]));
    }

    /**
     * Handle the BoardItem "updated" event.
     *
     * @param \App\Models\BoardItem $boardItem
     * @return void
     */
    public function updated(BoardItem $boardItem)
    {
        if ($boardItem->wasChanged('status_id')) {
            $message = match ($boardItem->status->title) {
                'IN_PROCESS' => __('Issue :code updated status to :to', ['code' => $boardItem->code, 'to' => $boardItem->status->title]),
                'COMPLETED' => __('Issue :code updated status to :to', ['code' => $boardItem->code, 'to' => $boardItem->status->title]),
            };
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/updated.log'),
            ])->info($message);
        }
    }
}
