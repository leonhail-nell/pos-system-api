<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $orders->map(fn (Order $o) => $this->formatOrder($o))->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.productId' => ['required', 'string'],
            'lines.*.name' => ['required', 'string', 'max:255'],
            'lines.*.qty' => ['required', 'integer', 'min:1'],
            'lines.*.priceCents' => ['required', 'integer', 'min:0'],
            'totalCents' => ['required', 'integer', 'min:0'],
        ]);

        $sum = 0;
        foreach ($validated['lines'] as $line) {
            $sum += $line['priceCents'] * $line['qty'];
        }

        if ($sum !== $validated['totalCents']) {
            return response()->json([
                'message' => 'Total does not match line items.',
            ], 422);
        }

        $linesForDb = [];
        foreach ($validated['lines'] as $line) {
            $linesForDb[] = [
                'product_id' => $line['productId'],
                'name' => $line['name'],
                'qty' => $line['qty'],
                'price_cents' => $line['priceCents'],
            ];
        }

        $order = Order::create([
            'user_id' => $request->user()->id,
            'total_cents' => $validated['totalCents'],
            'status' => 'paid',
            'lines' => $linesForDb,
        ]);

        return response()->json([
            'data' => $this->formatOrder($order),
        ], 201);
    }

    private function formatOrder(Order $o): array
    {
        $lines = [];
        foreach ($o->lines as $line) {
            $lines[] = [
                'productId' => (string) $line['product_id'],
                'name' => $line['name'],
                'qty' => (int) $line['qty'],
                'priceCents' => (int) $line['price_cents'],
            ];
        }

        return [
            'id' => (string) $o->id,
            'userId' => (string) $o->user_id,
            'lines' => $lines,
            'totalCents' => $o->total_cents,
            'status' => $o->status,
            'createdAt' => $o->created_at->toIso8601String(),
        ];
    }
}
