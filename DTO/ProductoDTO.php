<?php
require_once 'CategoriaDTO.php';

class ProductoDTO
{
    public $id_producto;
    public $nombre_producto;
    public $categoria;

    public function __construct($id_producto = null, $nombre_producto = null,  CategoriaDTO $categoria = null)
    {
        $this->id_producto = $id_producto;
        $this->nombre_producto = $nombre_producto;
        $this->categoria = $categoria;
    }
}
