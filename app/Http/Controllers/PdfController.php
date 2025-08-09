<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\FrontDoor\InitiatePurge;
use App\Jobs\FrontDoor\TrackPurgeStatus;
use App\Models\CdnPurge;
use App\Models\Pdf;
use App\Services\FrontDoorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    public function index()
    {
        return Pdf::orderByDesc('id')->paginate(50);
    }

    public function show(Pdf $pdf)
    {
        return [
            'id' => $pdf->id,
            'title' => $pdf->title,
            'path' => $pdf->path,
            'cdn_url' => $pdf->cdn_url,
            'storage_url' => $pdf->storage_url,
            'size_bytes' => $pdf->size_bytes,
            'content_type' => $pdf->content_type,
            'etag' => $pdf->etag,
            'hash_sha256' => $pdf->hash_sha256,
            'created_at' => $pdf->created_at,
            'updated_at' => $pdf->updated_at,
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file'  => ['required', 'file', 'mimetypes:application/pdf', 'max:51200'],
        ]);

        $file = $data['file'];
        $filename = Str::uuid() . '.pdf';
        $path = 'documents/' . $filename;

        $disk = Storage::disk('azure-documents');
        $disk->putFileAs('documents', $file, $filename, [
            'visibility' => 'public',
            'mimetype'   => 'application/pdf',
        ]);

        $pdf = Pdf::create([
            'title'        => $data['title'],
            'path'         => $path,
            'size_bytes'   => $file->getSize(),
            'content_type' => 'application/pdf',
            'hash_sha256'  => hash_file('sha256', $file->getRealPath()),
        ]);

        return response()->json($pdf, 201);
    }

    public function update(Request $request, Pdf $pdf)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'file'  => ['sometimes', 'file', 'mimetypes:application/pdf', 'max:51200'],
        ]);

        $pathsToPurge = [];

        if ($request->hasFile('file')) {
            $file = $data['file'];
            $disk = Storage::disk('azure-documents');

            $disk->put($pdf->path, file_get_contents($file->getRealPath()), [
                'visibility' => 'public',
                'mimetype'   => 'application/pdf',
            ]);

            $pdf->size_bytes  = $file->getSize();
            $pdf->hash_sha256 = hash_file('sha256', $file->getRealPath());
            $pathsToPurge[] = '/' . ltrim($pdf->path, '/');
        }

        if (isset($data['title'])) {
            $pdf->title = $data['title'];
        }

        $pdf->save();

        $purge = null;
        if (!empty($pathsToPurge)) {
            $purge = CdnPurge::create([
                'paths' => $pathsToPurge,
                'status' => 'pending',
                'provider' => 'frontdoor',
            ]);
            InitiatePurge::dispatch($purge);
        }

        return response()->json([
            'pdf' => $pdf,
            'purge' => $purge,
        ]);
    }

    public function destroy(Pdf $pdf)
    {
        $path = '/' . ltrim($pdf->path, '/');
        Storage::disk('azure-documents')->delete($pdf->path);
        $pdf->delete();

        $purge = CdnPurge::create([
            'paths' => [$path],
            'status' => 'pending',
            'provider' => 'frontdoor',
        ]);
        InitiatePurge::dispatch($purge);

        return response()->json([
            'message' => 'Deleted',
            'purge' => $purge,
        ]);
    }
}