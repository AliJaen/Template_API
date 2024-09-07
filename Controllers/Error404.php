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


class Error404 extends Controller
{
    /**
     * @return void
     */
    #[NoReturn] public function index(): void
    {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode([
            "error" =>  "URL not found"
        ]);
        exit();
    }
}
