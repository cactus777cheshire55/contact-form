<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_contacts(): void
    {
        $category = Category::factory()->create();
        Contact::factory()->count(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->contacts);
    }

    public function test_contact_belongs_to_category_and_syncs_tags(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create(['category_id' => $category->id]);
        $tagA = Tag::create(['name' => '重要']);
        $tagB = Tag::create(['name' => '不具合']);

        $contact->tags()->sync([$tagA->id, $tagB->id]);

        $this->assertSame($category->id, $contact->category->id);
        $this->assertEqualsCanonicalizing([$tagA->id, $tagB->id], $contact->tags->pluck('id')->all());
    }

    public function test_tag_is_attached_to_multiple_contacts_through_pivot(): void
    {
        $tag = Tag::create(['name' => '要対応']);
        $contactA = Contact::factory()->create();
        $contactB = Contact::factory()->create();

        $tag->contacts()->sync([$contactA->id, $contactB->id]);

        $this->assertCount(2, $tag->contacts);
    }
}
