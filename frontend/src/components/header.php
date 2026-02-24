<?php
$user = $_SESSION['usuario'] ?? null;
$isLogged = !empty($user);

$roleName = strtolower($user['rol_nombre'] ?? '');
$showSettings = in_array($roleName, ['Administrador', 'Operador', 'Auditor', 'Supervisor', 'Recursos Humanos']);
$displayName = htmlspecialchars(trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? '')) ?: ($user['username'] ?? 'Usuario'));
$logoSrc = htmlspecialchars($user['img_url'] ?? '../assets/img/logo.png');
?>

<style>
  .hamburger-btn {
    color: rgba(255, 255, 255, .95);
    margin-right: .6rem;
    display: inline-flex;
    align-items: center;
    padding: .25rem;
    border-radius: 6px
  }

  .hamburger-btn:hover {
    background: rgba(255, 255, 255, 0.04)
  }
</style>

<nav class="full-box navbar-info d-flex align-items-center" aria-label="Barra de navegación superior"
  style="padding:.5rem 1rem;">

  <!-- Hamburger a la izquierda -->
  <button id="btn_toggle_sidebar" class="hamburger-btn" aria-label="Alternar menú lateral" title="Alternar menú"
    style="background:transparent;border:0;padding:.25rem;">
    <i class="material-icons" aria-hidden="true">menu</i>
  </button>

  <!-- Nombre a la izquierda -->
  <div class="nav-name d-flex align-items-center" style="color:#fff;font-weight:600;font-size:1rem;margin-right:auto;">
    <button class="btn btn-sm btn-transparent show-nav-lateral" aria-label="Mostrar menú"
      style="margin-right:.6rem;color:inherit;border:0;background:transparent;padding:.25rem;">
      <!-- <i class="material-icons" aria-hidden="true" style="font-size:1rem;">menu</i> -->
    </button>
    <div style="display:flex;flex-direction:column;">
      <span style="line-height:1;"><?= $displayName ?></span>
      <small style="opacity:.85;font-size:.85rem;"><?= htmlspecialchars($user['rol_nombre'] ?? '') ?></small>
    </div>
  </div>

  <!-- Logo a la derecha con menú desplegable -->
  <div class="nav-logo d-flex align-items-center" style="position:relative;gap:.6rem;">
    <button id="header_logo_btn" aria-haspopup="true" aria-expanded="false" title="Opciones usuario"
      style="background:transparent;border:0;padding:0;cursor:pointer;color:inherit;">
      <img src="<?= $logoSrc ?>" alt="Logo"
        style="width:36px;height:36px;object-fit:cover;border-radius:4px;border:1px solid rgba(255,255,255,0.15);box-shadow:0 1px 3px rgba(0,0,0,.25);" />
    </button>

    <div id="header_logo_menu" role="menu" aria-label="Opciones usuario" hidden
      style="position:absolute;right:0;top:calc(100% + .5rem);min-width:240px;background:#fff;color:#111;border-radius:6px;box-shadow:0 8px 20px rgba(0,0,0,.2);padding:.5rem;z-index:1050;">
      <?php if ($isLogged): ?>
        <div
          style="padding:.25rem .5rem;border-bottom:1px solid rgba(0,0,0,.05);margin-bottom:.5rem;text-align:left;display:flex;gap:.5rem;align-items:center;">
          <img src="<?= $logoSrc ?>" alt="avatar"
            style="width:42px;height:42px;border-radius:6px;object-fit:cover;border:1px solid rgba(0,0,0,.06);">
          <div>
            <strong style="display:block"><?= $displayName ?></strong>
            <small style="color:rgba(0,0,0,.6);"><?= htmlspecialchars($user['rol_nombre'] ?? '') ?></small>
          </div>
        </div>
        <ul style="list-style:none;margin:0;padding:0;">
          <?php if ($showSettings): ?>
            <li><a href="./setting.php" role="menuitem"
                style="display:block;padding:.4rem .5rem;color:inherit;text-decoration:none;">⚙️ Configuración</a></li>
            <!-- <li><a href="./usuarios.php" role="menuitem"
                style="display:block;padding:.4rem .5rem;color:inherit;text-decoration:none;">👥 Listado de usuarios</a> -->
            </li>
          <?php endif; ?>
          <li><a href="./perfil.php" role="menuitem"
              style="display:block;padding:.4rem .5rem;color:inherit;text-decoration:none;">👤 Perfil</a></li>
          <li><a href="./cambiar_password.php" role="menuitem"
              style="display:block;padding:.4rem .5rem;color:inherit;text-decoration:none;">🔒 Cambiar contraseña</a></li>
          <li><a href="#" id="btn_logout" role="menuitem"
              style="display:block;padding:.4rem .5rem;color:red;text-decoration:none;">❌ Cerrar sesión</a></li>
        </ul>
      <?php else: ?>
        <div style="padding:.25rem .5rem;text-align:left;">No autenticado</div>
        <a href="../login.php" role="menuitem"
          style="display:block;padding:.4rem .5rem;color:inherit;text-decoration:none;">Iniciar sesión</a>
      <?php endif; ?>
    </div>
  </div>

</nav>

<script>
  (function () {
    const btn = document.getElementById('header_logo_btn');
    const menu = document.getElementById('header_logo_menu');
    const btnToggle = document.getElementById('btn_toggle_sidebar');
    const body = document.body;
    if (!btn || !menu) return;
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      const isHidden = menu.hasAttribute('hidden');
      if (isHidden) { menu.removeAttribute('hidden'); btn.setAttribute('aria-expanded', 'true'); }
      else { menu.setAttribute('hidden', ''); btn.setAttribute('aria-expanded', 'false'); }
    });
    if (btnToggle) {
      btnToggle.addEventListener('click', function (e) {
        e.preventDefault();
        body.classList.toggle('sidebar-collapsed');
      });
    }
    document.addEventListener('click', function (e) {
      if (!menu.contains(e.target) && !btn.contains(e.target)) { menu.setAttribute('hidden', ''); btn.setAttribute('aria-expanded', 'false'); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { menu.setAttribute('hidden', ''); btn.setAttribute('aria-expanded', 'false'); } });
  })();
</script>