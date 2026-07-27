<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_export_validation_accepts_valid_filters(): void
    {
        $category = Category::factory()->create();

        $validator = Validator::make([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => now()->toDateString(),
        ], (new ExportContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_csv_export_validation_rejects_invalid_gender_and_unknown_category(): void
    {
        $validator = Validator::make([
            'gender' => 9,
            'category_id' => 999999,
        ], (new ExportContactRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }
}
