<?php
// CSRF helper procedural functions
// Simple implementation: token stored in session with 1h expiry
if (session_status() !== PHP_SESSION_ACTIVE) {
  @session_start();
}

function csrf_get_token()
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
  }
  $ttl = 3600; // 1 hour
  if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > $ttl) {
    try {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (\Exception $e) {
      $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
    $_SESSION['csrf_token_time'] = time();
  }
  return $_SESSION['csrf_token'];
}

function csrf_validate_token($token)
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
  }
  if (empty($token) || empty($_SESSION['csrf_token']))
    return false;
  $ttl = 3600;
  if ((time() - ($_SESSION['csrf_token_time'] ?? 0)) > $ttl)
    return false;
  return hash_equals($_SESSION['csrf_token'], $token);
}
