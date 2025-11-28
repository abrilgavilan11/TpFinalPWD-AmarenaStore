<?php
namespace App\Models;

class User extends BaseModel
{
    /**
     * Busca un usuario por su dirección de email.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE usmail = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca un usuario por su ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE idusuario = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Autentica a un usuario verificando su email y contraseña (hasheada).
     * TEMPORALMENTE SIN HASH: Método inseguro para desarrollo
     */
    public function authenticate(string $email, string $password)
    {
        $user = $this->findByEmail($email);
        
        // TEMPORAL: Compara la contraseña en texto plano. ¡No usar en producción!
        if ($user && $user['usdeshabilitado'] === null && $password === $user['uspass']) {
            return $user;
        }

    // ...existing code...
        return false;
    }
    
    /**
     * Crea un nuevo usuario en la base de datos con una contraseña hasheada.
     * Valida unicidad de email
     */
    public function create(array $data)
    {
        try {
            // Verificar que el email no exista
            $existing = $this->findByEmail($data['email']);
            if ($existing) {
                return false;
            }
            
            $stmt = $this->pdo->prepare("INSERT INTO usuario (usnombre, usmail, uspass) VALUES (?, ?, ?)");
            $stmt->execute([
                $data['usnombre'],
                $data['email'],
                $data['password'] // TEMPORAL: Se guarda la contraseña en texto plano
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (\Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el rol principal de un usuario.
     */
    public function getRole(int $userId): ?string
    {
        $stmt = $this->pdo->prepare("SELECT r.rodescripcion 
                FROM usuariorol ur
                JOIN rol r ON ur.idrol = r.idrol
                WHERE ur.idusuario = ?
                LIMIT 1");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['rodescripcion'] : null;
    }

    /**
     * Obtiene todos los roles de un usuario.
     * Nuevo método para múltiples roles
     */
    public function getRoles(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT r.* 
                FROM usuariorol ur
                JOIN rol r ON ur.idrol = r.idrol
                WHERE ur.idusuario = ?
                ORDER BY r.rodescripcion");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Asigna un rol a un usuario.
     * Nuevo método para asignar roles
     */
    public function assignRole(int $userId, int $roleId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO usuariorol (idusuario, idrol) VALUES (?, ?)");
            $stmt->execute([$userId, $roleId]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al asignar rol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los usuarios (para admin).
     * Nuevo método para listar usuarios
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->prepare("SELECT u.*, GROUP_CONCAT(r.rodescripcion) as roles
                FROM usuario u
                LEFT JOIN usuariorol ur ON u.idusuario = ur.idusuario
                LEFT JOIN rol r ON ur.idrol = r.idrol
                WHERE u.usdeshabilitado IS NULL
                GROUP BY u.idusuario
                ORDER BY u.usnombre");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Deshabilita un usuario (soft delete).
     * Nuevo método para deshabilitar usuarios
     */
    public function disable(int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE usuario SET usdeshabilitado = NOW() WHERE idusuario = ?");
            $stmt->execute([$userId]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al deshabilitar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia la contraseña de un usuario.
     * Nuevo método seguro para cambiar contraseña
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE usuario SET uspass = ? WHERE idusuario = ?");
            $stmt->execute([$newPassword, $userId]); // TEMPORAL: Se guarda la contraseña en texto plano
            return true;
        } catch (\Exception $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar perfil del usuario
     */
    public function updateProfile(int $userId, string $name, string $email, string $phone = '', string $address = ''): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE usuario 
                SET usnombre = ?, usmail = ?, ustelefono = ?, usdireccion = ? 
                WHERE idusuario = ?
            ");
            return $stmt->execute([$name, $email, $phone, $address, $userId]);
        } catch (\Exception $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar contraseña del usuario
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        try {
            // Por ahora usamos hash, pero mantenemos compatibilidad con el sistema actual
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare("UPDATE usuario SET uspassword = ? WHERE idusuario = ?");
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (\Exception $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear token de recuperación de contraseña
     */
    public function createPasswordResetToken(int $userId, string $token, string $expiry): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                token = VALUES(token), 
                expires_at = VALUES(expires_at), 
                created_at = NOW()
            ");
            return $stmt->execute([$userId, $token, $expiry]);
        } catch (\Exception $e) {
            error_log("Error al crear token de recuperación: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Cambia el estado de un usuario (activo/inactivo)
     */
    public function setEstado(int $userId, int $estado): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE usuario SET usestado = ? WHERE idusuario = ?");
            $stmt->execute([$estado, $userId]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al cambiar estado de usuario: " . $e->getMessage());
            return false;
        }
    }
}
