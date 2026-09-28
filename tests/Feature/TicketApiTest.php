<?php

namespace Tests\Feature;

use App\Repositories\JsonTicketRepository;
use App\Repositories\TicketRepositoryInterface;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    private string $rutaTemporal;

    protected function setUp(): void
    {
        parent::setUp();

        // Trabajamos sobre una copia del JSON mock para no ensuciar el seed.
        $this->rutaTemporal = storage_path('framework/testing/tickets-test.json');
        @mkdir(dirname($this->rutaTemporal), 0777, true);
        copy(database_path('data/tickets.json'), $this->rutaTemporal);

        $this->app->bind(TicketRepositoryInterface::class, fn () => new JsonTicketRepository($this->rutaTemporal));
    }

    protected function tearDown(): void
    {
        @unlink($this->rutaTemporal);

        parent::tearDown();
    }

    public function test_lista_los_tickets_con_la_estructura_esperada(): void
    {
        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'folio', 'titulo', 'descripcion', 'sucursal_id', 'solicitante', 'severidad', 'estatus', 'creado', 'actualizado'],
                ],
            ]);
    }

    public function test_filtra_por_estatus_y_severidad(): void
    {
        $this->getJson('/api/tickets?estatus=resuelto')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/tickets?severidad=alta')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/tickets?estatus=abierto&severidad=alta')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_crea_un_ticket_y_responde_201(): void
    {
        $respuesta = $this->postJson('/api/tickets', [
            'titulo' => 'Sin papel en la impresora',
            'descripcion' => 'La caja 1 reporta que no hay rollo de papel desde temprano.',
            'sucursal_id' => 12,
            'solicitante' => 'Laura Mendez',
            'severidad' => 'media',
        ])->assertCreated()->assertJsonPath('data.estatus', 'abierto');

        $this->assertSame('TK-000007', $respuesta->json('data.folio'));

        $this->getJson('/api/tickets')->assertJsonCount(7, 'data');
    }

    public function test_valida_los_campos_obligatorios(): void
    {
        $this->postJson('/api/tickets', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['titulo', 'descripcion', 'sucursal_id', 'solicitante', 'severidad']);
    }

    public function test_rechaza_una_severidad_no_permitida(): void
    {
        $this->postJson('/api/tickets', [
            'titulo' => 'Falla rara',
            'descripcion' => 'Descripcion suficientemente larga para pasar la validacion.',
            'sucursal_id' => 3,
            'solicitante' => 'Jorge Nava',
            'severidad' => 'urgentisima',
        ])->assertStatus(422)->assertJsonValidationErrors(['severidad']);
    }

    public function test_actualiza_el_estatus(): void
    {
        $this->patchJson('/api/tickets/1/estatus', ['estatus' => 'en_proceso'])
            ->assertOk()
            ->assertJsonPath('data.estatus', 'en_proceso');
    }

    public function test_rechaza_un_estatus_no_permitido(): void
    {
        $this->patchJson('/api/tickets/1/estatus', ['estatus' => 'cerrado'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['estatus']);
    }

    public function test_devuelve_404_cuando_el_ticket_no_existe(): void
    {
        $this->getJson('/api/tickets/999')->assertNotFound();
        $this->patchJson('/api/tickets/999/estatus', ['estatus' => 'resuelto'])->assertNotFound();
    }
}
