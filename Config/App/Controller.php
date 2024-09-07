<?php

class Controller
{
    public function __construct()
    {
        $this->cargarModel();
    }

    /**
     * @return void
     */
    public function cargarModel(): void
    {
        $model = preg_replace('/Controller$/', '', get_class($this));
        $ruta = "Models/{$model}.php";

        if (file_exists($ruta)) {
            require_once $ruta;
            $model = new $model();
        }
    }
}
