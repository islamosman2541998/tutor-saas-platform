<?php

namespace App\Actions\Enrollments;

use App\Models\WaitingListEntry;
use App\Support\Activity\TenantActivity;
use Illuminate\Support\Facades\DB;

class ConvertWaitlistEntryAction
{
    public function __construct(protected EnrollStudentAction $enrollStudent) {}

    public function execute(WaitingListEntry $entry, array $data): array
    {
        return DB::transaction(function () use ($entry, $data) {
            $result = $this->enrollStudent->execute($entry->student, $entry->group, $data);

            if ($result['type'] === 'enrolled') {
                $entry->update(['status' => 'accepted']);

                TenantActivity::log('تحويل طالب من قائمة الانتظار لمجموعة', $entry, [
                    'student' => $entry->student->name,
                    'group' => $entry->group->name,
                ]);
            }

            return $result;
        });
    }
}
