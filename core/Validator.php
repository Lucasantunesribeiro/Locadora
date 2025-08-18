<?php

class Validator
{
    private $errors = [];

    public function validate($data, $rules)
    {
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $this->validateField($field, $value, $rule);
        }

        return empty($this->errors);
    }

    private function validateField($field, $value, $rules)
    {
        $rules = explode('|', $rules);

        foreach ($rules as $rule) {
            if (strpos($rule, ':') !== false) {
                [$rule, $parameter] = explode(':', $rule, 2);
            } else {
                $parameter = null;
            }

            switch ($rule) {
                case 'required':
                    if (empty($value)) {
                        $this->errors[$field][] = 'Campo obrigatório';
                    }
                    break;

                case 'email':
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->errors[$field][] = 'Email inválido';
                    }
                    break;

                case 'min':
                    if ($value && strlen($value) < $parameter) {
                        $this->errors[$field][] = "Mínimo de {$parameter} caracteres";
                    }
                    break;

                case 'max':
                    if ($value && strlen($value) > $parameter) {
                        $this->errors[$field][] = "Máximo de {$parameter} caracteres";
                    }
                    break;

                case 'numeric':
                    if ($value && !is_numeric($value)) {
                        $this->errors[$field][] = 'Deve ser um número';
                    }
                    break;

                case 'positive':
                    if ($value && (!is_numeric($value) || $value <= 0)) {
                        $this->errors[$field][] = 'Deve ser um número positivo';
                    }
                    break;

                case 'date':
                    if ($value && !strtotime($value)) {
                        $this->errors[$field][] = 'Data inválida';
                    }
                    break;
            }
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFirstError($field)
    {
        return $this->errors[$field][0] ?? null;
    }
}