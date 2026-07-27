<?php

namespace Tests\Feature\Web;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_index_supports_filters_and_paginates_by_seven(): void
    {
        $user = User::factory()->create();
        $targetCategory = Category::factory()->create(['content' => '採用']);
        $otherCategory = Category::factory()->create(['content' => 'その他']);

        $target = Contact::factory()->create([
            'first_name' => '一致',
            'last_name' => '対象',
            'email' => 'target@example.com',
            'gender' => 2,
            'category_id' => $targetCategory->id,
            'created_at' => '2026-07-01 10:00:00',
        ]);

        Contact::factory()->count(8)->create([
            'category_id' => $otherCategory->id,
            'gender' => 1,
            'created_at' => '2026-07-02 10:00:00',
        ]);

        $response = $this->actingAs($user)->get('/admin?keyword=target@example.com&gender=2&category_id='.$targetCategory->id.'&date=2026-07-01');

        $response->assertOk();
        $response->assertSee($target->email);
        $response->assertViewHas('contacts', fn ($contacts) => $contacts->perPage() === 7);
    }

    public function test_admin_show_displays_contact_with_category_details(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['content' => '技術']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
            'email' => 'show@example.com',
        ]);

        $response = $this->actingAs($user)->get('/admin/contacts/'.$contact->id);

        $response->assertOk();
        $response->assertViewIs('admin.show');
        $response->assertSee('show@example.com');
        $response->assertSee($category->content);
    }

    public function test_admin_destroy_deletes_contact_and_redirects_to_admin(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($user)->delete('/admin/contacts/'.$contact->id);

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}
