<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EvidenceFile;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'incident_id'   => 'required|uuid|exists:incidents,id',
            'alert_id'      => 'nullable|uuid|exists:alerts,id',
            'file'          => 'required|file|max:102400',
            'file_type'     => 'required|in:video,photo,audio',
            'camera_label'  => 'nullable|string',
            'recorded_at'   => 'nullable|date',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $filename = uniqid('evidence_') . '.' . $extension;
        $path = $file->storeAs('evidence', $filename);

        $evidence = EvidenceFile::create([
            'incident_id'      => $request->incident_id,
            'alert_id'         => $request->alert_id,
            'file_path'        => $path,
            'file_type'        => $request->file_type,
            'duration_seconds' => $request->duration_seconds,
            'camera_label'     => $request->camera_label,
            'sync_status'      => 'synced',
            'recorded_at'      => $request->recorded_at ?? now(),
            'uploaded_by'      =>$request->user()->id,
            'original_name'    =>$file->getOriginalName(),
            'mime_type'        =>$file->getMimeType(),
            'size_bytes'        =>$file->getSize(),
 

            
        ]);

        AuditLog::register('evidence_uploaded', 'evidence_files', $evidence->id, [
            'file_type'   => $request->file_type,
            'incident_id' => $request->incident_id,
        ]);

        return response()->json($evidence, 201);
    }

    public function index(Request $request)
    {
        $request->validate([
            'incident_id' => 'required|uuid|exists:incidents,id',
        ]);

        $evidence = EvidenceFile::where('incident_id', $request->incident_id)
                                ->orderByDesc('recorded_at')
                                ->get();

        return response()->json($evidence);
    }

    public function download(string $id)
    {
        $evidence = EvidenceFile::findOrFail($id);

        if (!Storage::exists($evidence->file_path)) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        return Storage::download($evidence->file_path);
    }
}