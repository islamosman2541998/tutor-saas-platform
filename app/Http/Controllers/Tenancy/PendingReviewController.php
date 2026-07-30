<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;

class PendingReviewController extends Controller
{
    public function __invoke()
    {
        return view('tenant.pending-review');
    }
}
