<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\WebsiteSettings;
use App\Support\Tenancy\TenantContext;

class PublicTipsIndexController extends Controller
{
    public function __invoke(TenantContext $context)
    {
        return view('website.tips-index', [
            'tenant' => $context->get(),
            'settings' => WebsiteSettings::query()->first() ?? new WebsiteSettings,
            'tips' => Post::query()->publicly()
                ->orderByDesc('is_featured')->orderByDesc('published_at')
                ->paginate(12),
        ]);
    }
}
