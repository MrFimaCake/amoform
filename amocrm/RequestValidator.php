<?php

namespace amocrm;

/**
 * Call it to validate form
 *
 * Class RequestValidator
 * @package amocrm
 */
class RequestValidator
{
    protected $data = [];
    protected $errors;

    protected $rules = [];

    public function __construct(array $requestArgs)
    {
        $this->data = $requestArgs;
    }

    /**
     * Dynamically sets the validation rules
     *
     * @param array $rules
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Validate with set rules
     *
     * @return bool
     */
    public function validate()
    {
        //create storage for errors for each request key
        $this->errors = array_combine(
            array_keys($this->rules),
            array_fill(0, count($this->rules), [])
        );

        $validate = true;

        foreach ($this->rules as $field => $ruleList) {
            $checkRules = (array) $ruleList;
            foreach ($checkRules as $checkRule) {
                switch ($checkRule) {
                    case "required":
                        if (!isset($this->data[$field]) || !$this->data[$field]) {
                            $this->addError($field, 'Field `%s` is required');
                            $validate = false;
                        }
                        break;
                    case "email":
                        if (!filter_var($this->data[$field] ?? "", FILTER_VALIDATE_EMAIL)) {
                            $this->addError($field, 'Email is not valid');
                            $validate = false;
                        }
                        break;

                    default:
                        break;
                }
            }
        }

        return $validate;
    }

    public function addError($field, $error)
    {
        $this->errors[$field][] = sprintf($error, ucfirst($field));
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getField($field)
    {
        return $this->data[$field];
    }

    public function getFields()
    {
        return $this->data;
    }
}