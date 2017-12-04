<?php
/**
 * Created by PhpStorm.
 * User: mrcake
 * Date: 12/4/17
 * Time: 2:22 PM
 */

namespace amocrm;


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
     * @param $dealId
     * @return mixed
     */
    public function createTaskByDeal($dealId)
    {
        $currentAccount = $this->currentAccountInfo ?? $this->client->currentAccount();
        $taskTypes = $currentAccount->account->task_types;
        $taskTypes = array_filter($taskTypes, function ($typeObject) {
            return $typeObject->code == 'CALL';
        });

        return $this->client->createTask($dealId, ApiClient::TASK_ELEMENT_TYPE_DEAL, array_shift($taskTypes)->id);
    }

    public function linkDealToContact($contactId, $dealId)
    {
        return $this->client->setDealToContact($contactId, $dealId);
    }

    /**
     * @param $email
     * @param $phone
     * @return array|null
     */
    public function findContact($email, $phone) : ?array
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
            return [$contacts[0]->id, $contacts[0]->responsible_user_id];
        }
    }

    /**
     * @return object
     */
    public function accountInfo() : \stdClass
    {
        return $this->currentAccountInfo = $this->client->currentAccount();
    }

    /**
     * @param array $fields
     * @return object
     */
    public function createContact(array $fields) : \stdClass
    {
        $currentAccountInfo = $this->currentAccountInfo ?? $this->accountInfo();
        $customFields = $currentAccountInfo->account->custom_fields->contacts;
        $customFieldsAssoc = [];

        foreach ($customFields as $customField) {
            $customFieldsAssoc[$customField->code] = $customField;
        }

        return $contact = $this->client->createContact($fields, $customFieldsAssoc);
    }

    /**
     * @return mixed
     */
    public function getBusylessUser()
    {
        $currentAccountInfo = $this->currentAccountInfo ?? $this->accountInfo();
        array_filter($currentAccountInfo->account->users, function ($user) {
            return !$user->is_admin;
        });

        $users = [];
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
