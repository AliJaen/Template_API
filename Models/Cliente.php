<?php

class Cliente extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function findAll(): array
    {
        $sql = "SELECT * FROM cliente";
        $clientesData = parent::query($sql);

        $clientes = []; // En caso de no haber clientes registrados se asegura de enviar un array vacío
        foreach ($clientesData as $data) {
            $clienteDTO = parent::mapToDTO($data, ClienteDTO::class);

            // Mapear al cliente
            $clientes[] = $clienteDTO;
        }

        return $clientes;
    }

    public static function findByID(int $clienteID): ClienteDTO | false
    {
        $sql = "SELECT * FROM cliente WHERE id_cliente = :id_cliente";
        $clientesData = parent::query($sql, ["id_cliente" => $clienteID]);

        if ($clientesData === []) {
            return false;
        }

        // siempre retornará al menos 1, por tanto se forza a retornar el primero
        $clienteDTO = parent::mapToDTO($clientesData[0], ClienteDTO::class);

        return $clienteDTO;
    }

    public static function createCliente(ClienteDTO $clienteDTO): string | false
    {
        try {
            // Iniciar la transacción´
            self::$db->beginTransaction();

            // Realizar el insert
            $sql = "INSERT INTO cliente (nombre_cliente, num_cliente) VALUES (:nombre_cliente, :num_cliente)";
            parent::query($sql, [
                'nombre_cliente' => $clienteDTO->nombre_cliente,
                'num_cliente' => $clienteDTO->num_cliente,
            ]);

            // Obtener el ID del cliente
            $id_cliente = self::$db->lastInsertId();

            // Confirmar la transacción
            self::$db->commit();

            return $id_cliente; // Retornar el ID del cliente creado
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            self::$db->rollBack();
            throw $e;
        }
    }

    public static function updateCliente(ClienteDTO $clienteDTO)
    {
        try {
            // Iniciar la transacción´
            self::$db->beginTransaction();

            // Realizar el insert
            $sql = "UPDATE cliente SET
                        nombre_cliente = :nombre_cliente
                    WHERE id_cliente = :id_cliente";
            parent::query($sql, [
                'nombre_cliente' => $clienteDTO->nombre_cliente,
                'id_cliente' => $clienteDTO->id_cliente
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
