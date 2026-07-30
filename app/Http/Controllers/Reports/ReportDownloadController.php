<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportDownloadController extends Controller
{
    public function __invoke(Request $request, TenantContext $context)
    {
        abort_unless($request->hasValidSignature(), 403);

        $tenant = $context->get();

        // basename() strips any directory traversal attempt from the route
        // param before it's ever used to build a filesystem path, and the
        // path is always rebuilt from the *currently resolved* tenant (set
        // by ResolveTenant off the {tenant} slug in the URL) rather than
        // trusted from the request — a signed link minted for tenant A's
        // slug simply can't resolve tenant B's folder, signature or not.
        $filename = basename((string) $request->route('filename'));
        $path = "tenants/{$tenant->id}/reports/{$filename}";

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, 'تقرير.xlsx');
    }
}
