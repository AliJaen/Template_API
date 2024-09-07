<?php

class CategoriaDTO
{
    public int| null $id_categoria;
    public string| null $nombre_categoria;

    public function __construct($id_categoria = null, $nombre_categoria = null)
    {
        $this->id_categoria = $id_categoria;
        $this->nombre_categoria = $nombre_categoria;
    }
}
