<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardPreferenceController extends Controller
{
    public const WIDGETS = [
        'active_employees', 'scheduled_today', 'checked_in', 'late_arrivals',
        'approved_leave', 'no_checkout', 'uncovered_schedule', 'open_tasks',
        'action_queue', 'my_work', 'recent_attendance',
    ];

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'max:20'],
            'order.*' => ['required', 'string', Rule::in(self::WIDGETS)],
            'hidden' => ['array', 'max:20'],
            'hidden.*' => ['required', 'string', Rule::in(self::WIDGETS)],
        ]);

        $request->user()->forceFill([
            'dashboard_preferences' => [
                'order' => array_values(array_unique($data['order'])),
                'hidden' => array_values(array_unique($data['hidden'] ?? [])),
            ],
        ])->save();

        return response()->json(['saved' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->forceFill(['dashboard_preferences' => null])->save();

        return response()->json(['reset' => true]);
    }
}
