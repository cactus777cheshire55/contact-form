<?php

namespace Tests\Feature\Web;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_download_csv_with_filters(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['content' => '機能改善']);
        $otherCategory = Category::factory()->create(['content' => 'その他']);

        Contact::factory()->create([
            'first_name' => '一致',
            'last_name' => '対象',
            'email' => 'csv-target@example.com',
            'gender' => 2,
            'category_id' => $category->id,
            'created_at' => '2026-07-20 09:00:00',
        ]);

        Contact::factory()->create([
            'email' => 'csv-other@example.com',
            'gender' => 1,
            'category_id' => $otherCategory->id,
            'created_at' => '2026-07-21 09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/contacts/export?keyword=csv-target@example.com&gender=2&category_id='.$category->id.'&date=2026-07-20');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('csv-target@example.com', $csv);
        $this->assertStringNotContainsString('csv-other@example.com', $csv);
    }

    public function test_export_without_filters_outputs_newest_first(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['content' => '並び順検証']);

        $older = Contact::factory()->create([
            'first_name' => '古い',
            'last_name' => 'データ',
            'category_id' => $category->id,
            'created_at' => '2026-07-01 10:00:00',
        ]);

        $newer = Contact::factory()->create([
            'first_name' => '新しい',
            'last_name' => 'データ',
            'category_id' => $category->id,
            'created_at' => '2026-07-03 10:00:00',
        ]);

        $response = $this->actingAs($user)->get('/contacts/export');
        $response->assertOk();

        $rows = array_values(array_filter(explode("\n", trim($response->streamedContent()))));
        $firstData = str_getcsv($rows[1]);

        $this->assertSame((string) $newer->id, $firstData[0]);
        $this->assertNotSame((string) $older->id, $firstData[0]);
    }

    public function test_export_rejects_invalid_filters(): void
    {
        $user = User::factory()->create();

        $invalidGender = $this->actingAs($user)->from('/admin')->get('/contacts/export?gender=9');
        $invalidGender->assertRedirect('/admin');
        $invalidGender->assertSessionHasErrors('gender');

        $invalidCategory = $this->actingAs($user)->from('/admin')->get('/contacts/export?category_id=999999');
        $invalidCategory->assertRedirect('/admin');
        $invalidCategory->assertSessionHasErrors('category_id');
    }
}
