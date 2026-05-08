<?php

class Parametro {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    // GET
    public function obtenerPorClave($clave) {
        $sql = "SELECT parametro_valor FROM parametros WHERE parametro_clave = '$clave'";
        $result = $this->conn->query($sql);
        $fila = $result->fetch_assoc();
        return $fila ? $fila['parametro_valor'] : null;
    }
    // SET
    public function actualizar($clave, $valor) {
        $sql = "UPDATE parametros SET parametro_valor = '$valor' WHERE parametro_clave = '$clave'";
        return $this->conn->query($sql);
    }

}