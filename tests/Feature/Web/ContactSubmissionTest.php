<?php

namespace Tests\Feature\Web;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(int $categoryId, array $tagIds = []): array
    {
        return [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1',
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'tel1' => '090',
            'tel2' => '1234',
            'tel3' => '5678',
            'address' => '東京都渋谷区1-2-3',
            'building' => '渋谷ビル',
            'category_id' => $categoryId,
            'tag_ids' => $tagIds,
            'detail' => 'お問い合わせ内容です',
        ];
    }

    public function test_confirm_page_displays_submitted_values_when_validation_passes(): void
    {
        $category = Category::factory()->create(['content' => 'その他']);
        $tag = Tag::create(['name' => '重要']);
        $payload = $this->validPayload($category->id, [$tag->id]);

        $response = $this->post('/contacts/confirm', $payload);

        $response->assertOk();
        $response->assertViewIs('contact.confirm');
        $response->assertSee('太郎 山田');
        $response->assertSee('taro@example.com');
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    public function test_confirm_redirects_back_with_errors_when_validation_fails(): void
    {
        $response = $this->from('/')->post('/contacts/confirm', [
            'first_name' => '',
            'last_name' => '',
            'gender' => '9',
            'email' => 'invalid',
            'tel' => '090-1234-5678',
            'tel1' => '',
            'tel2' => '',
            'tel3' => '',
            'address' => '',
            'category_id' => '',
            'detail' => '',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    public function test_store_saves_contact_and_tags_then_redirects_to_thanks(): void
    {
        $category = Category::factory()->create();
        $tagA = Tag::create(['name' => '緊急']);
        $tagB = Tag::create(['name' => '要確認']);
        $payload = $this->validPayload($category->id, [$tagA->id, $tagB->id]);

        $response = $this->post('/contacts', $payload);

        $response->assertRedirect('/thanks');
        $this->assertDatabaseHas('contacts', [
            'email' => 'taro@example.com',
            'category_id' => $category->id,
        ]);

        $contact = Contact::where('email', 'taro@example.com')->firstOrFail();
        $this->assertDatabaseHas('contact_tag', ['contact_id' => $contact->id, 'tag_id' => $tagA->id]);
        $this->assertDatabaseHas('contact_tag', ['contact_id' => $contact->id, 'tag_id' => $tagB->id]);
    }

    public function test_store_redirects_back_with_errors_when_validation_fails(): void
    {
        $response = $this->from('/')->post('/contacts', [
            'first_name' => '',
            'last_name' => '',
            'gender' => '9',
            'email' => 'invalid',
            'tel' => 'x',
            'tel1' => '',
            'tel2' => '',
            'tel3' => '',
            'address' => '',
            'category_id' => '',
            'detail' => '',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors();
    }
}
