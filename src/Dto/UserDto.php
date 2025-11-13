<?php

declare(strict_types=1);

namespace App\Dto;

class UserDto
{
    public $name;
    public $phoneNumber;

    public function __construct($name, $phoneNumber)
    {
        $this->name = $name;
        $this->phoneNumber = $phoneNumber;

        error_log("UserDto constructor called with: name='{$name}', phoneNumber='{$phoneNumber}'");

        error_log("UserDto properties set: name='{$this->name}', phoneNumber='{$this->phoneNumber}'");
    }
}
