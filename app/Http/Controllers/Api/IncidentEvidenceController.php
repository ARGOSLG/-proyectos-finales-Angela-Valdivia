<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IncidentEvidenceController extends Controller
{
    // GET /api/incidents/{incident}/evidences
    public function index(Incident $incident)
    {
        $evidences = $incident->evidences()->with('uploadedBy')->get();

        return response()->json([
            'success' => true,
            'data'    => $evidences->map(function ($evidence) {
                $evidence->url = $evidence->url; // fuerza el accessor
                return $evidence;
            }),
        ]);
    }

    // POST /api/incidents/{incident}/evidences
    public function store(Request $request, Incident $incident)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4,mov,avi|max:51200', // 50MB máximo
        ]);

        $file = $request->file('file');

        // Determinar tipo según el mime
        $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'photo';

        // Generar nombre único y guardar en storage/app/public/incidents/{incident_id}/
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            "incidents/{$incident->id}",
            $fileName,
            'public'
        );

        $evidence = IncidentEvidence::create([
            'incident_id'    => $incident->id,
            'uploaded_by'    => $request->user()->id,
            'type'           => $type,
            'file_path'      => $path,
            'original_name'  => $file->getClientOriginalName(),
            'mime_type'      => $file->getMimeType(),
            'size_bytes'     => $file->getSize(),
        ]);

        $evidence->url = $evidence->url;

        return response()->json([
            'success' => true,
            'data'    => $evidence,
        ], 201);
    }

    // DELETE /api/incidents/{incident}/evidences/{evidence}
    public function destroy(Incident $incident, IncidentEvidence $evidence)
    {
        // Borrar archivo físico
        \Storage::disk('public')->delete($evidence->file_path);

        $evidence->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evidencia eliminada correctamente',
        ]);
    }
}