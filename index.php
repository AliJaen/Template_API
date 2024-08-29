<?php
require_once "Config/Config.php";
require_once "Config/App/autoload.php";

$ruta = !empty($_GET['url']) ? $_GET['url'] : CONTROLLER_DEFAULT . "/" . METHOD_DEFAULT;
$array = explode("/", $ruta);
$controller = $array[0] . "Controller";
$method = METHOD_DEFAULT;
$param = "";

/**
 * Obtener el controlador de la ruta (a partir del / que sigue a la URL principal)
 */
if (!empty($array[1])) {
    if (!empty($array[1] != '')) {
        $method = $array[1];
    }
}

/**
 * Obtener los parámetros de la ruta (a partir del / que sigue al correspondiente al controlador)
 */
if (!empty($array[2])) {
    if (!empty($array[2] != '')) {
        for ($i = 2; $i < count($array); $i++) {
            $param .= $array[$i] . ",";
        }
        $param = trim($param, ",");
    }
}

// Determinar si hay controlador
$didController = CONTROLLER . "/{$controller}.php";
$errorController = CONTROLLER . "/" . CONTROLLER_ERROR . ".php";

// Verifica si existe el controlador
if (file_exists($didController)) {
    require_once $didController;
    $controller = new $controller();

    // Verifica si existe el método en el controlador
    if (method_exists($controller, $method)) {
        $controller->$method($param);

        // Si no existe el método
    } else {
        require_once $errorController;
        $controller = new Error404;
        $controller->index();
    }

    // Si no existe el controlador
} else {
    require_once $errorController;
    $controller = new Error404;
    $controller->index();
}
