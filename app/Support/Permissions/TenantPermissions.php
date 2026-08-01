<?php

namespace App\Support\Permissions;

/**
 * Global permission catalogue (permission NAMES are shared across all
 * tenants — Spatie's Teams feature scopes roles and role assignments per
 * tenant, not the permission list itself) and the default permission set
 * granted to each built-in tenant role.
 *
 * More modules will extend this list as they are implemented (Enrollments,
 * Monthly Dues, Website Builder, ...). Keeping it centralised avoids typo'd
 * permission strings scattered across Policies and Livewire components.
 */
class TenantPermissions
{
    public const TEACHER_OWNER = 'teacher_owner';

    public const ASSISTANT = 'assistant';

    public const ACCOUNTANT = 'accountant';

    public const CONTENT_MANAGER = 'content_manager';

    public static function all(): array
    {
        return [
            'students.view', 'students.create', 'students.update', 'students.delete',
            'groups.view', 'groups.create', 'groups.update', 'groups.delete',
            'attendance.view', 'attendance.record', 'attendance.unlock',
            'payments.view', 'payments.record', 'payments.cancel',
            'reports.view', 'reports.export',
            'website.manage',
            'settings.manage',
            'users.manage',
            'activity.view',
        ];
    }

    /**
     * @return array<string, array<int, string>> role name => permission names
     */
    public static function defaultRolePermissions(): array
    {
        return [
            self::TEACHER_OWNER => self::all(),
            self::ASSISTANT => [
                'students.view', 'students.create', 'students.update',
                'groups.view',
                'attendance.view', 'attendance.record',
            ],
            self::ACCOUNTANT => [
                'payments.view', 'payments.record',
                'reports.view', 'reports.export',
            ],
            self::CONTENT_MANAGER => [
                'website.manage',
            ],
        ];
    }

    /**
     * Roles a Teacher Owner can hand out to a staff member. TEACHER_OWNER
     * itself is excluded — it's assigned exactly once, at tenant
     * registration (see RegisterTenantAction), never through the staff form.
     *
     * @return array<int, string>
     */
    public static function assignableRoles(): array
    {
        return [self::ASSISTANT, self::ACCOUNTANT, self::CONTENT_MANAGER];
    }

    public static function roleLabel(string $role): string
    {
        return match ($role) {
            self::TEACHER_OWNER => 'صاحب الحساب',
            self::ASSISTANT => 'مساعد',
            self::ACCOUNTANT => 'محاسب',
            self::CONTENT_MANAGER => 'مسؤول المحتوى',
            default => $role,
        };
    }
}
