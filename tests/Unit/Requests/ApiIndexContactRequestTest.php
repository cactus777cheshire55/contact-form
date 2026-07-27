<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiIndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_index_validation_accepts_filters_and_per_page(): void
    {
        $category = Category::factory()->create();

        $validator = Validator::make([
            'keyword' => '山田',
            'gender' => '1',
            'category_id' => $category->id,
            'date' => now()->toDateString(),
            'per_page' => 25,
        ], (new IndexContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_api_index_validation_rejects_invalid_values(): void
    {
        $validator = Validator::make([
            'gender' => '9',
            'category_id' => 9999,
            'date' => '2026/01/01',
            'per_page' => 0,
        ], (new IndexContactRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }
}
