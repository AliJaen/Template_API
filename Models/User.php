<?php

class User extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function findAll(): array
    {
        $sql = "SELECT * FROM user";
        $usersData = parent::query($sql);

        $users = [];
        foreach ($usersData as $data) {
            $userDTO = parent::mapToDTO($data, UserDTO::class);

            // Maper el usuario
            $users[] = $userDTO;
        }

        return $users;
    }

    public static function findByID(string $userID): UserDTO | false
    {
        $sql = "SELECT * FROM user WHERE id_user = :id_user";
        $userData = parent::query($sql, ["id_user" => $userID]);

        if ($userData === []) {
            return false;
        }

        // siempre retornamrá al mnos 1, por tanto se forza a retornar el primero
        $userDTO = parent::mapToDTO($userData[0], UserDTO::class);

        return $userDTO;
    }

    public static function findByUsername(UserDTO $userDTO): UserDTO | false
    {
        $sql = "SELECT * FROM user WHERE username = :username";
        $userData = parent::query($sql, ["username" => $userDTO->username]);

        if ($userData === []) {
            return false;
        }

        // siempre retornamrá al mnos 1, por tanto se forza a retornar el primero
        $userDTO = parent::mapToDTO($userData[0], UserDTO::class);

        return $userDTO;
    }

    public static function createUser(UserDTO $userDTO): string | false
    {
        try {
            // Iniciar la transacción
            self::$db->beginTransaction();

            // Realizar el insert
            $sql = "INSERT INTO user (id_user, username, password, user_email) VALUES (:id_user, :username, :password, :user_email)";
            parent::query($sql, [
                'id_user' => $userDTO->id_user,
                'username' => $userDTO->username,
                'password' => $userDTO->password,
                'user_email' => $userDTO->user_email,
            ]);

            // Obtener el ID del usuario creado
            $id_usuario = $userDTO->id_user;

            // Confirmar la transacción
            self::$db->commit();

            return $id_usuario; // Retornar el ID del usuario creado
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            self::$db->rollBack();
            throw $e;
        }
    }
}
