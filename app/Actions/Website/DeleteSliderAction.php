<?php

namespace App\Actions\Website;

use App\Models\Slider;
use App\Support\Activity\TenantActivity;

class DeleteSliderAction
{
    public function execute(Slider $slider): void
    {
        $slider->delete();

        TenantActivity::log('حذف سلايد', $slider, ['title' => $slider->title]);
    }
}
