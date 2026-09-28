<?php

namespace App\Repositories;

/**
 * Contrato de acceso a tickets de soporte.
 *
 * Hoy lo implementa JsonTicketRepository (datos mock en un archivo JSON).
 * En produccion lo implementaria un EloquentTicketRepository (MySQL)
 * sin necesidad de modificar el controlador ni los FormRequest.
 */
interface TicketRepositoryInterface
{
    /**
     * Devuelve los tickets, opcionalmente filtrados por estatus y/o severidad.
     *
     * @param  array{estatus?: string, severidad?: string}  $filtros
     * @return array<int, array<string, mixed>>
     */
    public function all(array $filtros = []): array;

    /**
     * Devuelve un ticket por su id, o null si no existe.
     *
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array;

    /**
     * Crea un ticket y devuelve el registro resultante.
     *
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function create(array $datos): array;

    /**
     * Actualiza el estatus de un ticket y devuelve el registro, o null si no existe.
     *
     * @return array<string, mixed>|null
     */
    public function updateEstatus(int $id, string $estatus): ?array;
}
