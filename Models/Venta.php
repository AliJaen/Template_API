<?php

class Venta extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public static function findAll(): array
    {
        $sql = "SELECT v.*, c.*
                FROM venta AS v
                LEFT JOIN cliente AS c ON v.cliente = c.id_cliente";
        $ventasData = parent::query($sql);

        $ventas = [];

        foreach ($ventasData as $data) {
            $ventasDTO = parent::mapToDTO($data, VentaDTO::class);

            // Mapear el cliente
            $clienteDTO = parent::mapToDTO($data, ClienteDTO::class);
            $ventasDTO->cliente = $clienteDTO;
            // Obtener los productos que correspondan a la venta
            $ventasDTO->productos = self::findProductosVenta($data["id_venta"]);

            $ventas[] = $ventasDTO;
        }

        return $ventas;
    }

    /**
     * @param int $ventaID
     * @return VentaDTO|false
     */
    public static function findByID(int $ventaID): VentaDTO | false
    {
        $sql = "SELECT v.*, c.*
                FROM venta AS v
                LEFT JOIN cliente AS c ON v.cliente = c.id_cliente
                WHERE id_venta = :id_venta";
        $ventaData = parent::query($sql, ["id_venta" => $ventaID]);

        if ($ventaData === []) {
            return false;
        }

        $ventaDTO = parent::mapToDTO($ventaData[0], VentaDTO::class);

        // Mapear el cliente
        $clienteDTO = parent::mapToDTO($ventaData[0], ClienteDTO::class);
        $ventaDTO->cliente = $clienteDTO;
        // Obtener los productos que correspondan a la venta
        $ventaDTO->productos = self::findProductosVenta($ventaData[0]["id_venta"]);

        return $ventaDTO;
    }

    /**
     * @param $ventaID
     * @return array
     */
    private static function findProductosVenta($ventaID): array
    {
        $sql = "SELECT vp.*, p.*, c.*
                FROM venta_producto AS vp
                LEFT JOIN producto AS p ON p.id_producto = vp.id_producto
                LEFT JOIN categoria AS c ON c.id_categoria = p.categoria
                WHERE vp.id_venta = :ventaID";
        $produdcotsVentaData = parent::query($sql, [":ventaID" => $ventaID]);

        $ventasProducto = [];
        foreach ($produdcotsVentaData as $data) {
            $ventaProductoDTO = parent::mapToDTO($data, VentaProductoDTO::class);

            // Mapear el producto
            $productoDTO = parent::mapToDTO($data, ProductoDTO::class);
            // Mapear la categoria del producto
            $categoriaDTO = parent::mapToDTO($data, CategoriaDTO::class);
            $productoDTO->categoria = $categoriaDTO;

            $ventaProductoDTO->producto = $productoDTO;

            // Agrega el producto al array de la venta
            $ventasProducto[] = $ventaProductoDTO;
        }

        return $ventasProducto;
    }

    /**
     * @param VentaDTO $ventaDTO
     * @return string|false
     * @throws Exception
     */
    public static function createVenta(VentaDTO $ventaDTO): string | false
    {
        try {
            // Iniciar la transacción
            self::$db->beginTransaction();

            // Insertar la venta
            $sql = "INSERT INTO venta (cliente, folio, fecha, total) VALUES (:cliente, :folio, :fecha, :total)";
            parent::query($sql, [
                'cliente' => $ventaDTO->cliente->id_cliente,
                'folio' => $ventaDTO->folio,
                'fecha' => $ventaDTO->fecha,
                'total' => $ventaDTO->total,
            ]);

            // Obtener el ID de la venta
            $id_vetnta = self::$db->lastInsertId();

            // Realizar el insert de cada producto a la tabla venta_producto
            foreach ($ventaDTO->productos as $ventaProductoDTO) {
                $sqlProducto = "INSERT INTO venta_producto (id_venta, id_producto, cantidad, precio) VALUES (:id_venta, :id_producto, :cantidad, :precio)";
                parent::query($sqlProducto, [
                    'id_venta' => $id_vetnta,
                    'id_producto' => $ventaProductoDTO->id_producto,
                    'cantidad' => $ventaProductoDTO->cantidad,
                    'precio' => $ventaProductoDTO->precio,
                ]);
            }

            // Una vez insertados todos los productos de la venta, confirma la transacción
            self::$db->commit();

            return $id_vetnta; // Retorna el ID de la venta creada
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            self::$db->rollBack();
            throw $e;
        }
    }
}
