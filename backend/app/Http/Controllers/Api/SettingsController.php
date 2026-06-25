<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Setting::all()->groupBy('group')->toArray());
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|max:255',
            'settings.*.value' => 'nullable',
            'settings.*.group' => 'sometimes|string|max:255',
        ]);

        foreach ($validated['settings'] as $setting) {
            Setting::setValue($setting['key'], $setting['value'], $setting['group'] ?? 'general');
        }

        return response()->json(['message' => 'Settings updated.', 'settings' => Setting::all()->groupBy('group')]);
    }
}
