<?php

namespace App\Utils;

use Illuminate\Contracts\Validation\Rule;

class PasswordValidatorUtil implements Rule
{
    private $message = '';

public function passes($attribute, $value)
{
    $errors = [];
    
    if (strlen($value) < 12) $errors[] = '12+ characters';
    if (!preg_match('/[A-Z]/', $value)) $errors[] = 'uppercase letter';
    if (!preg_match('/[a-z]/', $value)) $errors[] = 'lowercase letter';
    if (!preg_match('/[0-9]/', $value)) $errors[] = 'number';
    if (!preg_match('/[\W_]/', $value)) $errors[] = 'special character';
    
    if (!empty($errors)) {
        $this->message = 'Missing: ' . implode(', ', $errors);
        return false;
    }
    
    return true;
}

    public function message()
    {
        return $this->message;
    }
}