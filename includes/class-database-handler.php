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

}