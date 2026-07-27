<?php

namespace Tests\Feature\Web;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_crud_tags_and_is_redirected_to_admin(): void
    {
        $user = User::factory()->create();
        $tag = Tag::create(['name' => '既存タグ']);

        $editResponse = $this->actingAs($user)->get('/admin/tags/'.$tag->id.'/edit');
        $editResponse->assertOk();

        $storeResponse = $this->actingAs($user)->post('/admin/tags', ['name' => '新規タグ']);
        $storeResponse->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['name' => '新規タグ']);

        $updateResponse = $this->actingAs($user)->put('/admin/tags/'.$tag->id, ['name' => '更新タグ']);
        $updateResponse->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '更新タグ']);

        $deleteResponse = $this->actingAs($user)->delete('/admin/tags/'.$tag->id);
        $deleteResponse->assertRedirect('/admin');
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_guest_user_is_redirected_to_login_for_tag_operations(): void
    {
        $tag = Tag::create(['name' => 'ゲスト検証']);

        $this->get('/admin/tags/'.$tag->id.'/edit')->assertRedirect('/login');
        $this->post('/admin/tags', ['name' => '作成不可'])->assertRedirect('/login');
        $this->put('/admin/tags/'.$tag->id, ['name' => '更新不可'])->assertRedirect('/login');
        $this->delete('/admin/tags/'.$tag->id)->assertRedirect('/login');
    }
}
