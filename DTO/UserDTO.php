<?php

class UserDTO
{
    public string | null $id_user;
    public string | null $username;
    public string | null $password;
    public string | null $user_email;

    public function __construct($id_user = null, $username = null, $password = null, $user_email = null)
    {
        $this->id_user = $id_user;
        $this->username = $username;
        $this->password = $password;
        $this->user_email = $user_email;
    }
}
