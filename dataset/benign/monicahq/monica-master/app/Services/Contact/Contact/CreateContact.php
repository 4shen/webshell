<?php

namespace App\Services\Contact\Contact;

use App\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Services\BaseService;
use function Safe\json_encode;
use App\Models\Contact\Contact;
use App\Jobs\AuditLog\LogAccountAudit;
use App\Jobs\Avatars\GenerateDefaultAvatar;
use App\Jobs\Avatars\GetAvatarsFromInternet;

class CreateContact extends BaseService
{
    /**
     * Get the validation rules that apply to the service.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account_id' => 'required|integer|exists:accounts,id',
            'author_id' => 'required|integer|exists:users,id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'gender_id' => 'nullable|integer|exists:genders,id',
            'description' => 'nullable|string|max:255',
            'is_partial' => 'nullable|boolean',
            'is_birthdate_known' => 'required|boolean',
            'birthdate_day' => 'nullable|integer',
            'birthdate_month' => 'nullable|integer',
            'birthdate_year' => 'nullable|integer',
            'birthdate_is_age_based' => 'nullable|boolean',
            'birthdate_age' => 'nullable|integer',
            'birthdate_add_reminder' => 'nullable|boolean',
            'is_deceased' => 'required|boolean',
            'is_deceased_date_known' => 'required|boolean',
            'deceased_date_day' => 'nullable|integer',
            'deceased_date_month' => 'nullable|integer',
            'deceased_date_year' => 'nullable|integer',
            'deceased_date_add_reminder' => 'nullable|boolean',
        ];
    }

    /**
     * Create a contact.
     *
     * @param array $data
     * @return Contact
     */
    public function execute(array $data): Contact
    {
        $this->validate($data);

        // filter out the data that shall not be updated here
        $dataOnly = Arr::except(
            $data,
            [
                'author_id',
                'is_birthdate_known',
                'birthdate_day',
                'birthdate_month',
                'birthdate_year',
                'birthdate_is_age_based',
                'birthdate_age',
                'birthdate_add_reminder',
                'is_deceased',
                'is_deceased_date_known',
                'deceased_date_day',
                'deceased_date_month',
                'deceased_date_year',
                'deceased_date_add_reminder',
            ]
        );

        $contact = Contact::create($dataOnly);

        $this->updateBirthDayInformation($data, $contact);

        $this->updateDeceasedInformation($data, $contact);

        $this->generateUUID($contact);

        $this->addAvatars($contact);

        $this->log($data, $contact);

        // we query the DB again to fill the object with all the new properties
        $contact->refresh();

        return $contact;
    }

    /**
     * Generates a UUID for this contact.
     *
     * @param Contact $contact
     * @return void
     */
    private function generateUUID(Contact $contact)
    {
        $contact->uuid = Str::uuid()->toString();
        $contact->save();
    }

    /**
     * Add the different default avatars.
     *
     * @param Contact $contact
     * @return void
     */
    private function addAvatars(Contact $contact)
    {
        // set the default avatar color
        $contact->setAvatarColor();
        $contact->save();

        // populate the avatar from Adorable and grab the Gravatar
        GetAvatarsFromInternet::dispatch($contact);

        // also generate the default avatar
        GenerateDefaultAvatar::dispatch($contact);
    }

    /**
     * Update the information about the birthday.
     *
     * @param array $data
     * @param Contact $contact
     * @return void
     */
    private function updateBirthDayInformation(array $data, Contact $contact)
    {
        app(UpdateBirthdayInformation::class)->execute([
            'account_id' => $data['account_id'],
            'contact_id' => $contact->id,
            'is_date_known' => $data['is_birthdate_known'],
            'day' => $this->nullOrvalue($data, 'birthdate_day'),
            'month' => $this->nullOrvalue($data, 'birthdate_month'),
            'year' => $this->nullOrvalue($data, 'birthdate_year'),
            'is_age_based' => $this->nullOrvalue($data, 'birthdate_is_age_based'),
            'age' => $this->nullOrvalue($data, 'birthdate_age'),
            'add_reminder' => $this->nullOrvalue($data, 'birthdate_add_reminder'),
            'is_deceased' => $data['is_deceased'],
        ]);
    }

    /**
     * Update the information about the date of death.
     *
     * @param array $data
     * @param Contact $contact
     * @return void
     */
    private function updateDeceasedInformation(array $data, Contact $contact)
    {
        app(UpdateDeceasedInformation::class)->execute([
            'account_id' => $data['account_id'],
            'contact_id' => $contact->id,
            'is_deceased' => $data['is_deceased'],
            'is_date_known' => $data['is_deceased_date_known'],
            'day' => $this->nullOrValue($data, 'deceased_date_day'),
            'month' => $this->nullOrValue($data, 'deceased_date_month'),
            'year' => $this->nullOrValue($data, 'deceased_date_year'),
            'add_reminder' => $this->nullOrValue($data, 'deceased_date_add_reminder'),
        ]);
    }

    /**
     * Add an audit log.
     *
     * @param array $data
     * @param Contact $contact
     * @return void
     */
    private function log(array $data, Contact $contact): void
    {
        $author = User::find($data['author_id']);

        LogAccountAudit::dispatch([
            'action' => 'contact_created',
            'account_id' => $author->account_id,
            'about_contact_id' => $contact->id,
            'author_id' => $author->id,
            'author_name' => $author->name,
            'audited_at' => now(),
            'should_appear_on_dashboard' => true,
            'objects' => json_encode([
                'contact_name' => $contact->name,
                'contact_id' => $contact->id,
            ]),
        ]);
    }
}
