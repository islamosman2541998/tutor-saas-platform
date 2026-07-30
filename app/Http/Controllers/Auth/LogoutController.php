<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request, TenantContext $context)
    {
        $tenant = $context->get();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(
            $tenant ? 'tenant.login' : 'admin.login',
            $tenant ? ['tenant' => $tenant->slug] : []
        );
    }
}
