<?php

const base_url = 'YOUR_URL_BASE';

/**
 * DB constants
 */
const DB_HOST = "YOUR_HOST";
const DB_NAME = "prueba"; // Es la base de datos por defecto, adaptar a la base de datos requerida
const DB_USER = "YOUR_USER"; // Usa el usuario correspondiente
const DB_PASSWORD = "YOUR_PASSWORD"; // Modifica la contraseña configurada en tu servidor
const DB_CHARSET = "utf8";
const KEY_SIGNATURE = "YOUR_SIGNARUTE";
const SIGNER = "YOUR_SIGNER";

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