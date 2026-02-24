<?php
session_start();

function is_logged_in()
{
    return !empty($_SESSION['usuario']);
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function is_admin()
{
    if (empty($_SESSION['usuario']))
        return false;
    $u = $_SESSION['usuario'];
    return !empty($u['is_super']) && ($u['is_super'] === 1 || $u['is_super'] === '1' || $u['is_super'] === true);
}

function require_admin()
{
    if (!is_logged_in() || !is_admin()) {
        $_SESSION['error'] = "Acceso denegado. Solo administradores.";
        header('Location: dashboard.php');
        exit();
    }
}

function getCurrentUser()
{
    if (!is_logged_in())
        return null;
    return $_SESSION['usuario'];
}


/**
 * Busca recursivamente un menú por su URL en la estructura de menús del usuario.
 * Intenta comparar por coincidencia exacta y por basename (sin parámetros).
 */
function find_menu_by_url(array $menus, string $url)
{
    $normalize = function ($u) {
        $u = trim((string) $u);
        $u = preg_replace('#\?.*$#', '', $u); // quitar query
        $u = ltrim($u, './');
        return $u;
    };
    $target = $normalize($url);
    foreach ($menus as $m) {
        $mu = $m['url'] ?? '';
        if ($normalize($mu) === $target)
            return $m;
        // compare basename
        $base = basename(parse_url($mu, PHP_URL_PATH) ?: '');
        if ($base && $base === basename($target))
            return $m;
        // children
        if (!empty($m['children']) && is_array($m['children'])) {
            $found = find_menu_by_url($m['children'], $url);
            if ($found)
                return $found;
        }
    }
    return null;
}


/**
 * Devuelve un arreglo con información sobre los permisos del usuario para una URL dada.
 * Retorna: ['allowed' => bool, 'is_super' => bool, 'menu' => menu|null, 'perms' => array]
 */
function get_user_permissions(string $pageUrl)
{
    $user = $_SESSION['usuario'] ?? null;
    if (!$user)
        return ['allowed' => false, 'is_super' => false, 'menu' => null, 'perms' => []];
    $isSuper = !empty($user['is_super']) && ($user['is_super'] === 1 || $user['is_super'] === '1' || $user['is_super'] === true);
    if ($isSuper) {
        return ['allowed' => true, 'is_super' => true, 'menu' => null, 'perms' => ['view', 'create', 'edit', 'delete', 'print']];
    }

    $menus = $user['menus'] ?? $user['menu'] ?? [];
    if (!is_array($menus))
        $menus = [];
    $menu = find_menu_by_url($menus, $pageUrl);
    if (!$menu) {
        return ['allowed' => false, 'is_super' => false, 'menu' => null, 'perms' => []];
    }
    // try to infer permissions from menu entry if present
    $perms = [];
    if (!empty($menu['permisos'])) {
        if (is_array($menu['permisos']))
            $perms = $menu['permisos'];
        else
            $perms = preg_split('/[,_\|; ]+/', (string) $menu['permisos'], -1, PREG_SPLIT_NO_EMPTY);
    }
    // always allow view if menu exists
    if (!in_array('view', $perms))
        $perms[] = 'view';
    return ['allowed' => true, 'is_super' => false, 'menu' => $menu, 'perms' => $perms];
}


/**
 * Comprueba si el usuario tiene un permiso concreto para la página.
 * $perm: 'view','create','edit','delete','print' (por defecto 'view')
 */
function check_user_permission(string $pageUrl, string $perm = 'view')
{
    $info = get_user_permissions($pageUrl);
    if ($info['is_super'])
        return true;
    $perm = strtolower($perm);
    return in_array($perm, array_map('strtolower', $info['perms'] ?? []), true);
}


/**
 * Requiere permiso y redirige a dashboard si no tiene acceso.
 */
function require_permission(string $pageUrl, string $perm = 'view')
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
    if (!check_user_permission($pageUrl, $perm)) {
        // opcional: setear mensaje y redirigir
        $_SESSION['error'] = 'Acceso denegado';
        header('Location: dashboard.php');
        exit();
    }
}

function logout()
{
    session_unset();
    session_destroy();
    header('Location: index.html');
    exit();
}


?>