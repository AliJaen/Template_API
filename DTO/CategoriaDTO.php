<?php

class CategoriaDTO
{
    public $id_categoria;
    public $nombre_categoria;

    public function __construct($id_categoria = null, $nombre_categoria = null,)
    {
        $this->id_categoria = $id_categoria;
        $this->nombre_categoria = $nombre_categoria;
    }
}
