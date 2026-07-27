<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_contact_validation_accepts_required_fields_and_tags(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::create(['name' => '重要']);

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'tel1' => '090',
            'tel2' => '1234',
            'tel3' => '5678',
            'address' => '東京都渋谷区1-2-3',
            'building' => '渋谷ビル',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => 'お問い合わせ内容です',
        ], (new StoreContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_store_contact_validation_rejects_invalid_tel_format(): void
    {
        $category = Category::factory()->create();

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '090-1234-5678',
            'tel1' => '090',
            'tel2' => '1234',
            'tel3' => '5678',
            'address' => '東京都渋谷区1-2-3',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ], (new StoreContactRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
    }
}
