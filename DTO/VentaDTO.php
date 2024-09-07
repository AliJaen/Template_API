<?php
require_once 'ClienteDTO.php';
require_once 'VentaProductoDTO.php';

class VentaDTO
{
    public int | null $id_venta;
    public ClienteDTO | int | null $cliente;
    public string | null $folio;
    public string | null $fecha;
    public int | null $total;
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
