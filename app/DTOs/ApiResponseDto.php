<?php

namespace App\DTOs;

class ApiResponseDto
{
    public bool $success;
    public string $message;
    public mixed $data;
    public ?array $errors;
    public int $status;

    public function __construct(
        bool $success,
        string $message,
        mixed $data = null,
        ?array $errors = null,
        int $status = 200
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
        $this->status = $status;
    }

    public function toArray()
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
            'status' => $this-> status
        ];
    }
}