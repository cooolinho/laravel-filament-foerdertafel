<?php

namespace App\Listeners;

use App\Events\InquiryRejected;
use App\Models\Field;
use Illuminate\Support\Facades\Log;

class ReleaseReservedFields
{
    /**
     * Handle the event.
     */
    public function handle(InquiryRejected $event): void
    {
        $inquiry = $event->inquiry;

        // Get the requested field IDs
        $fieldIds = $inquiry->requested_fields ?? [];

        if (empty($fieldIds)) {
            Log::warning("Inquiry {$inquiry->id} has no requested fields to release.");
            return;
        }

        // Update all requested fields back to 'available' status
        // Only if they are currently reserved and not rented
        $updatedCount = Field::whereIn('id', $fieldIds)
            ->where(Field::status, Field::STATUS_RESERVED)
            ->update([Field::status => Field::STATUS_AVAILABLE]);

        Log::info("Released {$updatedCount} reserved fields for rejected Inquiry #{$inquiry->id}");
    }
}
