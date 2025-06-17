<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderIsPaid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order');
        
        // If no order found in route parameters
        if (!$order) {
            abort(404, 'Order not found');
        }
        
        // Check payment status
        if ($order->payment_status !== 'paid') {
            return redirect()
                ->back()
                ->with('error', 'This order must be paid before proceeding');
        }
        
        return $next($request);
    }
}