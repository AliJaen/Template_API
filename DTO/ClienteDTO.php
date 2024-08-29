<?php

class ClienteDTO
{
    public $id_cliente;
    public $nombre_cliente;
    public $num_cliente;

    public function __construct($id_cliente = null, $nombre_cliente = null, $num_cliente = null)
    {
        $this->id_cliente = $id_cliente;
        $this->nombre_cliente = $nombre_cliente;
        $this->num_cliente = $num_cliente;
    }
}
