<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $tags = Tag::all();

        Contact::factory()->count(20)->make()->each(function (Contact $contact) use ($categories, $tags) {
            $contact->category_id = $categories->random()->id;
            $contact->save();

            $tagIds = $tags->random(rand(1, min(3, $tags->count())))->pluck('id')->toArray();
            $contact->tags()->attach($tagIds);
        });
    }
}
