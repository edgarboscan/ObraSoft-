<?php
namespace App\Controllers;

class HelloController
{
  public static function hello()
  {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['message' => 'Hola desde HelloController']);
  }
}
