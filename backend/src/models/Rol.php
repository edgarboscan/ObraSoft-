<?php
namespace App\Models;

class Rol
{
  public int $id_rol;
  public string $nombre_rol;
  public ?string $descripcion;
  public int $nivel_prioridad;

  public function __construct(array $data)
  {
    $this->id_rol = isset($data['id_rol']) ? (int) $data['id_rol'] : 0;
    $this->nombre_rol = $data['nombre_rol'] ?? '';
    $this->descripcion = $data['descripcion'] ?? null;
    $this->nivel_prioridad = isset($data['nivel_prioridad']) ? (int) $data['nivel_prioridad'] : 1;
  }
}
