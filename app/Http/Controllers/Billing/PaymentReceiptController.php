<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentReceiptController extends Controller
{
    public function __invoke(Request $request)
    {
        Gate::authorize('payments.view');

        // Read straight off the route rather than an untyped/typed method
        // parameter: with more URI segments (teacher/{tenant}/payments/
        // {payment}/receipt) than this method declares, Laravel's non-class
        // parameter resolution matches by *position* in the full parameter
        // list, not by name — an untyped `$payment` here actually receives
        // {tenant}'s value. request()->route('payment') sidesteps that.
        $payment = Payment::query()
            ->with(['student', 'monthlyDue'])
            ->findOrFail((int) $request->route('payment'));

        return view('billing.payment-receipt', [
            'payment' => $payment,
        ]);
    }
}
