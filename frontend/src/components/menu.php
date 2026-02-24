<section class="full-box nav-lateral">
  <div class="full-box nav-lateral-bg show-nav-lateral"></div>
  <div class="full-box nav-lateral-content" style="display:flex;flex-direction:column;height:100%;">
    <figure class="full-box nav-lateral-avatar">
      <!-- <i class="material-icons show-nav-lateral" aria-hidden="true">close</i> -->
      <img src="../assets/img/logo1.png" class="img-fluid" alt="Avatar" />
      <!-- <figcaption class="roboto-medium text-center">
        SAHUM <br /><small class="roboto-condensed-light">Hospital Universitario de
          Maracaibo</small>
      </figcaption> -->
    </figure>

    <nav class="full-box nav-lateral-menu" style="flex:1;overflow:auto;" aria-label="Menú lateral principal">
      <div style="padding:.5rem;border-bottom:1px solid rgba(0,0,0,.03);">
        <input type="search" placeholder="Buscar menú..." id="menu_search"
          style="width:100%;padding:.45rem;border-radius:6px;border:1px solid rgba(0,0,0,.06);">
      </div>
      <ul class="nav-list" style="list-style:none;margin:0;padding:.5rem;">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);

        // map common FontAwesome token to Material Icons name
        $icon_map = [
          'home' => 'home',
          'building' => 'apartment',
          'list' => 'list',
          'plus-circle' => 'add_circle',
          'user-tie' => 'person',
          'users' => 'group',
          'user-cog' => 'manage_accounts',
          'receipt' => 'receipt_long',
          'file-invoice-dollar' => 'receipt_long',
          'tags' => 'label',
          'chart-bar' => 'insert_chart',
          'hand-holding-usd' => 'paid',
          'cash-register' => 'point_of_sale',
          'history' => 'history',
          'user-shield' => 'security',
          'user-friends' => 'group',
          'user-tag' => 'badge',
          'chart-line' => 'show_chart',
          'clipboard-list' => 'assignment',
          'cogs' => 'settings',
          'shield-alt' => 'security',
          'sliders-h' => 'tune',
          'circle' => 'lens'
        ];

        // Si no hay menús en sesión, usar el menú por defecto adaptado al README
        if (empty($sessionMenus)) {
          $sessionMenus = [
            [
              'nombre' => 'Inicio',
              'url' => 'home.php',
              'icono' => 'fas fa-home',
              'permisos' => ['ver']
            ],
            [
              'nombre' => 'Obras',
              'icono' => 'fas fa-building',
              'permisos' => ['ver'],
              'children' => [
                ['nombre' => 'Listado de obras', 'url' => 'obras.php', 'icono' => 'fas fa-list', 'permisos' => ['ver']],
                ['nombre' => 'Nueva obra', 'url' => 'obra_nueva.php', 'icono' => 'fas fa-plus-circle', 'permisos' => ['ver']]
              ]
            ],
            [
              'nombre' => 'Obreros',
              'icono' => 'fas fa-user-tie',
              'permisos' => ['ver'],
              'children' => [
                ['nombre' => 'Listado de obreros', 'url' => 'obreros.php', 'icono' => 'fas fa-users', 'permisos' => ['ver']],
                ['nombre' => 'Asignación', 'url' => 'asignacion_obreros.php', 'icono' => 'fas fa-user-cog', 'permisos' => ['ver']]
              ]
            ],
            [
              'nombre' => 'Gastos',
              'icono' => 'fas fa-receipt',
              'permisos' => ['ver'],
              'children' => [
                ['nombre' => 'Registrar gasto', 'url' => 'gastos_registrar.php', 'icono' => 'fas fa-file-invoice-dollar', 'permisos' => ['ver']],
                ['nombre' => 'Categorías', 'url' => 'categorias_gasto.php', 'icono' => 'fas fa-tags', 'permisos' => ['ver']],
                ['nombre' => 'Reportes de gastos', 'url' => 'reportes/gastos.php', 'icono' => 'fas fa-chart-bar', 'permisos' => ['ver']]
              ]
            ],
            [
              'nombre' => 'Pagos Obreros',
              'icono' => 'fas fa-hand-holding-usd',
              'permisos' => ['ver'],
              'children' => [
                ['nombre' => 'Procesar pagos', 'url' => 'pagos_procesar.php', 'icono' => 'fas fa-cash-register', 'permisos' => ['ver']],
                ['nombre' => 'Historial de pagos', 'url' => 'pagos_historial.php', 'icono' => 'fas fa-history', 'permisos' => ['ver']]
              ]
            ],
            [
              'nombre' => 'Usuarios',
              'icono' => 'fas fa-user-shield',
              'permisos' => ['ver'],
              'children' => [
                ['nombre' => 'Listado usuarios', 'url' => 'usuarios.php', 'icono' => 'fas fa-user-friends', 'permisos' => ['ver']],
                ['nombre' => 'Roles & permisos', 'url' => 'roles.php', 'icono' => 'fas fa-user-tag', 'permisos' => ['ver']]
              ]
            ],
            [
              'nombre' => 'Reportes',
              'url' => 'reportes.php',
              'icono' => 'fas fa-chart-line',
              'permisos' => ['ver']
            ],
            [
              'nombre' => 'Auditoría',
              'url' => 'auditoria.php',
              'icono' => 'fas fa-clipboard-list',
              'permisos' => ['ver']
            ],
            [
              'nombre' => 'Configuración',
              'icono' => 'fas fa-cogs',
              'permisos' => ['ver'],
              'children' => [
                ['nombre' => 'Políticas de seguridad', 'url' => 'config/politicas.php', 'icono' => 'fas fa-shield-alt', 'permisos' => ['ver']],
                ['nombre' => 'Ajustes', 'url' => 'config/ajustes.php', 'icono' => 'fas fa-sliders-h', 'permisos' => ['ver']]
              ]
            ]
          ];
        }

        $isSuperUser = !empty($_SESSION['usuario']['is_super']) && ($_SESSION['usuario']['is_super'] === 1 || $_SESSION['usuario']['is_super'] === '1' || $_SESSION['usuario']['is_super'] === true);

        // recursive renderer supporting arbitrary depth
        // include $icon_map in the closure scope to avoid undefined variable errors
        $render_items = function ($items, $level = 0) use (&$render_items, $current_page, $isSuperUser, $icon_map) {
          if (!is_array($items))
            return;
          foreach ($items as $item) {
            if (is_string($item)) {
              $dec = json_decode($item, true);
              if (is_array($dec))
                $item = $dec;
            }
            if (!is_array($item) || empty($item['nombre']))
              continue;

            $perms = $item['permisos'] ?? [];
            if (is_string($perms)) {
              $p = json_decode($perms, true);
              if (is_array($p))
                $perms = $p;
              else
                $perms = array_map('trim', explode(',', $perms));
            }
            // superuser sees everything
            if ($isSuperUser) {
              $show = true;
            } else {
              // show item if no permisos specified or 'ver' present
              $show = empty($perms) || in_array('ver', $perms);
            }
            if (!$show)
              continue;

            $hasChildren = !empty($item['children']);
            $url = $item['url'] ?? '#';
            $icon = htmlspecialchars($item['icono'] ?? 'fas fa-circle');
            $label = htmlspecialchars($item['nombre']);
            // derive material icon name from configured icono (may be a FA class)
            $mi_name = 'lens';
            if (is_string($item['icono']) && preg_match('/fa-([a-z0-9\-]+)/i', $item['icono'], $m)) {
              $token = $m[1];
              if (!empty($icon_map[$token]))
                $mi_name = $icon_map[$token];
              else
                $mi_name = $token;
            }

            // determine active: match current page or any child active
            $isActive = false;
            $bp = basename($url);
            if ($bp && $bp === $current_page)
              $isActive = true;
            if ($hasChildren) {
              // check children for active
              foreach ($item['children'] as $c) {
                $carr = is_string($c) ? (json_decode($c, true) ?: []) : $c;
                if (!is_array($carr))
                  continue;
                $cbase = basename($carr['url'] ?? '');
                if ($cbase === $current_page) {
                  $isActive = true;
                  break;
                }
              }
            }

            $liClass = 'menu-item level-' . (int) $level . ($isActive ? ' active' : '');
            echo "<li class='" . $liClass . "' data-level='" . (int) $level . "'>";

            // parent link: if has children, use toggle class and '#' unless real URL provided
            $aClass = $hasChildren ? 'nav-btn-submenu' : '';
            $href = htmlspecialchars($url ?: '#');
            $href_js = json_encode($url ?: '#');

            // build safe onclick string to avoid parsing issues when concatenating
            if ($hasChildren) {
              $onclick = "event.preventDefault();this.parentNode.classList.toggle('open');";
            } else {
              $onclick = "window.location.href=" . $href_js . ';return false;';
            }

            echo "<a href='" . $href . "' class='nav-link " . $aClass . ($isActive ? ' active' : '') . "' data-label='" . htmlspecialchars($label, ENT_QUOTES) . "' onclick='" . htmlspecialchars($onclick, ENT_QUOTES) . "' aria-current='" . ($isActive ? 'page' : '') . "'>";
            echo "<i class='material-icons' aria-hidden='true' style='font-size:20px;color:inherit;'>" . htmlspecialchars($mi_name) . "</i><span class='nav-label' style='margin-left:.5rem;'>" . $label . "</span>";
            if ($hasChildren)
              echo " <i class='material-icons submenu-caret' aria-hidden='true' style='font-size:18px;'>expand_more</i>";
            echo "</a>";

            if ($hasChildren) {
              echo "<ul class='submenu level-" . (int) ($level + 1) . "' style='list-style:none;margin:0;padding-left:1rem;'>";
              $render_items($item['children'], $level + 1);
              echo "</ul>";
            }

            echo "</li>";
          }
        };

        $render_items($sessionMenus, 0);
        ?>
      </ul>

      <script>
        // simple client-side menu search
        (function () {
          const input = document.getElementById('menu_search');
          if (!input) return;
          input.addEventListener('input', function (e) {
            const q = (e.target.value || '').toLowerCase().trim();
            document.querySelectorAll('.nav-list > li').forEach(function (li) {
              const txt = (li.textContent || '').toLowerCase();
              if (!q) { li.style.display = 'block'; return; }
              li.style.display = txt.indexOf(q) !== -1 ? 'block' : 'none';
            });
          });
        })();
      </script>

    </nav>
  </div>
</section>