<?php

namespace App\Actions\Website;

use App\Models\Slider;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;
use Illuminate\Http\UploadedFile;

class SaveSliderAction
{
    public function __construct(protected TenantUploads $uploads) {}

    public function execute(array $data, ?Slider $slider = null, ?UploadedFile $image = null, ?UploadedFile $mobileImage = null): Slider
    {
        if ($image) {
            $data['image'] = $slider && $slider->image
                ? $this->uploads->replace($slider->image, $image, 'sliders')
                : $this->uploads->store($image, 'sliders');
        }

        if ($mobileImage) {
            $data['mobile_image'] = $slider && $slider->mobile_image
                ? $this->uploads->replace($slider->mobile_image, $mobileImage, 'sliders')
                : $this->uploads->store($mobileImage, 'sliders');
        }

        if ($slider) {
            $slider->update($data);
            TenantActivity::log('تعديل سلايد', $slider, ['title' => $slider->title]);

            return $slider->fresh();
        }

        $data['sort_order'] = (Slider::query()->max('sort_order') ?? 0) + 1;
        $slider = Slider::query()->create($data);
        TenantActivity::log('إنشاء سلايد', $slider, ['title' => $slider->title]);

        return $slider;
    }
}
