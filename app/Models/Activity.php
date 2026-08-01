<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    protected $fillable = [
        'log_name', 'description', 'subject_type', 'subject_id',
        'causer_type', 'causer_id', 'properties', 'event', 'batch_uuid',
        'tenant_id', 'ip_address', 'user_agent',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Arabic label for the subject's model class, for display in the
     * activity log viewer — description strings already say *what* happened
     * (see TenantActivity::log call sites), this just labels *what kind of
     * thing* it happened to.
     */
    public function subjectLabel(): ?string
    {
        if (! $this->subject_type) {
            return null;
        }

        return match ($this->subject_type) {
            Student::class => 'طالب',
            User::class => 'مستخدم',
            Group::class => 'مجموعة',
            Enrollment::class => 'تسجيل',
            Payment::class => 'دفعة',
            MonthlyDue::class => 'قسط شهري',
            ClassSession::class => 'حصة',
            RegistrationRequest::class => 'طلب تسجيل',
            AcademicYear::class => 'عام دراسي',
            Tenant::class => 'حساب المدرس',
            Slider::class => 'سلايد',
            Post::class => 'مقال/نصيحة',
            Page::class => 'صفحة',
            Testimonial::class => 'رأي طالب',
            WebsiteSettings::class => 'إعدادات الموقع',
            DashboardAppearanceSettings::class => 'مظهر لوحة التحكم',
            default => class_basename($this->subject_type),
        };
    }
}
