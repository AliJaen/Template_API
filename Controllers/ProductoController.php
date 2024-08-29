<?php

// Configuración CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Manejo de solicitudes OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

class ProductoController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function findAll()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Method not allowed");
        }

        // Decodificar el token
        $jwt = Producto::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }

        try {
            $productos = Producto::findAll();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $productos
        );
        exit();
    }

    public function findByID($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Method not allowed");
        }

        // Decodificar el token
        $jwt = Producto::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }

        if ($id === "") {
            $this->sendErrorResponse(400, "Missing ID parameter");
        }

        try {
            $producto = Producto::findByID(Utils::validateData($id));
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        if ($producto === false) {
            $this->sendErrorResponse(404, "Producto not found");
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $producto
        );
        exit();
    }

    public function createProducto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse(405, "Method not allowed");
        }

        // Decodificar el token
        $jwt = Producto::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }

        $jsonData = json_decode(file_get_contents('php://input'), true);
        if ($jsonData === null) {
            $this->sendErrorResponse(400, "Invalid JSON format");
        }

        // Validar que la solicitud contenga los campos requeridos
        $requiredFields = [
            "nombre_producto",
            "categoria" => [
                "id_categoria", "nombre_categoria"
            ]
        ];
        $valido = Utils::validateArrayData($requiredFields, $jsonData);
        if ($valido["valido"] === false) {
            $this->sendErrorResponse(400, $valido["message"]);
        }

        // Validar que la categoría existe
        try {
            $sql = "SELECT * FROM categoria WHERE id_categoria = :id_categoria";
            $categoriaExist = Producto::query($sql, ['id_categoria' => Utils::validateData($jsonData['categoria']['id_categoria'])]);

            if ($categoriaExist === []) {
                $this->sendErrorResponse(400, "Invalid categoria", "Categoria " . $jsonData['categoria']['nombre_categoria'] . " not exist");
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Intenal server error", $e->getMessage());
        }

        try {
            // Mapera los datos del producto al ProductoDTO
            $productoDTO = new ProductoDTO();
            $productoDTO->nombre_producto = Utils::validateData($jsonData['nombre_producto']);
            $categoriaDTO = new CategoriaDTO();
            $categoriaDTO->id_categoria = Utils::validateData($jsonData['categoria']['id_categoria']);
            $categoriaDTO->nombre_categoria = Utils::validateData($jsonData['categoria']['nombre_categoria']);
            $productoDTO->categoria = $categoriaDTO;

            // Realizar el INSERT
            $id_producto = Producto::createProducto($productoDTO);

            // Respuesta exitosa
            $url = base_url . "/Producto/findByID/" . $id_producto;
            header('Content-Type: application/json');
            header('Location: ' . $url);
            http_response_code(201);
            echo json_encode(["id_producto" => $id_producto]);
            exit();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }
    }

    public function updateProducto($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendErrorResponse(405, "Method not allowed");
        }

        // Decodificar el token
        $jwt = Producto::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }

        if ($id === "") {
            $this->sendErrorResponse(400, "Missing ID parameter");
        }

        try {
            $producto = Producto::findByID(Utils::validateData($id));
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        if ($producto === false) {
            $this->sendErrorResponse(400, "Producto not found");
        }

        $jsonData = json_decode(file_get_contents('php://input'), true);
        if ($jsonData === null) {
            $this->sendErrorResponse(400, "Invalid JSON format");
        }

        // Validar que la solicitud contenga los campos requeridos
        $requiredFields = [
            "nombre_producto",
            "categoria" => [
                "id_categoria", "nombre_categoria"
            ]
        ];
        $valido = Utils::validateArrayData($requiredFields, $jsonData);
        if ($valido["valido"] === false) {
            $this->sendErrorResponse(400, $valido["message"]);
        }

        // Validar que la categoría existe
        try {
            $sql = "SELECT * FROM categoria WHERE id_categoria = :id_categoria";
            $categoriaExist = Producto::query($sql, ['id_categoria' => Utils::validateData($jsonData['categoria']['id_categoria'])]);

            if ($categoriaExist === []) {
                $this->sendErrorResponse(400, "Invalid categoria", "Categoria " . $jsonData['categoria']['nombre_categoria'] . " not exist");
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Intenal server error", $e->getMessage());
        }

        try {
            // Modificar el ClienteDTO encontrado con los nuevos datos
            $producto->nombre_producto = Utils::validateData($jsonData['nombre_producto']);
            // Obtener la categoría actual
            $categoriaDTO = $producto->categoria;
            // Establecer la nueva categoría
            $categoriaDTO->id_categoria = Utils::validateData($jsonData['categoria']['id_categoria']);
            $producto->categoria = $categoriaDTO;

            // Realizar el INSERT
            $success = Producto::updateProducto($producto);

            if ($success !== true) {
                $this->sendErrorResponse(500, "Internal server error");
            }

            // Respuesta exitosa
            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode(["message" => "Producto updated"]);
            exit();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }
    }

    private function sendErrorResponse($code, $message, $detail = "")
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            "error" =>  $message,
            "detail" =>  $detail
        ]);
        exit();
    }
}