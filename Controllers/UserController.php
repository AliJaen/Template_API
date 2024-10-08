<?php

// Configuración CORS
use JetBrains\PhpStorm\NoReturn;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejo de solicitudes OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse(405, "Method not allowed", "Method must be POST");
        }

        $requiredFields = ["username", "password"];
        $jsonData = $this->getJsonData($requiredFields);

        try {
            // Mapear los datos del user al UserDTO
            $userDTO = new UserDTO();
            $userDTO->username = Utils::validateData($jsonData['username']);
            $userDTO->password = Utils::validateData($jsonData['password']);

            // Validar que el usuario no esté registrado con anterioridad
            $userExist = User::findByUsername($userDTO);

            if ($userExist === false) {
                $this->sendErrorResponse(404, "User not found", "The username not exist");
            }

            // Verificar el password
            if (!password_verify(Utils::validateData($jsonData['password']), $userExist->password)) {
                $this->sendErrorResponse(401, "Unauthorized", "Invalid credentials");
            }

            // Generar el JWT
            $jwt = User::generateJWT(
                $userExist->id_user,
                $userExist->user_email,
                'ADMIN', // Modificar según los roles necesarios
                $userExist->username
            );

            // Respuesta exitosa
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode([
                "username" => $userExist->username,
                "id" => $userExist->id_user,
                "role" => "ADMIN", // Modificar según los roles necesarios
                "email" => $userExist->user_email,
                "jwt" => $jwt
            ]);
            exit();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function findAll(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Method not allowed", "Method must be GET");
        }

        // Decodificar el token
        $this->validateJWT('ADMIN');

        $users = [];
        try {
            $users = User::findAll();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $users
        );
        exit();
    }

    /**
     * @param $id
     * @return void
     */
    public function findByID($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Method not allowed", "Method must be GET");
        }

        // Decodificar el token
        $this->validateJWT('ADMIN');

        if ($id === "") {
            $this->sendErrorResponse(400, "Missing ID parameter");
        }

        $user = false;
        try {
            $user = User::findByID(Utils::validateData($id));
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        if ($user === false) {
            $this->sendErrorResponse(404, "User not found");
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $user
        );
        exit();
    }

    /**
     * @return void
     */
    public function createUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse(405, "Method not allowed", "Method must be POST");
        }

        // Decodificar el token
        $this->validateJWT('ADMIN');

        // Validar que la solicitud contenga los campos requeridos
        $requiredFields = ["username", "password", "user_email"];
        $jsonData = $this->getJsonData($requiredFields);

        try {
            // Encriptar la contraseña:
            $cryptPass = User::cryptPass(Utils::validateData($jsonData['password']));
            // Mapear los datos del user al UserDTO
            $userDTO = new UserDTO();
            $userDTO->id_user = Utils::generateUUID();
            $userDTO->username = Utils::validateData($jsonData['username']);
            $userDTO->password = $cryptPass;
            $userDTO->user_email = Utils::validateData($jsonData['user_email']);

            // Validar que el usuario no esté registrado con anterioridad
            $userExist = User::findByUsername($userDTO);

            if ($userExist !== false) {
                $this->sendErrorResponse(400, "User duplicated", "The username alredy exist");
            }

            // Realizar el INSERT
            $id_user = User::createUser($userDTO);

            // Respuesta exitosa
            $url = base_url . "/User/findByID/" . $id_user;
            header('Content-Type: application/json');
            header('Location: ' . $url);
            http_response_code(201);
            // echo json_encode(["id_user" => $id_user]);
            exit();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }
    }

    /**
     * @param int $code
     * @param string $message
     * @param string $detail
     * @return void
     */
    #[NoReturn] private function sendErrorResponse(int $code, string $message, string $detail = ""): void
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            "error" =>  $message,
            "detail" => $detail
        ]);
        exit();
    }

    /**
     * @param string $permissions
     * @return void
     */
    private function validateJWT(string $permissions = ''): void
    {
        $jwt = User::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }

        if ($permissions !== '') {
            if ($jwt['role'] !== $permissions) {
                $this->sendErrorResponse(401, "Unauthorized");
            }
        }
    }

    /**
     * @param array $requiredFields
     * @return mixed
     */
    private function getJsonData(array $requiredFields): mixed
    {
        $jsonData = json_decode(file_get_contents('php://input'), true);
        if ($jsonData === null) {
            $this->sendErrorResponse(400, "Invalid JSON format");
        }

        // Validar que la solicitud contenga los campos requeridos
        $valido = Utils::validateArrayData($requiredFields, $jsonData);
        if ($valido["valido"] === false) {
            $this->sendErrorResponse(400, $valido["message"]);
        }
        return $jsonData;
    }
}
