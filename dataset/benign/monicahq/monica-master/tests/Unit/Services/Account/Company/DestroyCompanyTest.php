<?php

namespace Tests\Unit\Services\Account\Company;

use Tests\TestCase;
use App\Models\Account\Account;
use App\Models\Account\Company;
use Illuminate\Validation\ValidationException;
use App\Services\Account\Company\DestroyCompany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DestroyCompanyTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_destroys_a_company()
    {
        $company = factory(Company::class)->create([]);

        $request = [
            'account_id' => $company->account_id,
            'company_id' => $company->id,
        ];

        app(DestroyCompany::class)->execute($request);

        $this->assertDatabaseMissing('companies', [
            'id' => $company->id,
        ]);
    }

    /** @test */
    public function it_throws_an_exception_if_account_is_not_linked_to_company()
    {
        $account = factory(Account::class)->create([]);
        $company = factory(Company::class)->create([]);

        $request = [
            'account_id' => $account->id,
            'company_id' => $company->id,
        ];

        $this->expectException(ModelNotFoundException::class);
        app(DestroyCompany::class)->execute($request);
    }

    /** @test */
    public function it_throws_an_exception_if_ids_do_not_exist()
    {
        $request = [
            'account_id' => 11111111,
            'company_id' => 11111111,
        ];

        $this->expectException(ValidationException::class);
        app(DestroyCompany::class)->execute($request);
    }
}
