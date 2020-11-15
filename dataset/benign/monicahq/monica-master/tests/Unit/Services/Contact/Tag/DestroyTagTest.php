<?php

namespace Tests\Unit\Services\Contact\Tag;

use Tests\TestCase;
use App\Models\Contact\Tag;
use App\Models\Account\Account;
use App\Models\Contact\Contact;
use App\Services\Contact\Tag\DestroyTag;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DestroyTagTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_destroys_a_tag()
    {
        $contact = factory(Contact::class)->create([]);

        $tag = factory(Tag::class)->create([
            'account_id' => $contact->account_id,
        ]);

        $contact->tags()->syncWithoutDetaching([
            $tag->id => [
                'account_id' => $contact->account_id,
            ],
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'account_id' => $contact->account_id,
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);

        $request = [
            'account_id' => $contact->account_id,
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ];

        app(DestroyTag::class)->execute($request);

        $this->assertDatabaseMissing('contact_tag', [
            'account_id' => $contact->account_id,
            'contact_id' => $contact->id,
        ]);

        $this->assertDatabaseMissing('tags', [
            'account_id' => $contact->account_id,
            'id' => $tag->id,
        ]);
    }

    /** @test */
    public function it_fails_if_wrong_parameters_are_given()
    {
        $request = [
            'account_id' => 1,
        ];

        $this->expectException(ValidationException::class);

        app(DestroyTag::class)->execute($request);
    }

    /** @test */
    public function it_throws_an_exception_if_tag_does_not_exist()
    {
        $account = factory(Account::class)->create();

        $request = [
            'account_id' => $account->id,
            'tag_id' => 123232,
        ];

        $this->expectException(ModelNotFoundException::class);
        app(DestroyTag::class)->execute($request);
    }
}
