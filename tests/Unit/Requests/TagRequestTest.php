<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_tag_validation_requires_unique_name_within_length(): void
    {
        Tag::create(['name' => '既存タグ']);

        $valid = Validator::make([
            'name' => '新規タグ',
        ], (new StoreTagRequest)->rules());

        $duplicate = Validator::make([
            'name' => '既存タグ',
        ], (new StoreTagRequest)->rules());

        $tooLong = Validator::make([
            'name' => str_repeat('a', 51),
        ], (new StoreTagRequest)->rules());

        $this->assertTrue($valid->passes());
        $this->assertTrue($duplicate->fails());
        $this->assertTrue($tooLong->fails());
    }

    public function test_update_tag_validation_allows_same_name_but_rejects_other_existing_name(): void
    {
        $editing = Tag::create(['name' => '編集対象']);
        $other = Tag::create(['name' => '他タグ']);

        $sameNameRequest = UpdateTagRequest::create('/admin/tags/'.$editing->id, 'PUT', [
            'name' => '編集対象',
        ]);
        $sameNameRequest->setRouteResolver(fn () => new class($editing)
        {
            public function __construct(private Tag $tag) {}

            public function parameter(string $name): ?Tag
            {
                return $name === 'tag' ? $this->tag : null;
            }
        });

        $duplicateRequest = UpdateTagRequest::create('/admin/tags/'.$editing->id, 'PUT', [
            'name' => $other->name,
        ]);
        $duplicateRequest->setRouteResolver(fn () => new class($editing)
        {
            public function __construct(private Tag $tag) {}

            public function parameter(string $name): ?Tag
            {
                return $name === 'tag' ? $this->tag : null;
            }
        });

        $sameNameValidator = Validator::make($sameNameRequest->all(), $sameNameRequest->rules());
        $duplicateValidator = Validator::make($duplicateRequest->all(), $duplicateRequest->rules());

        $this->assertTrue($sameNameValidator->passes());
        $this->assertTrue($duplicateValidator->fails());
        $this->assertArrayHasKey('name', $duplicateValidator->errors()->toArray());
    }
}
