<?php

use JetBrains\PhpStorm\NoReturn;

// Configuración CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejo de solicitudes OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

class CategoriaController extends Controller
{
    public function __construct()
    {
        parent::__construct();
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
        $this->validateJWT();

        $categorias = [];
        try {
            $categorias = Categoria::findAll();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $categorias
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
        $this->validateJWT();

        if ($id === "") {
            $this->sendErrorResponse(400, "Missing ID parameter");
        }

        $categoria = false;
        try {
            $categoria = Categoria::findByID(Utils::validateData($id));
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        if ($categoria === false) {
            $this->sendErrorResponse(404, "Categoria not found");
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $categoria
        );
        exit();
    }

    /**
     * @return void
     */
    public function createCategoria(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse(405, "Method not allowed", "Method must be POST");
        }

        // Decodificar el token
        $this->validateJWT();

        $jsonData = $this->getJsonData(["nombre_categoria"]);

        try {
            // Mapear los datos de la categoria a la CategoriaDTO
            $categoriaDTO = new CategoriaDTO();
            $categoriaDTO->nombre_categoria = Utils::validateData($jsonData["nombre_categoria"]);

            // Realizar el INSERT
            $id_categoria = Categoria::createCategoria($categoriaDTO);

            // Respuesta exitosa
            $url = base_url . "/Categoria/findByID/" . $id_categoria;
            header('Content-Type: application/json');
            header('Location: ' . $url);
            http_response_code(201);
            echo json_encode(["id_categoria" => $id_categoria]);
            exit();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }
    }

    /**
     * @param $id
     * @return void
     */
    public function updateCategoria($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendErrorResponse(405, "Method not allowed", "Method must be PUT");
        }

        // Decodificar el token
        $this->validateJWT();

        if ($id === "") {
            $this->sendErrorResponse(400, "Missing ID parameter");
        }

        $categoria = false;

        try {
            $categoria = Categoria::findByID(Utils::validateData($id));
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        if ($categoria === false) {
            $this->sendErrorResponse(400, "Categoria not found");
        }

        $jsonData = $this->getJsonData(["nombre_categoria"]);

        try {
            // Modificar la CategoriaDTO con los nuevos datos
            $categoria->nombre_categoria = Utils::validateData($jsonData["nombre_categoria"]);

            // Realizar el UPDATE
            $success = Categoria::updateCategoria($categoria);

            if ($success !== true) {
                $this->sendErrorResponse(500, "Internal server error");
            }

            // Respuesta exitosa
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(["message" => "Categoria updated"]);
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
     * @return void
     */
    private function validateJWT(): void
    {
        $jwt = Categoria::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }
    }

    /**
     * @param array $requiredFields
     * @return mixed
     */
    public function getJsonData(array $requiredFields): mixed
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
