<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BudgetController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'monthly_income' => ['required', 'integer', 'min:10000'],
            'food_pct' => ['required', 'numeric', 'min:0'],
            'operational_pct' => ['required', 'numeric', 'min:0'],
            'healing_pct' => ['required', 'numeric', 'min:0'],
        ]);

        $total = $validated['food_pct'] + $validated['operational_pct'] + $validated['healing_pct'];

        if (abs($total - 100) > 0.0001) {
            throw ValidationException::withMessages([
                'percentages' => 'The sum of all allocation percentages must equal exactly 100%.',
            ]);
        }

        $income = $validated['monthly_income'];

        return response()->json([
            'status' => 'success',
            'data' => [
                'income' => $income,
                'allocation' => [
                    'food' => (int) round($income * ($validated['food_pct'] / 100)),
                    'operational' => (int) round($income * ($validated['operational_pct'] / 100)),
                    'healing' => (int) round($income * ($validated['healing_pct'] / 100)),
                ],
            ],
        ], 200);
    }
}
