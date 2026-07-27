<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiStoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_store_validation_accepts_required_fields_and_tags(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::create(['name' => '機能要望']);

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1',
            'email' => 'api@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'building' => '渋谷ビル',
            'detail' => 'APIからのお問い合わせ',
            'category_id' => $category->id,
            'tags' => [$tag->id],
        ], (new StoreContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_api_store_validation_rejects_invalid_values(): void
    {
        $validator = Validator::make([
            'first_name' => '',
            'last_name' => '',
            'gender' => '9',
            'email' => 'invalid',
            'tel' => '090-1111-2222',
            'address' => '',
            'detail' => '',
            'category_id' => 9999,
            'tags' => [9999],
        ], (new StoreContactRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('tags.0', $validator->errors()->toArray());
    }
}
