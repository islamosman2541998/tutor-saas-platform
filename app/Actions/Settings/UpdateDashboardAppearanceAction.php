<?php

namespace App\Actions\Settings;

use App\Models\DashboardAppearanceSettings;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;
use Illuminate\Http\UploadedFile;

/**
 * dashboard_appearance_settings is 1-1 with the tenant (unique tenant_id),
 * same first-or-new pattern as UpdateWebsiteSettingsAction.
 */
class UpdateDashboardAppearanceAction
{
    public function __construct(protected TenantUploads $uploads) {}

    /**
     * @param  array{logo_full?: ?UploadedFile, logo_mini?: ?UploadedFile, favicon?: ?UploadedFile}  $files
     */
    public function execute(array $data, array $files = []): DashboardAppearanceSettings
    {
        $settings = DashboardAppearanceSettings::query()->first() ?? new DashboardAppearanceSettings;

        foreach (['logo_full', 'logo_mini', 'favicon'] as $field) {
            /** @var ?UploadedFile $file */
            $file = $files[$field] ?? null;

            if ($file) {
                $data[$field] = $settings->exists && $settings->{$field}
                    ? $this->uploads->replace($settings->{$field}, $file, 'appearance')
                    : $this->uploads->store($file, 'appearance');
            }
        }

        $settings->fill($data);
        $settings->save();

        TenantActivity::log('تحديث مظهر لوحة التحكم', $settings);

        return $settings->fresh();
    }
}
