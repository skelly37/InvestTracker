<?php
// classes/Validator.php

class Validator {
    private $data;
    private $rules;
    private $errors = [];
    
    public function __construct(array $data, array $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    public function validate(): bool {
        $this->errors = [];
        
        foreach ($this->rules as $field => $ruleSet) {
            $this->validateField($field, $ruleSet);
        }
        
        return empty($this->errors);
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
    
    public function getFirstError(string $field): ?string {
        return $this->errors[$field][0] ?? null;
    }
    
    private function validateField(string $field, array $rules): void {
        $value = $this->data[$field] ?? null;
        
        foreach ($rules as $rule) {
            if (is_string($rule)) {
                $this->applyRule($field, $value, $rule);
            } elseif (is_array($rule) && count($rule) === 2) {
                [$ruleName, $parameter] = $rule;
                $this->applyRule($field, $value, $ruleName, $parameter);
            }
        }
    }
    
    private function applyRule(string $field, $value, string $rule, $parameter = null): void {
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, ucfirst($field) . ' is required');
                }
                break;
                
            case 'string':
                if (!is_string($value) && $value !== null) {
                    $this->addError($field, ucfirst($field) . ' must be a string');
                }
                break;
                
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid email address');
                }
                break;
                
            case 'min':
                if ($value && strlen($value) < $parameter) {
                    $this->addError($field, ucfirst($field) . " must be at least {$parameter} characters long");
                }
                break;
                
            case 'max':
                if ($value && strlen($value) > $parameter) {
                    $this->addError($field, ucfirst($field) . " must be no more than {$parameter} characters long");
                }
                break;
                
            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a number');
                }
                break;
                
            case 'alpha':
                if ($value && !preg_match('/^[a-zA-Z]+$/', $value)) {
                    $this->addError($field, ucfirst($field) . ' must contain only letters');
                }
                break;
                
            case 'alphanumeric':
                if ($value && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                    $this->addError($field, ucfirst($field) . ' must contain only letters and numbers');
                }
                break;
                
            case 'username':
                if ($value && !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                    $this->addError($field, ucfirst($field) . ' can only contain letters, numbers and underscores');
                }
                break;
                
            case 'in':
                if ($value && !in_array($value, $parameter)) {
                    $this->addError($field, ucfirst($field) . ' must be one of: ' . implode(', ', $parameter));
                }
                break;
        }
    }
    
    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    public static function make(array $data, array $rules): self {
        return new self($data, $rules);
    }
}