<?php

class Usuario {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    

    public function login($nombre, $password) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM usuarios WHERE nombre_usuario = ?"
        );
        if (!$stmt) {
            return false; 
        }
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['contraseña_usuario'])) {
                return $user;
            }
        }

        return false;
    }

    
    public function registrar($nombre, $password, $roles) {
        $check     = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = '$nombre'";
        $resultado = $this->conn->query($check);

        if ($resultado->num_rows > 0) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre_usuario, contraseña_usuario) VALUES ('$nombre', '$hash')";
        if (!$this->conn->query($sql)) {
            return false;
        }

        $id_usuario = $this->conn->insert_id;

        foreach ($roles as $rol) {
            $sql_rol = "INSERT INTO rol_usuarios (usuario_rol_usuario, usuario_rol_rol) 
                        VALUES ('$id_usuario', '$rol')";
            if (!$this->conn->query($sql_rol)) {
                $this->conn->query("DELETE FROM usuarios WHERE id_usuario = '$id_usuario'");
                return false;
            }
        }

        return true;
    }
    public function obtenerRoles($id_usuario) {
    $sql = "SELECT usuario_rol_rol FROM rol_usuarios WHERE usuario_rol_usuario = '$id_usuario'";
    $result = $this->conn->query($sql);
    $roles = [];
    while ($fila = $result->fetch_assoc()) {
        $roles[] = $fila['usuario_rol_rol'];
    }
    return $roles;
}

}