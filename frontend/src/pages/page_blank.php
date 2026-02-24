<?php
require '../utils/auth.php';
require '../utils/curl.php';
require_login();
$currentPage = $_SERVER['PHP_SELF'] ?? basename(__FILE__);
require_permission($currentPage, 'view');

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
  <!-- CSS Secundario (carga diferida) -->
  <link rel="stylesheet" href="../../node_modules/animate.css/animate.css" media="print" onload="this.media='all'">

  <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css" media="print"
    onload="this.media='all'">
  <link rel="stylesheet" href="../css/jquery.mCustomScrollbar.css" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="../../node_modules/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css"
    media="print" onload="this.media='all'">
  </link>

  <!-- Fallback para navegadores sin JavaScript -->
  <noscript>
    <link rel="stylesheet" href="../../node_modules/normalize.css/normalize.css" />
    <link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css" />
    <!-- legacy bootstrap-material-design removed -->
    <link rel="stylesheet" href="../../node_modules/animate.css/animate.css" />

    <link rel="stylesheet" href="../../node_modules/sweetalert2/dist/sweetalert2.css" />
    <link rel="stylesheet" href=".../../node_modules/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css" />
    <link rel="stylesheet" href="../../node_modules/material-icons/css/material-icons.min.css" />
  </noscript>
</head>

<body class="h-100">
  <main class="full-box main-container">
    <?php include('../components/header.php'); ?>
    <section class="full-box page-content">
      <?php include('../components/header.php'); ?>

      <section id="resumen-grid" class="full-box tile-container" aria-labelledby="tiles-heading">
        <h2 id="tiles-heading" class="sr-only">Resumen de insumos</h2>
        <div aria-live="polite">Cargando resumen…</div>
      </section>

      <div class="container-fluid ">
        <div class="dashboard-grid" style="display:grid;grid-template-columns:1fr ;gap:1rem;padding:1rem;">
          <div class="dashboard-main">
            <section class="card" style="margin-top:1rem;">
              <div class="card-header  d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Pagina en blanco</h4>
                <button id="mp_reload" class="btn btn-sm btn-outline-secondary  border-0" data-bs-toggle="tooltip"
                  title="Recargar lista"><i class="fas fa-redo"></i></button>
              </div>
              <div class="card-body">
                <div class="filters-advanced" style="margin-bottom:.75rem;">
                  <div class="filters-row" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">

                  </div>
                  <div class="filters-row"
                    style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;margin-top:.5rem;">
                    <div style="margin-left:auto;display:flex;gap:.5rem;"><button id="btnApplyFilters"
                        class="btn btn-outline-primary border-0"><i class="fas fa-filter"></i></button>
                      <button id="btnClearFilters" class="btn btn-outline-danger border-0"><i
                          class="fas fa-eraser"></i></button>
                      <button id="btnNewInsumo" class="btn btn-sm btn-outline-info border-0"><i
                          class="fas fa-plus"></i></button>
                    </div>
                  </div>
                </div>
                <div class="table-responsive">
                  <table id="tableInsumos" class="table table-striped table-hover" role="table"
                    aria-label="Tabla de Insumos">
                    <thead>
                      <tr>

                      </tr>
                    </thead>
                    <tbody id="tbodyInsumos" aria-live="polite"></tbody>
                  </table>
                  <nav aria-label="Paginación Insumos" style="display:flex;justify-content:flex-end;margin-top:.5rem;">
                    <ul id="paginationInsumos" class="pagination pagination-sm"></ul>
                  </nav>
                </div>
              </div>
            </section>
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
  <script src="../js/jquery.mCustomScrollbar.concat.min.js"></script>
  <script src="../node_modules/sweetalert2/dist/sweetalert2.js"></script>
  <script src="../node_modules/moment/min/moment.min.js"></script>
  <script src="../js/helper.js"></script>
  <script type="module" src="../js/main.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  <!-- Development version -->
  <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.js"></script>

  <!-- Production version -->
  <script src="https://unpkg.com/@popperjs/core@2"></script>


  <?php if (!empty($_SESSION['error'])): ?>
    <script>
      (function () {
        try {
          const msg = <?php echo json_encode($_SESSION['error']); ?>;
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Acceso denegado', text: msg });
          } else {
            alert('Acceso denegado: ' + msg);
          }
        } catch (e) {
          console.warn('Mostrar error sesión falló', e);
        }
      })();
    </script>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <div id="spinner-carga" hidden aria-hidden="true"
    style="position: fixed;top: 0;left: 0;width: 100vw;height: 100vh;background: rgba(255,255,255,0.7);z-index: 9999;display:flex;align-items:center;justify-content:center;">
    <div class="spinner-border text-primary" role="status" style="width:4rem;height:4rem"><span
        class="sr-only">Cargando...</span></div>
  </div>
</body>

</html>