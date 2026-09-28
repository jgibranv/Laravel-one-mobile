<?php

namespace App\Repositories;

use Illuminate\Support\Arr;
use RuntimeException;

/**
 * Implementacion de TicketRepositoryInterface con datos mock en un archivo JSON.
 *
 * No usa base de datos: el archivo database/data/tickets.json hace de "tabla".
 * Se apoya en flock() para que dos peticiones simultaneas no corrompan el archivo.
 */
class JsonTicketRepository implements TicketRepositoryInterface
{
    public function __construct(private readonly string $ruta)
    {
        if (! is_file($this->ruta)) {
            throw new RuntimeException("No existe el archivo de tickets: {$this->ruta}");
        }
    }

    public function all(array $filtros = []): array
    {
        $tickets = $this->leer()['tickets'];

        if (($estatus = Arr::get($filtros, 'estatus')) !== null && $estatus !== '') {
            $tickets = array_values(array_filter(
                $tickets,
                fn (array $ticket): bool => $ticket['estatus'] === $estatus
            ));
        }

        if (($severidad = Arr::get($filtros, 'severidad')) !== null && $severidad !== '') {
            $tickets = array_values(array_filter(
                $tickets,
                fn (array $ticket): bool => $ticket['severidad'] === $severidad
            ));
        }

        // Mas recientes primero.
        usort($tickets, fn (array $a, array $b): int => strcmp($b['created_at'], $a['created_at']));

        return $tickets;
    }

    public function find(int $id): ?array
    {
        foreach ($this->leer()['tickets'] as $ticket) {
            if ((int) $ticket['id'] === $id) {
                return $ticket;
            }
        }

        return null;
    }

    public function create(array $datos): array
    {
        $archivo = $this->leer();
        $consecutivo = ((int) $archivo['ultimo_folio']) + 1;
        $ahora = now()->format('Y-m-d H:i:s');

        $ticket = [
            'id' => $consecutivo,
            'folio' => 'TK-'.str_pad((string) $consecutivo, 6, '0', STR_PAD_LEFT),
            'titulo' => $datos['titulo'],
            'descripcion' => $datos['descripcion'],
            'sucursal_id' => (int) $datos['sucursal_id'],
            'solicitante' => $datos['solicitante'],
            'severidad' => $datos['severidad'],
            'estatus' => 'abierto',
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ];

        $archivo['tickets'][] = $ticket;
        $archivo['ultimo_folio'] = $consecutivo;

        $this->escribir($archivo);

        return $ticket;
    }

    public function updateEstatus(int $id, string $estatus): ?array
    {
        $archivo = $this->leer();
        $actualizado = null;

        foreach ($archivo['tickets'] as $indice => $ticket) {
            if ((int) $ticket['id'] === $id) {
                $archivo['tickets'][$indice]['estatus'] = $estatus;
                $archivo['tickets'][$indice]['updated_at'] = now()->format('Y-m-d H:i:s');
                $actualizado = $archivo['tickets'][$indice];
                break;
            }
        }

        if ($actualizado === null) {
            return null;
        }

        $this->escribir($archivo);

        return $actualizado;
    }

    /**
     * @return array{ultimo_folio: int, tickets: array<int, array<string, mixed>>}
     */
    private function leer(): array
    {
        $handle = fopen($this->ruta, 'r');

        if ($handle === false) {
            throw new RuntimeException("No se pudo leer el archivo de tickets: {$this->ruta}");
        }

        flock($handle, LOCK_SH);
        $contenido = stream_get_contents($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        $datos = json_decode((string) $contenido, true);

        if (! is_array($datos) || ! isset($datos['tickets'])) {
            throw new RuntimeException('El archivo de tickets no contiene un JSON valido.');
        }

        return $datos;
    }

    /**
     * @param  array<string, mixed>  $archivo
     */
    private function escribir(array $archivo): void
    {
        $handle = fopen($this->ruta, 'c');

        if ($handle === false) {
            throw new RuntimeException("No se pudo escribir el archivo de tickets: {$this->ruta}");
        }

        flock($handle, LOCK_EX);
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($archivo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
