<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\WebsiteSettings;
use Illuminate\Http\Request;

class PublicPostController extends Controller
{
    public function __invoke(Request $request)
    {
        // Read straight off the route rather than a typed model parameter —
        // see PaymentReceiptController for why: with more URI segments
        // (teacher/{tenant}/tips/{post}) than this method declares, an
        // implicitly-bound parameter here would receive {tenant}'s value,
        // not {post}'s.
        $post = Post::query()
            ->publicly()
            ->where('slug', $request->route('post'))
            ->firstOrFail();

        $post->increment('views_count');

        return view('website.post', [
            'post' => $post,
            'settings' => WebsiteSettings::query()->first() ?? new WebsiteSettings,
        ]);
    }
}
