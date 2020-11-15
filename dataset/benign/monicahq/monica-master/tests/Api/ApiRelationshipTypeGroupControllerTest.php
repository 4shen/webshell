<?php

namespace Tests\Api;

use Tests\ApiTestCase;
use App\Models\Relationship\RelationshipTypeGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiRelationshipTypeGroupControllerTest extends ApiTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_gets_the_right_number_of_relationship_type_groups()
    {
        $user = $this->signin();

        factory(RelationshipTypeGroup::class, 10)->create([
            'account_id' => $user->account_id,
        ]);

        $response = $this->json('GET', '/api/relationshiptypegroups');

        $response->assertStatus(200);
        $decodedJson = $response->decodeResponseJson();

        $this->assertCount(
            10,
            $decodedJson['data']
        );
    }

    /** @test */
    public function it_gets_the_list_of_relationship_type_groups()
    {
        $user = $this->signin();

        factory(RelationshipTypeGroup::class)->create([
            'account_id' => $user->account_id,
            'name' => 'love',
            'delible' => 0,
        ]);
        $relationshipTypeGroup2 = factory(RelationshipTypeGroup::class)->create([
            'account_id' => $user->account_id,
            'name' => 'hate',
            'delible' => 0,
        ]);

        $response = $this->json('GET', '/api/relationshiptypegroups');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $relationshipTypeGroup2->id,
            'object' => 'relationshiptypegroup',
            'name' => 'hate',
            'delible' => false,
        ]);
    }

    /** @test */
    public function it_gets_a_specific_relationship_type_group()
    {
        $user = $this->signin();

        $relationshipTypeGroup = factory(RelationshipTypeGroup::class)->create([
            'account_id' => $user->account_id,
            'name' => 'love',
            'delible' => 0,
        ]);

        $response = $this->json('GET', '/api/relationshiptypegroups/'.$relationshipTypeGroup->id);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $relationshipTypeGroup->id,
            'object' => 'relationshiptypegroup',
            'name' => 'love',
            'delible' => false,
        ]);
    }
}
