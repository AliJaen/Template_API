<?php
require_once 'VentaDTO.php';
require_once 'ProductoDTO.php';

class VentaProductoDTO
{
    public $id_venta;
    public $id_producto;
    public $cantidad;
    public $precio;

    public function __construct(VentaDTO $id_venta = null, ProductoDTO $id_producto = null, $cantidad = null, $precio = null)
    {
        $this->id_venta = $id_venta;
        $this->id_producto = $id_producto;
        $this->cantidad = $cantidad;
        $this->precio = $precio;
    }
}
