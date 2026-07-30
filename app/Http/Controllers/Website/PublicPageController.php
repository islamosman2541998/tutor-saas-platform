<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\WebsiteSettings;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function __invoke(Request $request)
    {
        // See PaymentReceiptController / PublicPostController for why this
        // is read off the route explicitly rather than an implicitly-bound
        // typed parameter.
        $page = Page::query()
            ->publicly()
            ->where('slug', $request->route('page'))
            ->firstOrFail();

        return view('website.page', [
            'page' => $page,
            'settings' => WebsiteSettings::query()->first() ?? new WebsiteSettings,
        ]);
    }
}
