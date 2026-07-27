<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_validation_accepts_all_filters(): void
    {
        $category = Category::factory()->create();

        $validator = Validator::make([
            'keyword' => 'test@example.com',
            'gender' => 2,
            'category_id' => $category->id,
            'date' => now()->toDateString(),
        ], (new IndexContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_index_validation_rejects_invalid_gender(): void
    {
        $validator = Validator::make([
            'gender' => 4,
        ], (new IndexContactRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }
}
