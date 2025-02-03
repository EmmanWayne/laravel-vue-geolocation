<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reminder;

class RecordatoriosController extends Controller
{
    /**
     * Obtener todos los recordatorios.
     */
    public function index()
    {
        return response()->json(Reminder::all(), 200);
    }

    /**
     * Crear un nuevo recordatorio.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:1',
        ]);

        $reminder = Reminder::create($validated);

        return response()->json($reminder, 201);
    }

    /**
     * Mostrar un recordatorio específico.
     */
    public function show($id)
    {
        $reminder = Reminder::find($id);

        if (!$reminder) {
            return response()->json(['message' => 'Recordatorio no encontrado'], 404);
        }

        return response()->json($reminder, 200);
    }

    /**
     * Actualizar un recordatorio.
     */
    public function update(Request $request, $id)
    {
        $reminder = Reminder::find($id);

        if (!$reminder) {
            return response()->json(['message' => 'Recordatorio no encontrado'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'radius' => 'sometimes|required|integer|min:1',
        ]);

        $reminder->update($validated);

        return response()->json($reminder, 200);
    }

    /**
     * Eliminar un recordatorio.
     */
    public function destroy($id)
    {
        $reminder = Reminder::find($id);

        if (!$reminder) {
            return response()->json(['message' => 'Recordatorio no encontrado'], 404);
        }

        $reminder->delete();

        return response()->json(['message' => 'Recordatorio eliminado'], 200);
    }
}

