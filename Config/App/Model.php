<?php
require_once VENDOR . "/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Model
{
    protected static PDO $db;

    public function __construct()
    {
        require_once "Config/App/DatabaseConnection.php";
        self::$db = (new DatabaseConnection())->getConnection();
        $this->cargaDTO();
    }

    /**
     * @return void
     */
    public function cargaDTO(): void
    {
        $dto = get_class($this) . "DTO";
        $ruta = "DTO/{$dto}.php";

        if (file_exists($ruta)) {
            require_once $ruta;
            $dto = new $dto();
        }
    }

    /**
     * @param string $sql
     * @param $params
     * @return array|false
     */
    public static function query(string $sql, $params = []): array | false
    {
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array $data
     * @param string $dtoClass
     * @return mixed
     */
    public static function mapToDTO(array $data, string $dtoClass): mixed
    {
        $dto = new $dtoClass();

        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }

        return $dto;
    }

    /**
     * Función para obtener el hash de la contraseña
     * 
     * @param string $pass La contraseña a hashear
     * @return string $pass_crypt El hash de la contraseña
     */
    public static function cryptPass(string $pass): string
    {
        return password_hash($pass, PASSWORD_DEFAULT);
    }

    /**
     * @param $id
     * @param $email
     * @param $role
     * @param $username
     * @return string
     */
    public static function generateJWT($id, $email, $role, $username): string
    {
        $time = time();
        $payload = [
            "iss" => SIGNER,
            "sub" => $username,
            "authorities" => "ROLE_$role",
            "iat" => $time,
            "exp" => $time + (60 * 60), // Una hora
            "nbf" => $time,
            "data" => [ // En caso de no necesitar datos extra, se puede eliminar la propiedad `data`
                "id" => $id,
                "email" => $email,
            ],
        ];

        $jwt = JWT::encode($payload, KEY_SIGNATURE, "HS256");

        return $jwt;
    }

    /**
     * @return array|array[]|string[]|string[][]|null
     */
    public static function decodeJWT(): ?array
    {
        $header = apache_request_headers();

        if (isset($header["Authorization"])) {
            // Obtener únicamente el TOKEN
            $header = str_replace('Bearer ', '', $header["Authorization"]);

            try {
                $decode = JWT::decode($header, new Key(KEY_SIGNATURE, "HS256"));

                // Valida el origen
                if ($decode->iss !== SIGNER) {
                    return null;
                }

                $role = str_replace('ROLE_', '', $decode->authorities); // Retirar el prefijo ROLE_
                $dataUser = get_object_vars($decode->data);
                return array_merge($dataUser, ["role" => $role]);
            } catch (Exception $e) {
                return null;
            }
        } else {
            // Manejar el caso en el que no se proporciona un token de autorización
            return null;
        }
    }
}
