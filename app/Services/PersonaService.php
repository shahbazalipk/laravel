<?php

namespace App\Services;

use App\Models\Persona;
use App\Traits\HasAuditLogging;

class PersonaService
{
    use HasAuditLogging;

    public function getAllPersonas()
    {
        return Persona::orderBy('sort_order')->orderBy('name')->get();
    }

    public function getActivePersonas()
    {
        return Persona::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function createPersona(array $data): Persona
    {
        $persona = Persona::create($data);
        $this->logCreated($persona, $data);
        return $persona;
    }

    public function updatePersona(Persona $persona, array $data): Persona
    {
        $oldData = $persona->toArray();
        $persona->update($data);
        $this->logUpdated($persona, $oldData, $persona->fresh()->toArray());
        return $persona->fresh();
    }

    public function deletePersona(Persona $persona): bool
    {
        $this->logDeleted($persona);
        return $persona->delete();
    }

    public function toggleActive(Persona $persona): Persona
    {
        $persona->is_active = !$persona->is_active;
        $persona->save();
        $this->logToggled($persona, $persona->is_active);
        return $persona;
    }
}
