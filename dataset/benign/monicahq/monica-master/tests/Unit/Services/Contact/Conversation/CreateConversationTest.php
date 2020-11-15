<?php

namespace Tests\Unit\Services\Contact\Conversation;

use Tests\TestCase;
use App\Models\Account\Account;
use App\Models\Contact\Contact;
use App\Models\Contact\Conversation;
use App\Models\Contact\ContactFieldType;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Contact\Conversation\CreateConversation;

class CreateConversationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_stores_a_conversation()
    {
        $contact = factory(Contact::class)->create([]);
        $contactFieldType = factory(ContactFieldType::class)->create([
            'account_id' => $contact->account_id,
        ]);

        $request = [
            'contact_id' => $contact->id,
            'account_id' => $contact->account_id,
            'happened_at' => now(),
            'contact_field_type_id' => $contactFieldType->id,
        ];

        $conversation = app(CreateConversation::class)->execute($request);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'contact_id' => $contact->id,
            'account_id' => $contact->account_id,
            'contact_field_type_id' => $contactFieldType->id,
        ]);

        $this->assertInstanceOf(
            Conversation::class,
            $conversation
        );
    }

    /** @test */
    public function it_fails_if_wrong_parameters_are_given()
    {
        $contact = factory(Contact::class)->create([]);

        $request = [
            'contact_id' => $contact->id,
            'happened_at' => now(),
        ];

        $this->expectException(ValidationException::class);

        app(CreateConversation::class)->execute($request);
    }

    /** @test */
    public function it_throws_an_exception_if_contact_is_not_linked_to_account()
    {
        $account = factory(Account::class)->create();
        $contact = factory(Contact::class)->create();
        $contactFieldType = factory(ContactFieldType::class)->create([
            'account_id' => $contact->account_id,
        ]);

        $request = [
            'contact_id' => $contact->id,
            'account_id' => $account->id,
            'happened_at' => now(),
            'contact_field_type_id' => $contactFieldType->id,
        ];

        $this->expectException(ModelNotFoundException::class);

        app(CreateConversation::class)->execute($request);
    }

    /** @test */
    public function it_throws_an_exception_if_contactfieldtype_is_not_linked_to_account()
    {
        $contact = factory(Contact::class)->create([]);
        $contactFieldType = factory(ContactFieldType::class)->create([]);

        $request = [
            'contact_id' => $contact->id,
            'account_id' => $contact->account_id,
            'happened_at' => now(),
            'contact_field_type_id' => $contactFieldType->id,
        ];

        $this->expectException(ModelNotFoundException::class);

        app(CreateConversation::class)->execute($request);
    }
}
