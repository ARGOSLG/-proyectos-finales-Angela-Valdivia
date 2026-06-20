<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Incident;
use App\Models\Protocol;
use App\Models\ProtocolExecution;
use Illuminate\Http\Request;

class ProtocolController extends Controller
{
    /**
     * Listar protocolos de la empresa
     * GET /api/protocols
     */
    public function index(Request $request)
    {
        $protocols = Protocol::where('company_id', $request->user()->company_id)
                             ->where('active', true)
                             ->get();

        return response()->json($protocols);
    }

    /**
     * Crear protocolo
     * POST /api/protocols
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string',
            'trigger_type' => 'required|string',
            'steps'        => 'required|array',
        ]);

        $protocol = Protocol::create([
            'company_id'   => $request->user()->company_id,
            'name'         => $request->name,
            'trigger_type' => $request->trigger_type,
            'steps'        => $request->steps,
            'active'       => true,
        ]);

        AuditLog::register('created', 'protocols', $protocol->id);

        return response()->json($protocol, 201);
    }

    /**
     * Ver protocolo
     * GET /api/protocols/{id}
     */
    public function show(Request $request, string $id)
    {
        $protocol = Protocol::where('company_id', $request->user()->company_id)
                            ->findOrFail($id);

        return response()->json($protocol);
    }

    /**
     * Ejecutar protocolo en un incidente
     * POST /api/protocols/{id}/execute
     */
    public function execute(Request $request, string $id)
    {
        $request->validate([
            'incident_id' => 'required|uuid|exists:incidents,id',
        ]);

        $protocol = Protocol::where('company_id', $request->user()->company_id)
                            ->findOrFail($id);

        $incident = Incident::findOrFail($request->incident_id);

        // Crear ejecución del protocolo
        $execution = ProtocolExecution::create([
            'protocol_id' => $protocol->id,
            'incident_id' => $incident->id,
            'operator_id' => $request->user()->id,
            'steps_state' => collect($protocol->steps)->map(fn($step) => [
                'step'      => $step,
                'completed' => false,
                'completed_at' => null,
            ])->toArray(),
            'status'          => 'in_progress',
            'motor_cut_sent'  => false,
        ]);

        // Actualizar estado del incidente
        $incident->update(['status' => 'in_progress']);

        AuditLog::register('protocol_executed', 'protocol_executions', $execution->id, [
            'protocol_id' => $protocol->id,
            'incident_id' => $incident->id,
        ]);

        return response()->json($execution, 201);
    }

    /**
     * Actualizar paso del protocolo
     * PATCH /api/protocols/executions/{id}/step
     */
    public function updateStep(Request $request, string $id)
    {
        $request->validate([
            'step_index' => 'required|integer',
            'completed'  => 'required|boolean',
        ]);

        $execution = ProtocolExecution::where('operator_id', $request->user()->id)
                                      ->findOrFail($id);

        $steps = $execution->steps_state;
        $steps[$request->step_index]['completed']    = $request->completed;
        $steps[$request->step_index]['completed_at'] = now();

        // Verificar si todos los pasos están completos
        $allCompleted = collect($steps)->every(fn($s) => $s['completed']);

        $execution->update([
            'steps_state' => $steps,
            'status'      => $allCompleted ? 'completed' : 'in_progress',
            'completed_at' => $allCompleted ? now() : null,
        ]);

        return response()->json($execution);
    }

    /**
     * Corte de motor remoto
     * POST /api/protocols/executions/{id}/motor-cut
     */
    public function motorCut(Request $request, string $id)
    {
        $execution = ProtocolExecution::findOrFail($id);

        $execution->update(['motor_deceleration' => true]);

        AuditLog::register('motor_deceleration', 'protocol_executions', $execution->id, [
            'operator_id' => $request->user()->id,
        ]);

        // Aquí se enviaría la señal al módulo GPS del vehículo
        // Por ahora solo registramos la acción

        return response()->json([
            'message' => 'motor_deceleration',
        ]);
    }
}