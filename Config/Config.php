<?php

const base_url = 'http://192.168.1.119/testTemplate';
const host_ws = 'localhost';
const port_ws = 8080;

/**
 * DB constants
 */
const DB_HOST = "localhost";
const DB_NAME = "pruebaid"; // Es la base de datos por defecto, pero el constructor de la conección acepta una base de datos para manejar diferentes bases de datos en el mismo proyecto
const DB_USER = "root"; // Usa el usuario correspondiente
const DB_PASSWORD = "mYp@ssW0rd"; // Modifica la contraseña configurada en tu servidor
const DB_CHARSET = "utf8";
const KEY_SIGNATURE = "d756468975ccf5f5c2567f2a00d3a0afe4096370329421ad5afd5bd28da8c6a7";
const SIGNER = "JWTTEST-BACKEND";

/**
 * Directorios de la APP
 */
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));
define('VENDOR', ROOT . DS . 'vendor');
define('CONTROLLER', ROOT . DS . 'Controllers');

/**
 * Controlador, Método & Error por defecto
 */
define('CONTROLLER_DEFAULT', 'Login');
define('METHOD_DEFAULT', 'index');
define('CONTROLLER_ERROR', 'Error404');
