<?php

namespace App\Http\Controllers\Website;

use App\Actions\Enrollments\SubmitRegistrationRequestAction;
use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class SubmitRegistrationRequestController extends Controller
{
    public function __invoke(Request $request, SubmitRegistrationRequestAction $action)
    {
        // Explicit Arabic messages (not just attribute labels): this project
        // has no lang/ar/validation.php, so the default Laravel messages
        // don't resolve to real text at all — they render as the raw
        // translation key (e.g. "validation.required"). That's tolerable
        // inside the dashboard where Livewire's own labeled error text still
        // carries meaning, but unacceptable on this anonymous public form.
        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'student_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'group_id.required' => 'اختر مجموعة أولًا.',
            'group_id.exists' => 'المجموعة المختارة غير صالحة.',
            'student_name.required' => 'من فضلك اكتب اسم الطالب.',
            'student_name.max' => 'اسم الطالب طويل جدًا.',
            'phone.required' => 'من فضلك اكتب رقم الهاتف.',
            'phone.max' => 'رقم الهاتف غير صالح.',
            'guardian_phone.max' => 'رقم ولي الأمر غير صالح.',
            'notes.max' => 'الملاحظات طويلة جدًا.',
        ]);

        $group = Group::query()->findOrFail($data['group_id']);
        $action->execute($group, $data);

        return response()->json([
            'message' => 'تم إرسال طلبك بنجاح، هنتواصل معاك قريبًا.',
        ], 201);
    }
}
