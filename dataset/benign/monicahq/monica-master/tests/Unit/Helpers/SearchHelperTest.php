<?php

namespace Tests\Unit\Helpers;

use Tests\FeatureTestCase;
use App\Helpers\SearchHelper;
use App\Models\Contact\Contact;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SearchHelperTest extends FeatureTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function searching_for_contacts_returns_a_collection_with_pagination()
    {
        $user = $this->signin();

        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);
        $searchResults = SearchHelper::searchContacts($contact->first_name, 'created_at')
            ->paginate(1);

        $this->assertNotNull($searchResults);
        $this->assertInstanceOf('Illuminate\Pagination\LengthAwarePaginator', $searchResults);
        $this->assertCount(1, $searchResults);
    }

    /** @test */
    public function searching_with_wrong_search_field()
    {
        $user = $this->signin();

        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);
        $searchResults = SearchHelper::searchContacts('wrongsearchfield:1', 'created_at')
            ->paginate(1);

        $this->assertNotNull($searchResults);
        $this->assertInstanceOf('Illuminate\Pagination\LengthAwarePaginator', $searchResults);
        $this->assertCount(0, $searchResults);
    }
}
