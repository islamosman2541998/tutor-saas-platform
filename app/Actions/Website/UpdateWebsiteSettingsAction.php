<?php

namespace App\Actions\Website;

use App\Models\WebsiteSettings;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;
use Illuminate\Http\UploadedFile;

/**
 * website_settings is 1-1 with the tenant (unique tenant_id), so this
 * always operates on "the current tenant's row" rather than a specific
 * model instance — TenantScope + BelongsToTenant's creating hook mean a
 * plain first-or-new here is automatically scoped/tagged correctly.
 */
class UpdateWebsiteSettingsAction
{
    public function __construct(protected TenantUploads $uploads) {}

    /**
     * @param  array{logo?: ?UploadedFile, favicon?: ?UploadedFile, teacher_image?: ?UploadedFile, maintenance_image?: ?UploadedFile}  $files
     */
    public function execute(array $data, array $files = []): WebsiteSettings
    {
        $settings = WebsiteSettings::query()->first() ?? new WebsiteSettings;

        foreach (['logo', 'favicon', 'teacher_image', 'maintenance_image'] as $field) {
            /** @var ?UploadedFile $file */
            $file = $files[$field] ?? null;

            if ($file) {
                $data[$field] = $settings->exists && $settings->{$field}
                    ? $this->uploads->replace($settings->{$field}, $file, 'website')
                    : $this->uploads->store($file, 'website');
            }
        }

        $settings->fill($data);
        $settings->save();

        TenantActivity::log('تحديث إعدادات الموقع التعريفي', $settings);

        return $settings->fresh();
    }
}
