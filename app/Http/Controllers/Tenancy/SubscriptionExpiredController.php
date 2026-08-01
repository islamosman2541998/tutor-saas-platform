<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;

class SubscriptionExpiredController extends Controller
{
    public function __invoke()
    {
        return view('tenant.subscription-expired');
    }
}
