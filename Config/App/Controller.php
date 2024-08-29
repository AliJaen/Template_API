<?php

class Controller
{
    public function __construct()
    {
        $this->cargarModel();
    }

    public function cargarModel()
    {
        $model = preg_replace('/Controller$/', '', get_class($this));
        $ruta = "Models/{$model}.php";

        if (file_exists($ruta)) {
            require_once $ruta;
            $model = new $model();
        }
    }
}
