<?php

namespace amocrm;

/**
 * Class AmoCrmRepo
 * @package amocrm
 */
class AmoCrmRepo
{
    private $client;
    private $currentAccountInfo;

    public function __construct(AmoCrmClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param $title
     * @param $userId
     * @return mixed
     */
    public function createDeal($title, $userId)
    {
        $newDeal = [
            'name' => $title,
            'responsible_user_id' => $userId
        ];

        return $this->client->createDeal($newDeal)->leads->add[0];
    }


    /**
     * Use lead data to create task
     *
     * @param $dealId
     * @param $userId
     * @return mixed
     */
    public function createTaskByDeal($dealId, $userId)
    {
        $currentAccount = $this->currentAccountInfo ?? $this->client->currentAccount();
        $taskTypes = $currentAccount->account->task_types;
        $taskTypes = array_filter($taskTypes, function ($typeObject) {
            return $typeObject->code == 'CALL';
        });

        return $this->client->createTask($dealId, ApiClient::TASK_ELEMENT_TYPE_DEAL, array_shift($taskTypes)->id, $userId);
    }

    public function linkDealToContact(array $contact, $dealId)
    {
        return $this->client->setDealToContact($contact, $dealId);
    }

    /**
     * Find contact by email and by phone
     *
     * @param $email
     * @param $phone
     * @return \stdClass|null
     */
    public function findContact($email, $phone) : ?\stdClass
    {
        //check if contact with given email exists
        $contacts = $this->client->getContacts($email);

        if (!count($contacts)) {
            //check if contact with given phone exists
            $contacts = $this->client->getContacts($phone);
        }

        if (!count($contacts)) {
            return null;
        } else {
//            return [$contacts[0]->id, $contacts[0]->responsible_user_id];
            return $contacts[0];
        }
    }

    /**
     * Get info who user belongs to
     *
     * @return object
     */
    public function accountInfo() : \stdClass
    {
        return $this->currentAccountInfo = $this->client->currentAccount();
    }

    public function updateContact($contactId, $fields, $contactInSystem)
    {
        $fields = array_merge((array)$contactInSystem, $fields);

        $customFieldsAssoc = $this->getAccountCurrentFields();

        $fields['responsible_user_id'] = $contactInSystem;
        $fields['last_modified'] = time();

        return $contact = $this->client->updateContact($contactId, $fields, $customFieldsAssoc);
    }

    protected function getAccountCurrentFields()
    {
        $currentAccountInfo = $this->currentAccountInfo ?? $this->accountInfo();
        $customFields = $currentAccountInfo->account->custom_fields->contacts;
        $customFieldsAssoc = [];

        foreach ($customFields as $customField) {
            $customFieldsAssoc[$customField->code] = $customField;
        }
        return $customFieldsAssoc;
    }

    /**
     * @param array $fields
     * @return object
     */
    public function createContact(array $fields, $responsibleUserId) : \stdClass
    {
        $customFieldsAssoc = $this->getAccountCurrentFields();

        $fields['responsible_user_id'] = $responsibleUserId;

        return $contact = $this->client->createContact($fields, $customFieldsAssoc);
    }

    /**
     * According to the task we should find user who has less tasks
     *
     * @return mixed
     */
    public function getBusylessUser()
    {
        $currentAccountInfo = $this->currentAccountInfo ?? $this->accountInfo();
        $users = array_filter($currentAccountInfo->account->users, function ($user) {
            return !$user->is_admin;
        });

        $usersToDeals = [];
        //get user with minimum deals(leads)
        foreach ($users as $user) {
            $deals = $this->client->getDeals(['responsible_user_id' => $user->id]);
            $mainContacts = [];
            if ($deals && $deals->leads) {
                foreach ($deals->leads as $lead) {
                    if (!in_array($lead->main_contact_id, $mainContacts)) {
                        $mainContacts[] = $lead->main_contact_id;
                    }
                }
            }
            $usersToDeals[$user->id] = count($mainContacts);
        }

        asort($usersToDeals, SORT_NUMERIC);
        $sortedUsers = array_keys($usersToDeals);

        return array_shift($sortedUsers);
    }
}
