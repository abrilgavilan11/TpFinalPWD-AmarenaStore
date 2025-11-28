<?php
namespace App\Models;

use \PDO;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Orden.php';
require_once __DIR__ . '/Usuario.php';


class Cliente extends BaseModel {
    // Obtener todos los clientes con cantidad de compras
    public function getAllWithCompras() {
        $sql = "SELECT u.idusuario, u.usnombre as nombre, u.usmail as email, u.usdeshabilitado as estado, u.usfecharegistro as fecharegistro,
                (SELECT COUNT(*) FROM orden o WHERE o.idusuario = u.idusuario) as compras
                FROM usuario u
                WHERE u.usdeshabilitado IS NULL
                ORDER BY u.usfecharegistro DESC";
        $stmt = $this->pdo->query($sql);
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Estado: 1 = activa, 0 = inactiva
        foreach ($clientes as &$c) {
            $c['estado'] = ($c['estado'] === null) ? 1 : 0;
        }
        return $clientes;
    }
    // Obtener cliente por ID con compras
    public function getByIdWithCompras($id) {
        $sql = "SELECT u.idusuario, u.usnombre as nombre, u.usmail as email, u.usdeshabilitado as estado, u.usfecharegistro as fecharegistro,
                (SELECT COUNT(*) FROM orden o WHERE o.idusuario = u.idusuario) as compras
                FROM usuario u
                WHERE u.idusuario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cliente) {
            $cliente['estado'] = ($cliente['estado'] === null) ? 1 : 0;
        }
        return $cliente;
    }
    // Actualizar datos básicos
    public function update($id, $data) {
        $sql = "UPDATE usuario SET usnombre = ?, usmail = ? WHERE idusuario = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['nombre'], $data['email'], $id]);
    }
    // Cambiar estado (activar/desactivar)
    public function setEstado($id, $estado) {
        if ($estado == 1) {
            $sql = "UPDATE usuario SET usdeshabilitado = NULL WHERE idusuario = ?";
        } else {
            $sql = "UPDATE usuario SET usdeshabilitado = NOW() WHERE idusuario = ?";
        }
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    // Eliminar cliente (soft delete)
    public function delete($id) {
        $sql = "UPDATE usuario SET usdeshabilitado = NOW() WHERE idusuario = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}
