<?php

namespace utils;

class InputValidator {
    private array $errors = [];
    
    public function validateRegistration(array $data): bool {
        $this->errors = []; // Reset errors
        
        // Check required fields
        if (!$this->validateRequired($data, ['email', 'username', 'password', 'confirmPassword'])) {
            return false;
        }
        
        // Validate email
        if (!$this->validateEmail($data['email'])) {
            return false;
        }
        
        // Validate password
        if (!$this->validatePassword($data['password'], $data['confirmPassword'])) {
            return false;
        }
        
        // Validate username
        if (!$this->validateUsername($data['username'])) {
            return false;
        }
        
        return empty($this->errors);
    }
    
    public function validateLogin(array $data): bool {
        $this->errors = []; // Reset errors
        
        // Check required fields
        if (!$this->validateRequired($data, ['email', 'password'])) {
            return false;
        }
        
        // Validate email
        if (!$this->validateEmail($data['email'])) {
            return false;
        }
        
        // Validate password isn't empty
        if (empty($data['password'])) {
            $this->errors['password'] = 'Password cannot be empty';
            return false;
        }
        
        return empty($this->errors);
    }

    private function validateRequired(array $data, array $fields): bool {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->errors[$field] = ucfirst($field) . ' is required';
                return false;
            }
        }
        return true;
    }
    
    private function validateEmail(string $email): bool {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format';
            return false;
        }
        return true;
    }
    
    private function validatePassword(string $password, string $confirmPassword): bool {
        if (strlen($password) < 8) {
            $this->errors['password'] = 'Password must be at least 8 characters long';
            return false;
        }
        
        if ($password !== $confirmPassword) {
            $this->errors['password'] = 'Passwords do not match';
            return false;
        }
        
        return true;
    }
    
    private function validateUsername(string $username): bool {
        if (strlen($username) < 3) {
            $this->errors['username'] = 'Username must be at least 3 characters long';
            return false;
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->errors['username'] = 'Username can only contain letters, numbers and underscores';
            return false;
        }
        
        return true;
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
    
    public function getFirstError(): ?string {
        return !empty($this->errors) ? array_values($this->errors)[0] : null;
    }
}