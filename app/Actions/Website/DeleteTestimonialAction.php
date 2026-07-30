<?php

namespace App\Actions\Website;

use App\Models\Testimonial;
use App\Support\Activity\TenantActivity;

class DeleteTestimonialAction
{
    public function execute(Testimonial $testimonial): void
    {
        $testimonial->delete();

        TenantActivity::log('حذف رأي طالب', $testimonial, ['student_name' => $testimonial->student_name]);
    }
}
