<?php
namespace App\Models;

class Usuario
{
  // Llama al procedimiento almacenado `sp_login_usuario`
  // El SP ahora recibe SOLO el usuario/email y devuelve los datos del usuario
  public static function loginWithProcedure(string $username)
  {
    // cargar helpers de DB
    require_once __DIR__ . '/../../config/db.php';

    $bd = conectarBaseDatos();

    // El procedimiento almacenado acepta solo el parámetro de usuario/email
    $stmt = $bd->prepare('CALL sp_login_usuario(:u)');
    $stmt->bindParam(':u', $username);
    $stmt->execute();

    // Obtener primer resultado (se asume que el SP devuelve una fila/JSON)
    $res = $stmt->fetch(\PDO::FETCH_ASSOC);

    return $res ?: null;
  }

  // Obtener las políticas de contraseña desde la tabla `politicas_password`
  public static function getPasswordPolicy(int $id = 1)
  {
    require_once __DIR__ . '/../../config/db.php';
    $bd = conectarBaseDatos();
    $stmt = $bd->prepare('SELECT * FROM politicas_password WHERE id_politica = :id LIMIT 1');
    $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
    $stmt->execute();
    $res = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $res ?: null;
  }

  // Actualizar la contraseña del usuario (intenta varios nombres de tabla comunes)
  public static function updatePassword(string $username, string $passwordHash)
  {
    require_once __DIR__ . '/../../config/db.php';
    $bd = conectarBaseDatos();

    $tables = ['usuarios', 'usuario', 'users'];
    foreach ($tables as $t) {
      try {
        $sql = "UPDATE `{$t}` SET `password_hash` = :h, `intentos_fallidos` = 0, `bloqueado` = 0 WHERE `username` = :u OR `email` = :u";
        $stmt = $bd->prepare($sql);
        $stmt->bindParam(':h', $passwordHash);
        $stmt->bindParam(':u', $username);
        $stmt->execute();
        if ($stmt->rowCount() > 0)
          return true;
      } catch (\Exception $e) {
        // Ignorar y probar siguiente tabla
      }
    }

    return false;
  }
}
