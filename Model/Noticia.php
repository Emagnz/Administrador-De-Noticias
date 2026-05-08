<?php

class Noticia {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }


    public function obtenerTodas() {
        $sql = "SELECT n.noticia_id, n.noticia_titulo, n.noticia_descripcion, 
                       n.noticia_imagen, n.noticia_fechaCreado, n.noticia_fechaPublicado,
                       n.noticia_estado, e.estado_nombre, u.nombre_usuario
                FROM noticias n
                JOIN estados e ON n.noticia_estado = e.estado_id
                JOIN usuarios u ON n.noticia_autor = u.id_usuario
                ORDER BY n.noticia_fechaCreado DESC";

        $result = $this->conn->query($sql);
        $noticias = [];
        while ($fila = $result->fetch_assoc()) {
            $noticias[] = $fila;
        }
        return $noticias;
    }


    public function obtenerPorId($id) {
        $sql = "SELECT n.noticia_id, n.noticia_titulo, n.noticia_descripcion,
                    n.noticia_imagen, n.noticia_fechaCreado, n.noticia_fechaPublicado,
                    n.noticia_estado, n.noticia_autor, e.estado_nombre, u.nombre_usuario
                FROM noticias n
                JOIN estados e ON n.noticia_estado = e.estado_id
                JOIN usuarios u ON n.noticia_autor = u.id_usuario
                WHERE n.noticia_id = '$id'";

        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }
    
    
    public function existeTitulo($titulo, $id_excluir = null) {
        $sql = "SELECT noticia_id FROM noticias 
                WHERE noticia_titulo = '$titulo'
                AND noticia_estado = 4";

        if ($id_excluir) {
            $sql .= " AND noticia_id != '$id_excluir'";
        }

        $result = $this->conn->query($sql);
        return $result->num_rows > 0;
    }


    public function obtenerPorEstado($estado_id) {
        $sql = "SELECT n.noticia_id, n.noticia_titulo, n.noticia_descripcion,
                       n.noticia_imagen, n.noticia_fechaCreado, n.noticia_fechaPublicado,
                       n.noticia_estado, e.estado_nombre, u.nombre_usuario
                FROM noticias n
                JOIN estados e ON n.noticia_estado = e.estado_id
                JOIN usuarios u ON n.noticia_autor = u.id_usuario
                WHERE n.noticia_estado = '$estado_id'
                ORDER BY n.noticia_fechaCreado DESC";

        $result = $this->conn->query($sql);
        $noticias = [];
        while ($fila = $result->fetch_assoc()) {
            $noticias[] = $fila;
        }
        return $noticias;
    }


    public function crear($titulo, $descripcion, $autor_id, $imagen = null) {
        $titulo      = $this->conn->real_escape_string($titulo);
        $descripcion = $this->conn->real_escape_string($descripcion);

        $sql = "INSERT INTO noticias (noticia_titulo, noticia_descripcion, noticia_autor, noticia_estado, noticia_imagen)
                VALUES ('$titulo', '$descripcion', '$autor_id', 1, " . ($imagen ? "'$imagen'" : "NULL") . ")";

        if (!$this->conn->query($sql)) {
            return false;
        }

        $id_nueva = $this->conn->insert_id;
        $this->registrarHistorial($id_nueva, $autor_id, null, 1);

        return true;
    }



    public function editar($id, $titulo, $descripcion, $imagen = null) {
        $titulo      = $this->conn->real_escape_string($titulo);
        $descripcion = $this->conn->real_escape_string($descripcion);

        if ($imagen) {
            $sql = "UPDATE noticias 
                    SET noticia_titulo = '$titulo',
                        noticia_descripcion = '$descripcion',
                        noticia_imagen = '$imagen'
                    WHERE noticia_id = '$id'";
        } else {
            $sql = "UPDATE noticias 
                    SET noticia_titulo = '$titulo',
                        noticia_descripcion = '$descripcion'
                    WHERE noticia_id = '$id'";
        }

        return $this->conn->query($sql);
    }


    public function cambiarEstado($id, $estado_nuevo, $usuario_id, $registrarHistorial = true) {

        $sql_actual  = "SELECT noticia_estado FROM noticias WHERE noticia_id = '$id'";
        $result      = $this->conn->query($sql_actual);
        $noticia     = $result->fetch_assoc();
        $estado_anterior = $noticia['noticia_estado'];

        // Evitar cambios innecesarios
        if ($estado_anterior == $estado_nuevo) {
            return true;
        }

        $fecha_publicado = "";
        if ($estado_nuevo == 4) {
            $fecha_publicado = ", noticia_fechaPublicado = CURDATE()";
        }

        $sql = "UPDATE noticias 
                SET noticia_estado = '$estado_nuevo'
                $fecha_publicado
                WHERE noticia_id = '$id'";

        if (!$this->conn->query($sql)) {
            return false;
        }

        // Registrar solo si corresponde
        if ($registrarHistorial) {
            $this->registrarHistorial($id, $usuario_id, $estado_anterior, $estado_nuevo);
        }

        return true;
    }


    public function verificarExpiracion($dias) {

        $dias = (int) $dias;

        // Reactivar noticias expiradas que aun no cumplieron los nuevos dias
        $sqlReactivar = "UPDATE noticias 
                        SET noticia_estado = 4
                        WHERE noticia_estado = 5
                        AND DATEDIFF(CURDATE(), noticia_fechaPublicado) < $dias";

        $this->conn->query($sqlReactivar);

        // Expirar noticias publicadas que ya cumplieron los dias
        $sqlExpirar = "SELECT noticia_id FROM noticias 
                    WHERE noticia_estado = 4
                    AND DATEDIFF(CURDATE(), noticia_fechaPublicado) >= $dias";

        $result = $this->conn->query($sqlExpirar);

        while ($fila = $result->fetch_assoc()) {
            $this->cambiarEstado($fila['noticia_id'], 5, null);
        }

        return true;
    }

    
    // Métodos de historial


    private function registrarHistorial($noticia_id, $usuario_id, $estado_anterior, $estado_nuevo) {
        $usuario_sql     = $usuario_id ? "'$usuario_id'" : "NULL";
        $estado_anterior_sql = $estado_anterior ? "'$estado_anterior'" : "NULL";

        $sql = "INSERT INTO historial_noticias 
                    (historial_noticia, historial_usuario, historial_estadoAnterior, historial_estadoPosterior)
                VALUES 
                    ('$noticia_id', $usuario_sql, $estado_anterior_sql, '$estado_nuevo')";

        return $this->conn->query($sql);
    }


    public function obtenerHistorial($noticia_id) {
        $sql = "SELECT h.historial_fecha,
                       u.nombre_usuario,
                       ea.estado_nombre AS estado_anterior,
                       ep.estado_nombre AS estado_posterior,
                       h.historial_observacion
                FROM historial_noticias h
                JOIN usuarios u ON h.historial_usuario = u.id_usuario
                LEFT JOIN estados ea ON h.historial_estadoAnterior = ea.estado_id
                JOIN estados ep ON h.historial_estadoPosterior = ep.estado_id
                WHERE h.historial_noticia = '$noticia_id'
                ORDER BY h.historial_fecha DESC";

        $result = $this->conn->query($sql);
        $historial = [];
        while ($fila = $result->fetch_assoc()) {
            $historial[] = $fila;
        }
        return $historial;
    }

}