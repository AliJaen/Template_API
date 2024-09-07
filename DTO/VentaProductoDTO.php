<?php
require_once 'VentaDTO.php';
require_once 'ProductoDTO.php';

class VentaProductoDTO
{
    public VentaDTO | int | null $id_venta;
    public ProductoDTO | int | null $id_producto;
    public int | null $cantidad;
    public int | null $precio;

    public function __construct(VentaDTO $id_venta = null, ProductoDTO $id_producto = null, $cantidad = null, $precio = null)
    {
        $this->id_venta = $id_venta;
        $this->id_producto = $id_producto;
        $this->cantidad = $cantidad;
        $this->precio = $precio;
    }
}
