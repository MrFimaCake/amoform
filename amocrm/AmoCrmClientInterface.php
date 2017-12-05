<?php

namespace amocrm;

/**
 * Interface AmoCrmClientInterface
 * @package amocrm
 */
interface AmoCrmClientInterface
{
    public function getContacts(string $queryFilters = "");

    public function createContact(array $fields, array $contactCustomFields);

    public function currentAccount();

    public function createDeal(array $fields);

    public function setDealToContact($contactId, $dealId);

    public function createTask($elementId, $elementType, $taskTypeId, $userId);
}