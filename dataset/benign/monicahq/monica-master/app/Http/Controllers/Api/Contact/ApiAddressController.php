<?php

namespace App\Http\Controllers\Api\Contact;

use Illuminate\Http\Request;
use App\Models\Contact\Address;
use App\Models\Contact\Contact;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Validation\ValidationException;
use App\Services\Contact\Address\CreateAddress;
use App\Services\Contact\Address\UpdateAddress;
use App\Services\Contact\Address\DestroyAddress;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\Address\Address as AddressResource;

class ApiAddressController extends ApiController
{
    /**
     * Get the list of addresses.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $addresses = auth()->user()->account->addresses()
                ->orderBy($this->sort, $this->sortDirection)
                ->paginate($this->getLimitPerPage());
        } catch (QueryException $e) {
            return $this->respondInvalidQuery();
        }

        return AddressResource::collection($addresses);
    }

    /**
     * Get the detail of a given address.
     *
     * @param Request $request
     *
     * @return AddressResource|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $address = Address::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        }

        return new AddressResource($address);
    }

    /**
     * Store the address.
     *
     * @param Request $request
     *
     * @return AddressResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $address = app(CreateAddress::class)->execute(
                $request->except(['account_id'])
                    +
                    [
                        'account_id' => auth()->user()->account_id,
                    ]
            );
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        } catch (ValidationException $e) {
            return $this->respondValidatorFailed($e->validator);
        } catch (QueryException $e) {
            return $this->respondInvalidQuery();
        }

        return new AddressResource($address);
    }

    /**
     * Update the address.
     *
     * @param Request $request
     * @param int $addressId
     *
     * @return AddressResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $addressId)
    {
        try {
            $address = app(UpdateAddress::class)->execute(
                $request->except(['account_id', 'address_id'])
                    +
                    [
                        'account_id' => auth()->user()->account_id,
                        'address_id' => $addressId,
                    ]
            );
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        } catch (ValidationException $e) {
            return $this->respondValidatorFailed($e->validator);
        } catch (QueryException $e) {
            return $this->respondInvalidQuery();
        }

        return new AddressResource($address);
    }

    /**
     * Delete an address.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $addressId)
    {
        try {
            app(DestroyAddress::class)->execute([
                'account_id' => auth()->user()->account_id,
                'address_id' => $addressId,
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        } catch (ValidationException $e) {
            return $this->respondValidatorFailed($e->validator);
        } catch (QueryException $e) {
            return $this->respondInvalidQuery();
        }

        return $this->respondObjectDeleted((int) $addressId);
    }

    /**
     * Get the list of addresses for the given contact.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function addresses(Request $request, $contactId)
    {
        try {
            $contact = Contact::where('account_id', auth()->user()->account_id)
                ->where('id', $contactId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound();
        }

        $addresses = $contact->addresses()
                ->paginate($this->getLimitPerPage());

        return AddressResource::collection($addresses);
    }
}
