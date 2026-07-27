<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(int $categoryId, array $tags = []): array
    {
        return [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1',
            'email' => 'api-create@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-2-3',
            'building' => '渋谷ビル',
            'detail' => 'API テスト',
            'category_id' => $categoryId,
            'tags' => $tags,
        ];
    }

    public function test_index_returns_json_list_with_filters_and_pagination(): void
    {
        $category = Category::factory()->create();

        Contact::factory()->create([
            'first_name' => '一致',
            'email' => 'api-match@example.com',
            'gender' => 1,
            'category_id' => $category->id,
        ]);
        Contact::factory()->create([
            'first_name' => '不一致',
            'email' => 'api-other@example.com',
            'gender' => 2,
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=api-match@example.com&gender=1&category_id='.$category->id.'&per_page=1');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
    }

    public function test_index_returns_422_for_invalid_query(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=9&category_id=999999&date=2026/01/01&per_page=0');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender', 'category_id', 'date', 'per_page']);
    }

    public function test_show_returns_contact_and_404_for_missing_id(): void
    {
        $contact = Contact::factory()->create();

        $this->getJson('/api/v1/contacts/'.$contact->id)
            ->assertOk()
            ->assertJsonPath('data.id', $contact->id);

        $this->getJson('/api/v1/contacts/999999')->assertNotFound();
    }

    public function test_store_creates_contact_and_returns_201_with_validation_errors_as_422(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::create(['name' => 'APIタグ']);

        $response = $this->postJson('/api/v1/contacts', $this->validPayload($category->id, [$tag->id]));

        $response->assertCreated();
        $this->assertDatabaseHas('contacts', ['email' => 'api-create@example.com']);

        $contact = Contact::where('email', 'api-create@example.com')->firstOrFail();
        $this->assertDatabaseHas('contact_tag', ['contact_id' => $contact->id, 'tag_id' => $tag->id]);

        $invalid = $this->postJson('/api/v1/contacts', [
            'first_name' => '',
            'last_name' => '',
            'gender' => '9',
            'email' => 'invalid',
            'tel' => '090-1234-5678',
            'address' => '',
            'detail' => '',
            'category_id' => 999999,
            'tags' => [999999],
        ]);

        $invalid->assertStatus(422);
        $invalid->assertJsonValidationErrors(['first_name', 'last_name', 'gender', 'email', 'tel', 'address', 'detail', 'category_id', 'tags.0']);
    }

    public function test_update_updates_contact_and_handles_404_and_422(): void
    {
        $category = Category::factory()->create();
        $anotherCategory = Category::factory()->create();
        $tag = Tag::create(['name' => '旧タグ']);
        $newTag = Tag::create(['name' => '新タグ']);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
            'email' => 'before@example.com',
        ]);
        $contact->tags()->sync([$tag->id]);

        $payload = [
            'first_name' => '更新',
            'last_name' => '済み',
            'gender' => '2',
            'email' => 'after@example.com',
            'tel' => '08012345678',
            'address' => '東京都港区1-2-3',
            'building' => '港ビル',
            'detail' => '更新後',
            'category_id' => $anotherCategory->id,
            'tags' => [$newTag->id],
        ];

        $this->putJson('/api/v1/contacts/'.$contact->id, $payload)
            ->assertOk()
            ->assertJsonPath('data.email', 'after@example.com');

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'after@example.com',
            'category_id' => $anotherCategory->id,
        ]);
        $this->assertDatabaseHas('contact_tag', ['contact_id' => $contact->id, 'tag_id' => $newTag->id]);

        $this->putJson('/api/v1/contacts/999999', $payload)->assertNotFound();

        $this->putJson('/api/v1/contacts/'.$contact->id, array_merge($payload, ['gender' => '9']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_destroy_deletes_contact_and_returns_204_with_404_for_missing_id(): void
    {
        $contact = Contact::factory()->create();

        $this->deleteJson('/api/v1/contacts/'.$contact->id)->assertNoContent();
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);

        $this->deleteJson('/api/v1/contacts/999999')->assertNotFound();
    }
}
