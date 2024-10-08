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

class ClienteController extends Controller
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

        $clientes = [];
        try {
            $clientes = Cliente::findAll();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $clientes
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

        $cliente = false;
        try {
            $cliente = Cliente::findByID(Utils::validateData($id));
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        if ($cliente === false) {
            $this->sendErrorResponse(404, "Cliente not found");
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $cliente
        );
        exit();
    }

    /**
     * @return void
     */
    public function createCliente(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse(405, "Method not allowed", "Method must be POST");
        }

        // Decodificar el token
        $this->validateJWT();

        $jsonData = $this->getJsonData(["nombre_cliente"]);

        try {
            // Mapear los datos del cliente al ClienteDTO
            $clienteDTO = new ClienteDTO();
            $clienteDTO->nombre_cliente = Utils::validateData($jsonData["nombre_cliente"]); // Valida los datos antes de agregarlos
            $clienteDTO->num_cliente = Utils::generaFolio(1); // Corresponde a generar un Folio/NumCliente del cliente

            // Realizar el INSERT
            $id_cliente = Cliente::createCliente($clienteDTO);

            // Respuesta exitosa
            $url = base_url . "/Cliente/findByID/" . $id_cliente;
            header('Content-Type: application/json');
            header('Location: ' . $url);
            http_response_code(201);
            echo json_encode(["id_cliente" => $id_cliente]);
            exit();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }
    }

    /**
     * @param $id
     * @return void
     */
    public function updateCliente($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendErrorResponse(405, "Method not allowed", "Method must be PUT");
        }

        // Decodificar el token
        $jwt = Cliente::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }

        if ($id === "") {
            $this->sendErrorResponse(400, "Missing ID parameter");
        }

        $cliente = false;

        try {
            $cliente = Cliente::findByID(Utils::validateData($id));
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        if ($cliente === false) {
            $this->sendErrorResponse(400, "Cliente not found");
        }

        $jsonData = $this->getJsonData(["nombre_cliente"]);

        try {
            // Modificar el ClienteDTO encontrado con los nuevos datos
            $cliente->nombre_cliente = Utils::validateData($jsonData["nombre_cliente"]); // Valida los datos antes de agregarlos

            // Realizar el UPDATE
            $success = Cliente::updateCliente($cliente);

            if ($success !== true) {
                $this->sendErrorResponse(500, "Internal server error");
            }

            // Respuesta exitosa
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(["message" => "Cliente updated"]);
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
     * @return void
     */
    private function validateJWT(): void
    {
        $jwt = Cliente::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
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
