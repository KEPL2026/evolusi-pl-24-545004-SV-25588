<?php

namespace Tests\Feature;

use Tests\TestCase;

class BudgetCalculationTest extends TestCase
{
    /** Test 1: Skenario kalkulasi sukses dengan rasio preset 50/30/20 */
    public function test_successful_budget_calculation_with_default_preset(): void
    {
        $payload = [
            'monthly_income' => 1000000,
            'food_pct' => 50,
            'operational_pct' => 30,
            'healing_pct' => 20,
        ];

        $response = $this->postJson('/calculate', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'income' => 1000000,
                    'allocation' => [
                        'food' => 500000,
                        'operational' => 300000,
                        'healing' => 200000,
                    ],
                ],
            ]);
    }

    /** Test 4: Validasi penolakan jika nominal uang kurang dari batas minimal */
    public function test_validation_fails_when_income_is_below_minimum(): void
    {
        $payload = [
            'monthly_income' => 5000, // di bawah Rp 10.000
            'food_pct' => 50,
            'operational_pct' => 30,
            'healing_pct' => 20,
        ];

        $response = $this->postJson('/calculate', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['monthly_income']);
    }
}
