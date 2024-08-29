<?php

class Producto extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function findAll(): array
    {
        $sql = "SELECT p.*, c.*
                FROM producto AS p
                LEFT JOIN categoria AS c ON p.categoria = c.id_categoria";
        $productosData = parent::query($sql);

        foreach ($productosData as $data) {
            $productoDTO = parent::mapToDTO($data, ProductoDTO::class);

            // Mapear la categorías
            $categoriaDTO = parent::mapToDTO($data, CategoriaDTO::class);
            $productoDTO->categoria = $categoriaDTO;

            $productos[] = $productoDTO;
        }

        return $productos;
    }

    public static function findByID(int $productoID): ProductoDTO | false
    {
        $sql = "SELECT p.*, c.*
                FROM producto AS p
                LEFT JOIN categoria AS c ON p.categoria = c.id_categoria
                WHERE id_producto = :id_producto";
        $productoData = parent::query($sql, ["id_producto" => $productoID]);

        if ($productoData === []) {
            return false;
        }

        $productoDTO = parent::mapToDTO($productoData[0], ProductoDTO::class);

        // Mapear la categorías
        $categoriaDTO = parent::mapToDTO($productoData[0], CategoriaDTO::class);
        $productoDTO->categoria = $categoriaDTO;

        return $productoDTO;
    }

    public static function createProducto(ProductoDTO $productoDTO): string | false
    {
        try {
            // Iniciar la transacción
            self::$db->beginTransaction();

            // Insertar el producto
            $sql = "INSERT INTO producto (nombre_producto, categoria) VALUES (:nombre_producto, :categoria)";
            parent::query($sql, [
                'nombre_producto' => $productoDTO->nombre_producto,
                'categoria' => $productoDTO->categoria->id_categoria,
            ]);

            // Obtener el ID del producto
            $id_producto = self::$db->lastInsertId();

            // Confirmar transacción
            self::$db->commit();

            return $id_producto; // Retornar el ID del producto creado
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            self::$db->rollBack();
            throw $e;
        }
    }

    public static function updateProducto(ProductoDTO $productoDTO)
    {
        try {
            // Iniciar la transacción
            self::$db->beginTransaction();

            // Insertar el producto
            $sql = "UPDATE producto SET
                        nombre_producto = :nombre_producto,
                        categoria = :categoria
                    WHERE id_producto = :id_producto";
            parent::query($sql, [
                'nombre_producto' => $productoDTO->nombre_producto,
                'categoria' => $productoDTO->categoria->id_categoria,
                'id_producto' => $productoDTO->id_producto,
            ]);

            // Confirmar transacción
            self::$db->commit();

            return true; // Retornar que ha sido actualizado
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            self::$db->rollBack();
            throw $e;
        }
    }
}
