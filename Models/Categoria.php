<?php

class Categoria extends Model
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
        $sql = "SELECT * FROM categoria";
        $categoriasData = parent::query($sql);

        $categorias = []; // En caso de no haber categorías se asegura de enviar un array vacío
        foreach ($categoriasData as $data) {
            $categoriaDTO = parent::mapToDTO($data, CategoriaDTO::class);

            // Mapear la categoría
            $categorias[] = $categoriaDTO;
        }

        return $categorias;
    }

    /**
     * @param int $categoriaID
     * @return CategoriaDTO|false
     */
    public static function findByID(int $categoriaID): CategoriaDTO | false
    {
        $sql = "SELECT * FROM categoria WHERE id_categoria = :id_categoria";
        $categoriasData = parent::query($sql, ["id_categoria" => $categoriaID]);

        if ($categoriasData === []) {
            return false;
        }

        // siempre retornará al menos 1, por tanto se forza a retornar el primero
        return parent::mapToDTO($categoriasData[0], CategoriaDTO::class);
    }

    /**
     * @param CategoriaDTO $categoriaDTO
     * @return string|false
     * @throws Exception
     */
    public static function createCategoria(CategoriaDTO $categoriaDTO): string | false
    {
        try {
            // Iniciar la transacción
            self::$db->beginTransaction();

            // Realizar el insert
            $sql = "INSERT INTO categoria (nombre_categoria) VALUES (:nombre_categoria)";
            parent::query($sql, [
                'nombre_categoria' => $categoriaDTO->nombre_categoria,
            ]);

            // Obtener el ID de la categoría creada
            $id_categoria = self::$db->lastInsertId();

            // Confirmar la transacción
            self::$db->commit();

            return $id_categoria; // Retornar el ID de la categoría creada
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            self::$db->rollBack();
            throw $e;
        }
    }

    /**
     * @param CategoriaDTO $categoriaDTO
     * @return bool
     * @throws Exception
     */
    public static function updateCategoria(CategoriaDTO $categoriaDTO): bool
    {
        try {
            // Iniciar la transacción
            self::$db->beginTransaction();

            // Realizar el insert
            $sql = "UPDATE categoria SET
                    nombre_categoria =  :nombre_categoria
                    WHERE id_categoria = :id_categoria";
            parent::query($sql, [
                'nombre_categoria' => $categoriaDTO->nombre_categoria,
                'id_categoria' => $categoriaDTO->id_categoria,
            ]);

            // Confirmar la transacción
            self::$db->commit();

            return true; // Retornar que ha sido actualizado
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            self::$db->rollBack();
            throw $e;
        }
    }
}
