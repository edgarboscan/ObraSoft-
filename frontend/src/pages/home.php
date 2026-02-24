<?php
$base = __DIR__ . '/..';
require '../utils/auth.php';
require '../utils/curl.php';
require_login();
$currentPage = $_SERVER['PHP_SELF'] ?? basename(__FILE__);
// require_permission($currentPage, 'view');

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ObraSoft</title>
  <link rel="icon" href="../assets/img/logo.png" type="image/png" />
  <meta name="description" content="🏗️ ObraSoft - Control de Gastos y Pago a Obreros" />
  <meta name="author" content="Ing. Edgar Boscan">
  <meta name="robots" content="index, follow">
  <!-- Normalize -->
  <link rel="icon" href="../assets/img/logo.png" type="image/png" />
  <link rel="stylesheet" href="../../node_modules/material-icons/css/material-icons.min.css" />

  <!-- CSS Libraries (optimizadas) -->
  <link rel="preload" href="../../node_modules/normalize.css/normalize.css" as="style"
    onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css" as="style"
    onload="this.onload=null;this.rel='stylesheet'">
  <!-- removed legacy bootstrap-material-design stylesheet to avoid conflicts -->
  <link rel="preload" href="../assets/css/styles.css" as="style" onload="this.onload=null;this.rel='stylesheet'">

  <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>

  <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css" media="print"
    onload="this.media='all'">
  <link rel="stylesheet" href="../../node_modules/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css"
    media="print" onload="this.media='all'">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <!-- Fallback para navegadores sin JavaScript -->
  <noscript>
    <link rel="stylesheet" href="../../node_modules/normalize.css/normalize.css" />
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <!-- legacy bootstrap-material-design removed -->
    <link rel="stylesheet" href="../../node_modules/animate.css/animate.css" />

    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css" />
    <link rel="stylesheet" href="../../node_modules/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css" />
    <link rel="stylesheet" href="../../node_modules/material-icons/css/material-icons.css" />
  </noscript>
</head>

<body class="h-100 app-page">
  <main class="full-box main-container">
    <?php include('../components/header.php'); ?>

    <style>
      /* Layout distribution: left sidebar + main content to the right */
      .app-layout {
        display: flex;
        gap: 1rem;
      }

      .app-sidebar {
        width: 260px;
        flex: 0 0 260px;
        height: calc(100vh - 64px);
        overflow: auto;
      }

      .app-main {
        flex: 1 1 auto;
        padding: 1.5rem;
        min-height: calc(100vh - 64px);
      }

      body.sidebar-collapsed .app-sidebar {
        width: 72px;
        flex: 0 0 72px;
      }
    </style>

    <div class="app-layout">
      <aside class="app-sidebar" id="appSidebar">
        <?php include('../components/menu.php'); ?>
      </aside>

      <section class="app-main">
        <div class="container-fluid">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
              <h1 style="margin:0;font-size:1.5rem;">Panel de control</h1>
              <small class="text-muted">Resumen rápido y accesos directos</small>
            </div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item active" aria-current="page">Inicio</li>
              </ol>
            </nav>
          </div>
          <!-- KPI resumen -->
          <div class="row g-3 mb-3" id="kpiRow">
            <div class="col-6 col-md-3">
              <div class="card shadow-sm h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <span class="material-icons kpi-icon text-muted me-2" aria-hidden="true"
                      title="Total obras">apartment</span>
                    <h6 class="card-title mb-0">Total obras</h6>
                  </div>
                  <div id="kpi_total_obras" style="font-size:1.4rem;font-weight:700;margin-top:.45rem;">—</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card shadow-sm h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <span class="material-icons kpi-icon text-muted me-2" aria-hidden="true"
                      title="Obras en curso">construction</span>
                    <h6 class="card-title mb-0">En curso</h6>
                  </div>
                  <div id="kpi_en_curso" style="font-size:1.4rem;font-weight:700;margin-top:.45rem;">—</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card shadow-sm h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <span class="material-icons kpi-icon text-muted me-2" aria-hidden="true"
                      title="Obreros activos">people</span>
                    <h6 class="card-title mb-0">Obreros activos</h6>
                  </div>
                  <div id="kpi_obreros" style="font-size:1.4rem;font-weight:700;margin-top:.45rem;">—</div>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card shadow-sm h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <span class="material-icons kpi-icon text-muted me-2" aria-hidden="true"
                      title="Presupuesto">attach_money</span>
                    <h6 class="card-title mb-0">Presupuesto</h6>
                  </div>
                  <div id="kpi_presupuesto" style="font-size:1.1rem;font-weight:700;margin-top:.45rem;">—</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Panel principal: alertas y tarjetas de detalle -->
          <div class="row g-3">
            <div class="col-12 col-lg-8">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h5 class="card-title">Alertas</h5>
                  <div id="alertList">Cargando alertas...</div>
                </div>
              </div>
              <div class="mt-3 card shadow-sm">
                <div class="card-body">
                  <h5 class="card-title">Obras en curso</h5>
                  <div id="obrasList">Cargando...</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-4">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h5 class="card-title">Financiero (resumen)</h5>
                  <div id="financieroSummary">Cargando...</div>
                  <div style="margin-top:.75rem;">
                    <div class="chart-container" style="height:220px;">
                      <canvas id="chartFinanciero" aria-label="Gastos por mes" style="width:100%;height:100%;"></canvas>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <!-- Debug: mostrador de SP invocados (solo desarrollo) -->
          <div class="row mb-3" id="debugRow" style="display:none;">
            <div class="col-12">
              <div class="card border-warning shadow-sm">
                <div class="card-body">
                  <h6 class="card-title">Debug SP</h6>
                  <div id="debugInfo" class="small text-monospace">Cargando...</div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Actividad reciente</h5>
                  <p class="text-muted">Aquí irán las últimas operaciones, alertas y notificaciones.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
  </main>

  <!--=============================================
  =            Include JavaScript files           =
  ==============================================-->
  <!-- JS Libraries -->
  <script src="../../node_modules/jquery/dist/jquery.js"></script>
  <script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../node_modules/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>

  <script src="../../node_modules/sweetalert2/dist/sweetalert2.js"></script>
  <script src="../../node_modules/moment/min/moment.min.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  <!-- Development version -->
  <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.js"></script>

  <!-- Production version -->
  <script src="https://unpkg.com/@popperjs/core@2"></script>
  <!-- Chart.js for dashboard charts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../../src/assets/js/home.js"></script>




  <div id="spinner-carga" hidden aria-hidden="true"
    style="position: fixed;top: 0;left: 0;width: 100vw;height: 100vh;background: rgba(255,255,255,0.7);z-index: 9999;display:flex;align-items:center;justify-content:center;">
    <div class="spinner-border text-primary" role="status" style="width:4rem;height:4rem"><span
        class="sr-only">Cargando...</span></div>
  </div>

  <script>
  </script>
</body>

</html>