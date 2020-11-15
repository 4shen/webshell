<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\Contact\Note;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NoteTest extends FeatureTestCase
{
    use DatabaseTransactions;

    /**
     * Returns an array containing a user object along with
     * a contact for that user.
     * @return array
     */
    private function fetchUser()
    {
        $user = $this->signIn();

        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);

        return [$user, $contact];
    }

    public function test_user_can_add_a_note()
    {
        [$user, $contact] = $this->fetchUser();

        $noteBody = 'This is a note that I would like to see';

        $params = [
            'body' => $noteBody,
            'is_favorited' => 0,
        ];

        $response = $this->post('/people/'.$contact->hashID().'/notes', $params);

        // Assert the note has been added for the correct user.
        $this->assertDatabaseHas('notes', [
            'body' => $noteBody,
        ]);
    }

    public function test_user_can_edit_a_note()
    {
        [$user, $contact] = $this->fetchUser();

        $note = factory(Note::class)->create([
            'contact_id' => $contact->id,
            'account_id' => $user->account_id,
            'body' => 'this is a test',
            'is_favorited' => 1,
        ]);

        // now edit the note
        $params = [
            'body' => 'this is another test',
            'is_favorited' => 0,
        ];

        $this->put('/people/'.$contact->hashID().'/notes/'.$note->id, $params);

        // Assert the note has been added for the correct user.
        $this->assertDatabaseHas('notes', [
            'body' => 'this is another test',
        ]);
    }

    public function test_user_can_delete_a_note()
    {
        [$user, $contact] = $this->fetchUser();

        $note = factory(Note::class)->create([
            'contact_id' => $contact->id,
            'account_id' => $user->account_id,
            'body' => 'this is a test',
        ]);

        $response = $this->delete('/people/'.$contact->hashID().'/notes/'.$note->id);

        $params = [];
        $params['id'] = $note->id;

        $this->assertDatabaseMissing('notes', $params);
    }
}
