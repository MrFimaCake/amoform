<?php

namespace amocrm;

use stdClass;

/**
 * cUrl client for using amoCrm api
 *
 * Class ApiClient
 * @package amocrm
 */
class ApiClient implements AmoCrmClientInterface
{
    const CUSTOM_FIELD_EMAIL_ENUM_DEFAULT = 'WORK';
    const CUSTOM_FIELD_PHONE_ENUM_DEFAULT = 'OTHER';

    const TASK_ELEMENT_TYPE_CONTACT = 0;
    const TASK_ELEMENT_TYPE_DEAL = 1;
    const TASK_ELEMENT_TYPE_COMPANY = 2;

    protected $ch;

    protected $lastError;

    protected $lastInfo;

    protected $success = false;

    protected $taskElementTypes;

    protected $lastResponse;

    public function __construct()
    {
        $taskElementTypeValues = Credentials::getTaskElementTypes();

        $this->taskElementTypes = [
            self::TASK_ELEMENT_TYPE_CONTACT => $taskElementTypeValues['task_element_type_contact'],
            self::TASK_ELEMENT_TYPE_DEAL => $taskElementTypeValues['task_element_type_deal'],
            self::TASK_ELEMENT_TYPE_COMPANY => $taskElementTypeValues['task_element_type_company'],
        ];
    }


    /**
     * Cause error is in different structure every time we need check manually error in this every time
     *
     * @param stdClass $response
     * @return stdClass
     * @throws AmoCrmException
     */
    public function checkError(stdClass $response)
    {
        if (isset($response->error)) {
            throw new AmoCrmException($response->error);
        }
        return $response;
    }

    public function safeRequest($path, $query = [], $additionalSetopt = [])
    {
        $this->lastResponse = $this->executeRequest($path, $query, $additionalSetopt);

        return $this->lastResponse;
    }

    public function executeRequest($path, $query = [], $additionalSetopt = [])
    {
        $query['type'] = 'json';
        $link = 'https://' . Credentials::getSubdomain() . '.amocrm.ru'
            . '/' . $path
            . '?' . http_build_query($query);

        $curl=curl_init();

        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL,$link);
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_COOKIEFILE,Credentials::getCookieFile());
        curl_setopt($curl,CURLOPT_COOKIEJAR,Credentials::getCookieFile());
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

        foreach ($additionalSetopt as $key => $value) {
            curl_setopt($curl, $key, $value);
        }

        $out = curl_exec($curl);
        $er = curl_error($curl);

        if (strlen($er)) {
            $this->lastError = $er;
        }

        $info = curl_getinfo($curl);

        $this->success = $info['http_code'] === 200 || $info['http_code'] === 204;

        curl_close($curl);

        $this->lastError = is_array($er) ? $er : [];
        $this->lastInfo = $info;

        $safeOut = strlen($out) > 0 ? $out : json_encode(['response' => []]);
        return json_decode($safeOut)->response;
    }

    /**
     * Set/check cookies
     *
     * @return stdClass
     * @throws AmoCrmException
     */
    public function authRequest()
    {
        $path = 'private/api/auth.php';
        $user = Credentials::getUserData();
        $userPostFields = json_encode($user);
        $setopt = [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $userPostFields
        ];

        return $this->checkError($this->safeRequest($path, [], $setopt));
    }

    /**
     * @return stdClass
     * @throws AmoCrmException
     */
    public function currentAccount()
    {
        return $this->checkError($this->safeRequest('private/api/v2/json/accounts/current'));
    }

    /**
     * @param  string $queryFilter
     * @return array
     * @throws AmoCrmException
     */
    public function getContacts(string $queryFilter = "")
    {
        $addQueryFilter = $queryFilter ? ['query' => $queryFilter] : [];
        $response = $this->checkError($this->safeRequest('private/api/v2/json/contacts/list', $addQueryFilter));

        return $response->contacts ?? [];
    }

    public function getTaskElementValue($type)
    {
        return $this->taskElementTypes[$type];
    }

    /**
     * @param array $fields
     * @param array $contactCustomFields
     * @return stdClass
     * @throws AmoCrmException
     */
    public function createContact(array $fields, array $contactCustomFields) : stdClass
    {
        $createFields = [];
        $createFields['name'] = $fields['name'] ?? [];
        $createFields['type'] = 'contact';
        $createFields['custom_fields'] = [];

        $createFields['custom_fields'][] = [
            'id' => (int)$contactCustomFields['EMAIL']->id,
            'values' => [
                [
                    'value' => $fields['email'],
                    'enum' => self::CUSTOM_FIELD_EMAIL_ENUM_DEFAULT,
                ]
            ],
        ];

        $createFields['custom_fields'][] = [
            'id' => (int)$contactCustomFields['PHONE']->id,
            'values' => [
                [
                    'value' => $fields['phone'],
                    'enum' => self::CUSTOM_FIELD_PHONE_ENUM_DEFAULT,
                ]
            ],
        ];

        $finalFieldStructure = ['request' => ['contacts' => ['add' => [$createFields]]]];

        $postFieldsAsSetopt = [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($finalFieldStructure)
        ];

        return $this->checkError(
            $this->safeRequest('private/api/v2/json/contacts/set', [], $postFieldsAsSetopt)->contacts->add[0]
        );
    }

    /**
     * @param  $contactId
     * @param  $dealId
     * @return mixed
     * @throws AmoCrmException
     */
    public function setDealToContact($contactId, $dealId) {

        $updateFields = [
            'id' => $contactId,
            'linked_leads_id' => [$dealId],
            'last_modified' => time()
        ];

        $postFields = ['request' => ['contacts' => ['update' => [$updateFields]]]];

        $linked =  $this->safeRequest('private/api/v2/json/contacts/set', [], [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postFields)
        ]);

        return $this->checkError($linked);
    }

    /**
     * Creates task by contact|lead|company
     *
     * @param  $elementId
     * @param  $elementType
     * @param  $taskTypeId
     * @return object
     */
    public function createTask($elementId, $elementType, $taskTypeId)
    {
        $taskBody = [
            'element_id' => $elementId,
            'element_type' => $this->getTaskElementValue($elementType),
            'task_type' => $taskTypeId,
            'complete_till' => '23:59'
        ];

        return $this->safeRequest('private/api/v2/json/tasks/set', [], [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(['request' => ['tasks' => ['add' => [$taskBody]]]])
        ]);
    }


    /**
     * @param array $fields
     * @return mixed
     * @throws AmoCrmException
     */
    public function createDeal(array $fields)
    {
        $deal = $this->safeRequest('private/api/v2/json/leads/set', [], [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(['request' => ['leads' => ['add' => [$fields]]]])
        ]);

        return $this->checkError($deal);
    }

    public function getDeals(array $filterParams = [])
    {
        return $this->safeRequest('private/api/v2/json/leads/list', $filterParams);
    }

    public function getErrors()
    {
        return $this->lastError;
    }

    public function getInfo()
    {
        return $this->lastInfo;
    }
}
