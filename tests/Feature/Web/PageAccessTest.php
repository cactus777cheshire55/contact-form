<?php

namespace Tests\Feature\Web;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_page_displays_with_categories_and_tags(): void
    {
        $category = Category::factory()->create(['content' => '製品のお問い合わせ']);
        $tag = Tag::create(['name' => '要望']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    public function test_thanks_page_displays_successfully(): void
    {
        $response = $this->get('/thanks');

        $response->assertOk();
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }
}
