<?php

namespace App\Actions\Settings;

use App\Models\LoginPageSettings;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;
use Illuminate\Http\UploadedFile;

class UpdateLoginPageSettingsAction
{
    public function __construct(protected TenantUploads $uploads) {}

    /**
     * @param  array{background_image?: ?UploadedFile, side_image?: ?UploadedFile}  $files
     */
    public function execute(array $data, array $files = []): LoginPageSettings
    {
        $settings = LoginPageSettings::query()->first() ?? new LoginPageSettings;

        foreach (['background_image', 'side_image'] as $field) {
            /** @var ?UploadedFile $file */
            $file = $files[$field] ?? null;

            if ($file) {
                $data[$field] = $settings->exists && $settings->{$field}
                    ? $this->uploads->replace($settings->{$field}, $file, 'login-page')
                    : $this->uploads->store($file, 'login-page');
            }
        }

        $settings->fill($data);
        $settings->save();

        TenantActivity::log('تحديث مظهر صفحة تسجيل الدخول', $settings);

        return $settings->fresh();
    }
}
