<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketEstatusRequest;
use App\Http\Resources\TicketResource;
use App\Repositories\TicketRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * API REST de tickets de soporte.
 *
 * El controlador depende de la INTERFAZ del repositorio, no de una
 * implementacion concreta: por eso hoy puede trabajar con datos mock (JSON)
 * y en produccion con Eloquent/MySQL sin cambiar este archivo.
 */
class TicketController extends Controller
{
    public function __construct(private readonly TicketRepositoryInterface $tickets)
    {
    }

    /**
     * GET /api/tickets?estatus=abierto&severidad=alta
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filtros = $request->only(['estatus', 'severidad']);

        return TicketResource::collection($this->tickets->all($filtros));
    }

    /**
     * GET /api/tickets/{id}
     */
    public function show(int $id): TicketResource
    {
        $ticket = $this->tickets->find($id);

        abort_if($ticket === null, 404, "No existe el ticket {$id}.");

        return new TicketResource($ticket);
    }

    /**
     * POST /api/tickets
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $this->tickets->create($request->validated());

        return (new TicketResource($ticket))->response()->setStatusCode(201);
    }

    /**
     * PATCH /api/tickets/{id}/estatus
     */
    public function updateEstatus(UpdateTicketEstatusRequest $request, int $id): TicketResource
    {
        $ticket = $this->tickets->updateEstatus($id, $request->validated()['estatus']);

        abort_if($ticket === null, 404, "No existe el ticket {$id}.");

        return new TicketResource($ticket);
    }
}
