<?php

class Utils
{
    /**
     * Función para obtener el hash de la contraseña
     * 
     * @param string $value El texto que se validará
     * @return string $value El texto validado
     */
    public static function validateData(string $value): string
    {
        // Elimina espacios innecesarios al principio y al final
        $value = trim($value);
        // Sanitiza los datos con htmlspecialchars para evitar inyecciones HTML
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Función para validar que los campos existan y tengan un valor diferente de NULL o vacío
     * 
     * @param array $campos un array con los campos a validar, puede ser una cadena o un array para objetos anidados
     * @param array $data un array con los datos a validar
     * @return array $valido Retorna si es válido o no y un mensaje con los detalles del valor inválido
     */
    public static function validateArrayData(array $campos, array $data): array
    {
        try {
            $valido = ["valido" => true, "message" => "valid"];

            foreach ($campos as $key => $campo) {
                if (is_array($campo)) {
                    // Si es un sub-array, entonces hay un objeto anidado que necesita validación
                    if (isset($data[$key])) {
                        $subValidacion = self::validateArrayData($campo, $data[$key]);
                        if (!$subValidacion["valido"]) {
                            return $subValidacion; // Retornar inmediatamente si falla la validación del sub-objeto
                        }
                    } else {
                        return ["valido" => false, "message" => "Missing parameter $key"];
                    }
                } else {
                    // Validar un campo simple
                    if (isset($data[$campo])) {
                        $campoSaneado = self::validateData($data[$campo]);
                        if ($campoSaneado === "") {
                            return ["valido" => false, "message" => "Missing or empty parameter $campo"];
                        }
                    } else {
                        return ["valido" => false, "message" => "Missing parameter $campo"];
                    }
                }
            }

            return $valido;
        } catch (Exception $e) {
            return ["valido" => false, "message" => $e->getMessage()];
        }
    }

    /**
     * Función para obtener la FECHA actual
     * @return string $now Es la fecha y hora en formato YYYY-MM-DD HH-MM-SS
     */
    public static function getFechaHora(): string
    {
        // Determinar el momento actual para establecer como HORA DE CANCELACIÓN
        date_default_timezone_set('America/Mazatlan');
        return date('Y-m-d H:i:s');
    }

    /**
     * Función para generar folios, números de cliente,
     * entre otros datos que deben tener un valor único
     *
     * @param int $tipo es el correspondiente al tipo de
     *              valor que desea generar
     * @return string $folio Es el folio generado
     * @throws Exception
     */
    public static function generaFolio(int $tipo): string
    {
        date_default_timezone_set('America/Mazatlan'); // Modifica a tu zona horaria
        $fechaHora = date('Ymd'); // Fotmato YYYYMMDD
        $uuid = bin2hex(random_bytes(2));
        // Configurar más formatos según sea necesario
        $codigoFolio = $tipo === 1 ? 'CID' : ($tipo === 2 ? 'TFO' : 'FOLIO');

        return $codigoFolio . '-' . $fechaHora . '-' . $uuid;
    }

    /**
     * Función para generar un UUID
     * @return string UUID v4.0 according to RFC 4122
     * @throws Exception
     */
    public static function generateUUID(): string
    {
        $data = random_bytes(16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
