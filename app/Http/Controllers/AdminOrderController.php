<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items'])->latest()->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function update(Request $request, Order $order)
    {
        $currentStatus = $order->status;
        
        $validTransitions = [
            'pending' => ['cooking', 'cancelled'],
            'cooking' => ['delivery', 'cancelled'],
            'delivery' => ['completed'],
            'completed' => [], 
            'cancelled' => [], 
        ];
        
        $nextStatus = $currentStatus;
        
        if ($request->has('status')) {
            $requestedStatus = $request->status;
            
            if (isset($validTransitions[$currentStatus]) && 
                in_array($requestedStatus, $validTransitions[$currentStatus])) {
                $nextStatus = $requestedStatus;
            } else {
                return redirect()->back()->with('error', 
                    "Transisi status tidak valid: {$currentStatus} → {$requestedStatus}. " .
                    "Status harus mengikuti alur: pending → cooking → delivery → completed");
            }
        } else {
            if ($currentStatus == 'pending') {
                $nextStatus = 'cooking';
            } elseif ($currentStatus == 'cooking') {
                $nextStatus = 'delivery';
            } elseif ($currentStatus == 'delivery') {
                $nextStatus = 'completed';
            }
        }

        $order->update(['status' => $nextStatus]);

        return redirect()->back()->with('success', 'Status pesanan diperbarui: ' . strtoupper($nextStatus));
    }

    public function rejectOrder(Request $request, Order $order)
    {
        try {
            DB::transaction(function () use ($order) {
                $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

                if ($lockedOrder->status !== 'pending') {
                    throw new \Exception("Tidak dapat membatalkan pesanan yang sudah diproses.");
                }

                foreach ($lockedOrder->items as $item) {
                    if ($item->food_id) {
                        $food = Food::where('id', $item->food_id)->lockForUpdate()->first();
                        if ($food) {
                            $food->increment('stock', $item->quantity);
                        }
                    }
                }
                
                $lockedOrder->update(['status' => 'cancelled']);
            });

            return redirect()->back()->with('success', 'Pesanan ditolak. Stok telah dikembalikan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}