<?php

class UserDTO
{
    public $id_user;
    public $username;
    public $password;
    public $user_email;

    public function __construct($id_user = null, $username = null, $password = null, $user_email = null)
    {
        $this->id_user = $id_user;
        $this->username = $username;
        $this->password = $password;
        $this->user_email = $user_email;
    }
}
