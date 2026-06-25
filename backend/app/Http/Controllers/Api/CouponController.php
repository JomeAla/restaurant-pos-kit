<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Coupon::withCount('usages');

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($search = $request->search) {
            $query->where('code', 'like', "%{$search}%");
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:coupons,code',
            'type' => 'required|string|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_usage_count' => 'nullable|integer|min:1',
            'per_customer_limit' => 'nullable|integer|min:1',
            'applicable_item_ids' => 'nullable|array',
            'applicable_item_ids.*' => 'integer|exists:menu_items,id',
            'applicable_category_ids' => 'nullable|array',
            'applicable_category_ids.*' => 'integer|exists:categories,id',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $validated['code'] = $validated['code'] ?? Coupon::generateCode();

        return response()->json(Coupon::create($validated), 201);
    }

    public function show(Coupon $coupon): JsonResponse
    {
        return response()->json($coupon->loadCount('usages'));
    }

    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:coupons,code,' . $coupon->id,
            'type' => 'sometimes|string|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_usage_count' => 'nullable|integer|min:1',
            'per_customer_limit' => 'nullable|integer|min:1',
            'applicable_item_ids' => 'nullable|array',
            'applicable_item_ids.*' => 'integer|exists:menu_items,id',
            'applicable_category_ids' => 'nullable|array',
            'applicable_category_ids.*' => 'integer|exists:categories,id',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $coupon->update($validated);

        return response()->json($coupon);
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted.']);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'customer_identifier' => 'nullable|string',
            'order_amount' => 'nullable|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $validated['code'])->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Coupon not found.'], 404);
        }

        if (!$coupon->isValid($validated['customer_identifier'] ?? null, $validated['order_amount'] ?? null)) {
            return response()->json(['valid' => false, 'message' => 'Coupon is expired or no longer valid.'], 422);
        }

        $discount = $coupon->calculateDiscount((float) ($validated['order_amount'] ?? 0));

        return response()->json([
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
        ]);
    }
}
