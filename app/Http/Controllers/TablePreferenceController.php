<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TablePreferenceController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'table' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9._:-]+$/i'],
            'hidden_columns' => ['array', 'max:30'],
            'hidden_columns.*' => ['integer', 'min:0', 'max:50'],
        ]);
        $preferences = $request->user()->table_preferences ?? [];
        $preferences[$data['table']] = array_values(array_unique($data['hidden_columns'] ?? []));
        $request->user()->forceFill(['table_preferences' => $preferences])->save();

        return response()->json(['saved' => true]);
    }
}
