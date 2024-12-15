<?php

namespace App\Helpers;

class Validator
{
    private $errors = [];

    public function validate(array $data, array $rules): array
    {
        $validatedData = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule => $ruleOptions) {
                $method = "validate" . ucfirst($rule);
                if (method_exists($this, $method)) {
                    $result = $this->$method($value, $ruleOptions);
                    if ($result === true) {
                        $value = $this->sanitize($value, $rule);
                    } else {
                        $this->errors[$field][] = $result;
                    }
                } else {
                    throw new Exception("Validation rule '$rule' does not exist.");
                }
            }

            $validatedData[$field] = $value;
        }

        return $validatedData;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function sanitize($value, $type)
    {
        switch ($type) {
            case 'string':
            case 'text':
                return filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            case 'email':
                return filter_var($value, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($value, FILTER_SANITIZE_URL);
            case 'number':
            case 'float':
                return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            default:
                return $value;
        }
    }

    private function validateString($value, $options)
    {
        if (!is_string($value)) {
            return "Value must be a string.";
        }

        if (isset($options['maxLength']) && strlen($value) > $options['maxLength']) {
            return "String exceeds maximum length of {$options['maxLength']} characters.";
        }

        return true;
    }

    private function validateNumber($value, $options)
    {
        if (!is_numeric($value)) {
            return "Value must be a number.";
        }

        return true;
    }

    private function validateFloat($value, $options)
    {
        if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return "Value must be a valid float.";
        }

        return true;
    }

    private function validateDate($value, $options)
    {
        $format = $options['format'] ?? 'Y-m-d';
        $date = DateTime::createFromFormat($format, $value);

        if (!$date || $date->format($format) !== $value) {
            return "Invalid date format. Expected format is $format.";
        }

        return true;
    }

    private function validatePhone($value, $options)
    {
        $pattern = $options['pattern'] ?? '/^\+?[0-9]{10,15}$/';
        if (!preg_match($pattern, $value)) {
            return "Invalid phone number format.";
        }

        return true;
    }

    private function validateText($value, $options)
    {
        if (!is_string($value)) {
            return "Value must be a string.";
        }

        return true;
    }

    private function validateFile($value, $options)
    {
        if (!isset($value['tmp_name'], $value['name'], $value['size'], $value['type'])) {
            return "Invalid file upload.";
        }

        if (isset($options['maxSize']) && $value['size'] > $options['maxSize']) {
            return "File exceeds maximum size of {$options['maxSize']} bytes.";
        }

        if (isset($options['mimeTypes']) && !in_array($value['type'], $options['mimeTypes'])) {
            return "Invalid file type. Allowed types are: " . implode(', ', $options['mimeTypes']) . ".";
        }

        return true;
    }

    private function validateTime($value, $options)
    {
        $pattern = $options['pattern'] ?? '/^(?:[01]\d|2[0-3]):[0-5]\d$/';
        if (!preg_match($pattern, $value)) {
            return "Invalid time format. Expected format is HH:MM.";
        }

        return true;
    }

    private function validateUrl($value, $options)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return "Invalid URL format.";
        }

        return true;
    }

    private function validateEmail($value, $options)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email address.";
        }

        return true;
    }

    private function validatePassword($value, $options)
    {
        $minLength = $options['minLength'] ?? 8;
        if (strlen($value) < $minLength) {
            return "Password must be at least $minLength characters long.";
        }

        return true;
    }
}