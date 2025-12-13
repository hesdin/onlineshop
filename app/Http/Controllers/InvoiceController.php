<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function download(Request $request, Order $order)
    {
        $user = $request->user();

        // Verify user owns this order
        if ($order->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        // Load relationships
        $order->load([
            'items.product',
            'store',
            'paymentMethod',
            'address',
            'user',
        ]);

        $pdf = Pdf::loadView('invoices.invoice', [
            'order' => $order,
        ]);

        return $pdf->download("invoice-{$order->order_number}.pdf");
    }
}
