<?php
class Database_Handler {
    private $conexion;

    public function __construct() {
        $this->conectar();
    }

    private function conectar() {
        $host = 'srv1439.hstgr.io';
        $db   = 'u428104143_exigo';
        $user = 'u428104143_root';
        $pass = 'F6@ioWly9!';

        try {
            $this->conexion = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            error_log('Error de conexión: ' . $e->getMessage());
        }
    }

    public function obtener_conexion() {
        return $this->conexion;
    }

    public function cerrar_conexion() {
        $this->conexion = null;
    }

    public function install() {
        $this->crear_tabla_clientes();
        $this->insertar_datos_prueba();
    }

    private function crear_tabla_clientes() {
        $sql = "CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        try {
            $this->conexion->exec($sql);
            echo "Tabla 'clientes' creada con éxito.\n";
        } catch(PDOException $e) {
            echo "Error al crear la tabla 'clientes': " . $e->getMessage() . "\n";
        }
    }

    private function insertar_datos_prueba() {
        $clientes = [
            ['nombre' => 'Juan Pérez', 'email' => 'juan@example.com'],
            ['nombre' => 'María García', 'email' => 'maria@example.com'],
            ['nombre' => 'Carlos López', 'email' => 'carlos@example.com']
        ];

        $sql = "INSERT INTO clientes (nombre, email) VALUES (:nombre, :email)";
        $stmt = $this->conexion->prepare($sql);

        foreach ($clientes as $cliente) {
            try {
                $stmt->execute($cliente);
                echo "Cliente {$cliente['nombre']} insertado con éxito.\n";
            } catch(PDOException $e) {
                echo "Error al insertar cliente {$cliente['nombre']}: " . $e->getMessage() . "\n";
            }
        }
    }
}