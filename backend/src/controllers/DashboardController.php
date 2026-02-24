<?php
namespace App\Controllers;

class DashboardController
{
  /**
   * Comprueba y registra procedimientos almacenados faltantes.
   * @param \PDO $pdo
   * @param array $procedures
   * @return void
   */
  private static function checkAndLogProcedures($pdo, array $procedures)
  {
    if (!($pdo instanceof \PDO))
      return;
    $missing = [];
    foreach ($procedures as $name) {
      try {
        $stmt = $pdo->prepare("SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = :name");
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch(2);
        if (!$row)
          $missing[] = $name;
      } catch (\Exception $e) {
        // Si falla la comprobación, registrar y continuar
        error_log("DashboardController: fallo comprobando procedimiento {$name}: " . $e->getMessage());
      }
    }
    if (!empty($missing)) {
      $msg = sprintf("[%s] DashboardController: procedimientos faltantes: %s (remote=%s)", date('c'), implode(', ', $missing), $_SERVER['REMOTE_ADDR'] ?? 'cli');
      error_log($msg);
      $logDir = __DIR__ . '/../../logs';
      if (!is_dir($logDir))
        @mkdir($logDir, 0755, true);
      @file_put_contents($logDir . '/missing_sp.log', $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
  }

  public static function obras()
  {
    header('Content-Type: application/json; charset=utf-8');
    $maybeDb = __DIR__ . '/../../config/db.php';
    if (file_exists($maybeDb))
      require_once $maybeDb;
    try {
      $pdo = conectarBaseDatos();
      self::checkAndLogProcedures($pdo, ['sp_dashboard_obras']);
      $stmt = $pdo->query('CALL sp_dashboard_obras()');
      $rows = $stmt->fetchAll(2);
      if (count($rows) === 1) {
        $row = $rows[0];
        if (is_array($row))
          $row['_debug_sp'] = 'sp_dashboard_obras';
        echo json_encode($row);
      } else {
        echo json_encode($rows);
      }
    } catch (\Exception $e) {
      // Procedimiento no disponible o error en BD: devolver estructura vacía amigable
      $defaults = [
        'total_obras' => 0,
        'en_curso' => 0,
        'presupuesto_total' => 0,
        'alertas_importantes' => [],
      ];
      echo json_encode($defaults);
    }
  }

  public static function kpis()
  {
    header('Content-Type: application/json; charset=utf-8');
    $maybeDb = __DIR__ . '/../../config/db.php';
    if (file_exists($maybeDb))
      require_once $maybeDb;
    try {
      $pdo = conectarBaseDatos();
      self::checkAndLogProcedures($pdo, ['sp_dashboard_kpis']);
      $stmt = $pdo->query('CALL sp_dashboard_kpis()');
      $rows = $stmt->fetchAll(2);
      if (count($rows) === 1) {
        $row = $rows[0];
        if (is_array($row))
          $row['_debug_sp'] = 'sp_dashboard_kpis';
        echo json_encode($row);
      } else {
        echo json_encode($rows);
      }
    } catch (\Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  public static function general()
  {
    header('Content-Type: application/json; charset=utf-8');
    $maybeDb = __DIR__ . '/../../config/db.php';
    if (file_exists($maybeDb))
      require_once $maybeDb;
    $inicio = $_GET['inicio'] ?? ($_GET['fecha_inicio'] ?? null);
    $fin = $_GET['fin'] ?? ($_GET['fecha_fin'] ?? null);
    $id_usuario = $_GET['id_usuario'] ?? $_GET['id'] ?? null;
    try {
      $pdo = conectarBaseDatos();
      self::checkAndLogProcedures($pdo, ['sp_dashboard_general']);
      $stmt = $pdo->prepare('CALL sp_dashboard_general(:inicio,:fin,:id_usuario)');
      $stmt->execute(['inicio' => $inicio, 'fin' => $fin, 'id_usuario' => $id_usuario]);
      $rows = $stmt->fetchAll(2);
      if (count($rows) === 1) {
        $row = $rows[0];
        if (is_array($row))
          $row['_debug_sp'] = 'sp_dashboard_general';
        echo json_encode($row);
      } else {
        echo json_encode($rows);
      }
    } catch (\Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  public static function financiero()
  {
    header('Content-Type: application/json; charset=utf-8');
    $maybeDb = __DIR__ . '/../../config/db.php';
    if (file_exists($maybeDb))
      require_once $maybeDb;
    $anio = $_GET['anio'] ?? date('Y');
    try {
      $pdo = conectarBaseDatos();
      self::checkAndLogProcedures($pdo, ['sp_dashboard_financiero']);
      $stmt = $pdo->prepare('CALL sp_dashboard_financiero(:anio)');
      $stmt->execute(['anio' => $anio]);
      $rows = $stmt->fetchAll(2);
      if (count($rows) === 1) {
        $row = $rows[0];
        foreach ($row as $k => $v) {
          if (is_string($v) && ($decoded = json_decode($v, true)) !== null)
            $row[$k] = $decoded;
        }
        if (is_array($row))
          $row['_debug_sp'] = 'sp_dashboard_financiero';
        echo json_encode($row);
      } else
        echo json_encode($rows);
    } catch (\Exception $e) {
      // Si falla, devolver estructura financiera vacía para que el frontend muestre mensaje amigable
      $fallback = [
        'resumen' => ['total_gastos' => 0, 'total_pagos' => 0],
        'gastos_por_mes' => [],
      ];
      echo json_encode($fallback);
    }
  }

  public static function completo()
  {
    header('Content-Type: application/json; charset=utf-8');
    $maybeDb = __DIR__ . '/../../config/db.php';
    if (file_exists($maybeDb))
      require_once $maybeDb;
    $id_usuario = $_GET['id_usuario'] ?? $_GET['id'] ?? null;
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $per_page = max(1, min(100, (int) ($_GET['per_page'] ?? 8)));
    try {
      $pdo = conectarBaseDatos();
      self::checkAndLogProcedures($pdo, ['sp_dashboard_completo']);
      $stmt = $pdo->prepare('CALL sp_dashboard_completo(:id_usuario)');
      $stmt->execute(['id_usuario' => $id_usuario]);
      $rows = $stmt->fetchAll(2);
      if (count($rows) === 1) {
        $row = $rows[0];
        foreach ($row as $k => $v) {
          if (is_string($v)) {
            $dec = json_decode($v, true);
            if ($dec !== null)
              $row[$k] = $dec;
          }
        }

        if (!empty($row['obras_en_curso']) && is_array($row['obras_en_curso'])) {
          $total = count($row['obras_en_curso']);
          $total_pages = (int) ceil($total / $per_page);
          $page = min($page, max(1, $total_pages));
          $offset = ($page - 1) * $per_page;
          $paged = array_slice($row['obras_en_curso'], $offset, $per_page);
          $row['obras_en_curso_paged'] = $paged;
          $row['obras_en_curso_pagination'] = ['total' => $total, 'page' => $page, 'per_page' => $per_page, 'total_pages' => $total_pages];
        }

        if (is_array($row))
          $row['_debug_sp'] = 'sp_dashboard_completo';
        echo json_encode($row);
      } else {
        echo json_encode($rows);
      }
    } catch (\Exception $e) {
      // Devolver estructura mínima para evitar fallos en frontend
      $fallback = [
        'alertas' => [],
        'obras_en_curso' => [],
        'obras_en_curso_paged' => [],
        'obras_en_curso_pagination' => ['total' => 0, 'page' => 1, 'per_page' => $per_page, 'total_pages' => 0],
        'metricas_generales' => [],
      ];
      echo json_encode($fallback);
    }
  }
}
