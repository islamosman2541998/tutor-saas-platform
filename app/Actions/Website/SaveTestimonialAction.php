<?php

namespace App\Actions\Website;

use App\Models\Testimonial;
use App\Support\Activity\TenantActivity;
use App\Support\Files\TenantUploads;
use Illuminate\Http\UploadedFile;

class SaveTestimonialAction
{
    public function __construct(protected TenantUploads $uploads) {}

    public function execute(array $data, ?Testimonial $testimonial = null, ?UploadedFile $image = null): Testimonial
    {
        if ($image) {
            $data['student_image'] = $testimonial && $testimonial->student_image
                ? $this->uploads->replace($testimonial->student_image, $image, 'testimonials')
                : $this->uploads->store($image, 'testimonials');
        }

        if ($testimonial) {
            $testimonial->update($data);
            TenantActivity::log('تعديل رأي طالب', $testimonial, ['student_name' => $testimonial->student_name]);

            return $testimonial->fresh();
        }

        $data['sort_order'] = (Testimonial::query()->max('sort_order') ?? 0) + 1;
        $testimonial = Testimonial::query()->create($data);
        TenantActivity::log('إضافة رأي طالب', $testimonial, ['student_name' => $testimonial->student_name]);

        return $testimonial;
    }
}
