<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'folio' => $this->resource['folio'],
            'titulo' => $this->resource['titulo'],
            'descripcion' => $this->resource['descripcion'],
            'sucursal_id' => $this->resource['sucursal_id'],
            'solicitante' => $this->resource['solicitante'],
            'severidad' => $this->resource['severidad'],
            'estatus' => $this->resource['estatus'],
            'creado' => $this->resource['created_at'],
            'actualizado' => $this->resource['updated_at'],
        ];
    }
}
