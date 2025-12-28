<?php

declare(strict_types=1);

namespace App\Dto;

class UserDto
{
    public $name;
    public $phoneNumber;
    public $password;
    public $roles;

    public function __construct($name, $phoneNumber, $password = null, $roles = [])
    {
        $this->name = $name;
        $this->phoneNumber = $phoneNumber;
        $this->password = $password;
        $this->roles = $roles;
    }
}
