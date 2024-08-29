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

class VentaController extends Controller
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
        $jwt = Venta::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }

        try {
            $ventas = Venta::findAll();
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $ventas
        );
        exit();
    }

    public function findByID($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendErrorResponse(405, "Method not allowed");
        }

        // Decodificar el token
        $jwt = Venta::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }

        if ($id === "") {
            $this->sendErrorResponse(400, "Missing ID parameter");
        }

        try {
            $venta = Venta::findByID(Utils::validateData($id));
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Internal server error", $e->getMessage());
        }

        if ($venta === false) {
            $this->sendErrorResponse(404, "Venta not found");
        }

        // Respuesta exitosa
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(
            $venta
        );
        exit();
    }

    public function createVenta()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendErrorResponse(405, "Method not allowed");
        }

        // Decodificar el token
        $jwt = Venta::decodeJWT();
        if ($jwt === null) {
            $this->sendErrorResponse(401, "Unauthorized token");
        }

        $jsonData = json_decode(file_get_contents('php://input'), true);
        if ($jsonData === null) {
            $this->sendErrorResponse(400, "Invalid JSON format");
        }

        // Validar que la solicitud contenga los campos requeridos
        $requiredFields = [
            "cliente" => [
                "id_cliente",
                "nombre_cliente",
                "num_cliente",
            ],
            "total",
        ];
        $valido = Utils::validateArrayData($requiredFields, $jsonData);
        if ($valido["valido"] === false) {
            $this->sendErrorResponse(400, $valido["message"]);
        }

        // Validar que el cliente existe
        try {
            $sql = "SELECT * FROM cliente WHERE id_cliente = :id_cliente";
            $clienteExist = Venta::query($sql, ['id_cliente' => Utils::validateData($jsonData['cliente']['id_cliente'])]);

            if ($clienteExist === []) {
                $this->sendErrorResponse(400, "Invalid cliente", "Cliente " . $jsonData['cliente']['nombre_cliente'] . " not exist");
            }
        } catch (Exception $e) {
            $this->sendErrorResponse(500, "Intenal server error", $e->getMessage());
        }

        // Realizar la misma validación por cada producto
        if (!isset($jsonData['productos'])) {
            $this->sendErrorResponse(400, "Missing productos parameter");
        }
        foreach ($jsonData['productos'] as $producto) {
            $requiredFields = [
                "id_producto",
                "cantidad",
                "precio",
                "producto" => [
                    "id_producto",
                    "nombre_producto",
                    "categoria" => [
                        "id_categoria",
                        "nombre_categoria",
                    ],
                ],
            ];
            $valido = Utils::validateArrayData($requiredFields, $producto);
            if ($valido["valido"] === false) {
                $this->sendErrorResponse(400, $valido["message"]);
            }

            if ($producto['id_producto'] !== $producto['producto']['id_producto']) {
                $this->sendErrorResponse(400, "Invalid id_producto parameter", "id_producto & producto: {id_producto} must be equals");
            }

            // Validar que el producto existe
            try {
                $sql = "SELECT * FROM producto WHERE id_producto = :id_producto";
                $productoExist = Venta::query($sql, ['id_producto' => Utils::validateData($producto['producto']['id_producto'])]);

                if ($productoExist === []) {
                    $this->sendErrorResponse(400, "Invalid producto", "Producto " . $producto['producto']['nombre_producto'] . " not exist");
                }
            } catch (Exception $e) {
                $this->sendErrorResponse(500, "Intenal server error", $e->getMessage());
            }
        }

        try {
            // Mapera los datos de la venta a la VentaDTO
            $ventaDTO = new VentaDTO();
            $ventaDTO->total = Utils::validateData($jsonData['total']);
            // Mapera los datos del cliente al ClienteDTO
            $clienteDTO = new ClienteDTO();
            $clienteDTO->id_cliente = Utils::validateData($jsonData['cliente']['id_cliente']);
            $clienteDTO->nombre_cliente = Utils::validateData($jsonData['cliente']['nombre_cliente']);
            $clienteDTO->num_cliente = Utils::validateData($jsonData['cliente']['num_cliente']);
            $ventaDTO->cliente = $clienteDTO;
            $ventaDTO->folio = Utils::generaFolio(2);
            $ventaDTO->fecha = Utils::getFechaHora();

            // Mapear los todos los productos de la venta
            foreach ($jsonData['productos'] as $producto) {
                $ventaProductoDTO = new VentaProductoDTO();
                $ventaProductoDTO->id_producto = $producto['id_producto'];
                $ventaProductoDTO->cantidad = $producto['cantidad'];
                $ventaProductoDTO->precio = $producto['precio'];
                $productoDTO = new ProductoDTO();
                $productoDTO->id_producto = Utils::validateData($producto['producto']['id_producto']);
                $productoDTO->nombre_producto = Utils::validateData($producto['producto']['id_producto']);
                $categoriaDTO = new CategoriaDTO();
                $categoriaDTO->id_categoria = Utils::validateData($producto['producto']['categoria']['id_categoria']);
                $categoriaDTO->nombre_categoria = Utils::validateData($producto['producto']['categoria']['nombre_categoria']);
                $productoDTO->categoria = $categoriaDTO;
                $ventaDTO->productos[] = $ventaProductoDTO;
            }

            // Insertar la venta con sus respectivos productos
            $id_venta = Venta::createVenta($ventaDTO);

            // Respuesta exitosa
            $url = base_url . "/Venta/findByID/" . $id_venta;
            header('Content-Type: application/json');
            header('Location: ' . $url);
            http_response_code(201);
            echo json_encode(["id_venta" => $id_venta]);
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
