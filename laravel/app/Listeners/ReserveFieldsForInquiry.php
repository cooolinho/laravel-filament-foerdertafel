<?php

namespace App\Listeners;

use App\Events\InquiryCreated;
use App\Models\Field;
use Illuminate\Support\Facades\Log;

class ReserveFieldsForInquiry
{
    /**
     * Handle the event.
     */
    public function handle(InquiryCreated $event): void
    {
        $inquiry = $event->inquiry;

        // Get the requested field IDs
        $fieldIds = $inquiry->requested_fields ?? [];

        if (empty($fieldIds)) {
            Log::warning("Inquiry {$inquiry->id} has no requested fields to reserve.");
            return;
        }

        // Update all requested fields to 'reserved' status
        $updatedCount = Field::whereIn('id', $fieldIds)
            ->where(Field::status, Field::STATUS_AVAILABLE)
            ->update([Field::status => Field::STATUS_RESERVED]);

        Log::info("Reserved {$updatedCount} fields for Inquiry #{$inquiry->id}");
    }
}
