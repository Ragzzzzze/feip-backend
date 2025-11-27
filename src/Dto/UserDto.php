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

        error_log("UserDto constructor called with: name='{$name}', phoneNumber='{$phoneNumber}', password='{$password}', roles=" . json_encode($roles));

        error_log("UserDto properties set: name='{$this->name}', phoneNumber='{$this->phoneNumber}', password='{$this->password}', roles=" . json_encode($this->roles));
    }
}
