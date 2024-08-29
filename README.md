# Plantilla API Rest

Esta plantilla está hecha para realizar proyectos pequeños utilizando únicamente PHP y MySQL.
Ha sido creada y probada en un servidor Apache, por lo que se recomienda utilizar en dicho servidor (debido a la configuración del archivo .htaccess).
Puede ser consumida con JavaScript nativo o incluso con frameworks front-end como Angular(probada).
Implementa el uso de JWT (firebase) para autenticación por medio de tokens antes de cada consulta.

## Instalación

Después de clonar el repositorio ejecutar el comando `composer install` en la carpeta del proyecto para instalar el JWT
Es necesario modificar la base de datos y las consultas para incorporarla a las necesidades de tu proyecto.
Sin embargo se implementan unos métodos básicos para realizar consultas simples como prueba.
Configura los datos de tu conexión MySQL
Para el uso de JWT agrega una contraseña (secretKey) en el archivo de configuración: `KEY_SIGNATURE` y `SIGNER` para crear la firma se puede utilizar alguna herramienta como:
https://tools.keycdn.com/sha256-online-generator

## Forma de uso

Crear un DTO (Data Transfer Object, Objeto de Transferencia de Datos), para esta versión se utilizan para representar a la Entidad que almacena la base de datos por tabla (debe especificarse en el nombre del archivo y de la clase la palabra DTO para un correcto funcionamiento. Ejemplo UserDTO.php).
Debe crearse un Modelo el cuál se encarga de las consultas (debe llevar el nombre de la Entidad para un correcto funcionamiento. Ejemplo User).
Una vez creado el Modelo se Genera el Controlador (debe especificarse en el nombre del archivo y de la clase la palabra Controller para un correcto funcionamiento. Ejemplo UserController.php).
En el controlador se especifica el método que se espera (GET, POST, PUT, etc.)(Ajustar las OPTIONS según sea necesario, en este ejemplo no se implementa el DELETE).
Para implementar la autorización JWT se debe llamar a la clase decodeJWT que se hereda del Modelo el cual hereda de DB. Este se encarga de detectar y validar el TOKEN, por lo que no es necesario realizar nada más que la validación verdadero/falso.
Las validaciones de los métodos UPDATE, INSERT, SELECT, DELETE deben ser realizadas según las necesidades. En los ejemplos se muestran algúnas formas de validar la respuesta correcta de la base de datos.

## Implementación de nuevas funcionalidades

Como se menciona en la forma de uso, la nueva versión implementa los DTO para simplificar el manejo de los Objetos de las peticiones y las Entidades de la base de datos.
También se agregan unas funciones para realizar algunas validaciones de los campos requeridos en las solicitudes. Adatptar las validaciones según sea necesario.
Agrega funciones para simplificar la creación de propiedades que requieran campos únicos y aleatorios, como folios o incluso UUID para emplearse en la creaciónd de ID en la BD.
