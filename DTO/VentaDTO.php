<?php
require_once 'ClienteDTO.php';
require_once 'VentaProductoDTO.php';

class VentaDTO
{
    public $id_venta;
    public $cliente;
    public $folio;
    public $fecha;
    public $total;
    public $productos = [];

    public function __construct($id_venta = null, ClienteDTO $cliente = null, $folio = null, $fecha = null, $total = null)
    {
        $this->id_venta = $id_venta;
        $this->cliente = $cliente;
        $this->folio = $folio;
        $this->fecha = $fecha;
        $this->total = $total;
    }
}
