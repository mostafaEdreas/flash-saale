<?php

namespace App\Services;

class ResponseService {
    private bool $success = true;
    private string $message = '';  
    private array|object $data = [] ;
    private array $additionals = [];

    public function successFalse(): self {
        $this->success = false;
        return $this;
    }   

    public function setMessage(string $message): self {
        $this->message = $message;
        return $this;
    }   

    public function setData( $data ): self {
        $this->data = $data;
        return $this;
    }


    public function additionals(array|string $attribute,  $value = null):  self {
        if (is_array($attribute) && !empty($attribute) && array_values($attribute) === $attribute) {
            throw new \InvalidArgumentException(
                'Invalid argument: The array must be associative. You passed an indexed array.'
            );
        }
        if (!is_array($attribute) && $value === null) {
            throw new \InvalidArgumentException(
                'Invalid argument: When passing a string as the first argument, the second argument must be provided.'
            );
        } 
        if (!is_array($attribute)) {
            $this->additionals[$attribute] = $value;
        }else {
            $this->additionals = array_merge($this->additionals, $attribute);

        }

        return $this;
    }

    public function toArray(): array {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            ...$this->additionals,
        ];
    }




}