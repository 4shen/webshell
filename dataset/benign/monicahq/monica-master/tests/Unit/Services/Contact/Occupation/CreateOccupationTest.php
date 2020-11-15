<?php

namespace Tests\Unit\Services\Contact\Occupation;

use Tests\TestCase;
use App\Models\Account\Account;
use App\Models\Account\Company;
use App\Models\Contact\Contact;
use App\Models\Contact\Occupation;
use Illuminate\Validation\ValidationException;
use App\Services\Contact\Occupation\CreateOccupation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateOccupationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_stores_an_occupation()
    {
        $account = factory(Account::class)->create([]);
        $contact = factory(Contact::class)->create([
            'account_id' => $account->id,
        ]);
        $company = factory(Company::class)->create([
            'account_id' => $account->id,
        ]);

        $request = [
            'account_id' => $account->id,
            'contact_id' => $contact->id,
            'company_id' => $company->id,
            'title' => 'Waiter',
        ];

        $occupation = app(CreateOccupation::class)->execute($request);

        $this->assertDatabaseHas('occupations', [
            'id' => $occupation->id,
            'account_id' => $account->id,
            'title' => 'Waiter',
            'description' => null,
        ]);

        $this->assertInstanceOf(
            Occupation::class,
            $occupation
        );
    }

    /** @test */
    public function it_fails_if_wrong_parameters_are_given()
    {
        $account = factory(Account::class)->create([]);

        $request = [
            'street' => '199 Lafayette Street',
        ];

        $this->expectException(ValidationException::class);
        app(CreateOccupation::class)->execute($request);
    }
}
