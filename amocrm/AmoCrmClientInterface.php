<?php
/**
 * Created by PhpStorm.
 * User: mrcake
 * Date: 12/4/17
 * Time: 12:38 AM
 */

namespace amocrm;


interface AmoCrmClientInterface
{
    public function getContacts(string $queryFilters = "");

    public function createContact(array $fields, array $contactCustomFields);

    public function currentAccount();

    public function createDeal(array $fields);

    public function setDealToContact($contactId, $dealId);

    public function createTask($elementId, $elementType, $taskTypeId);
}