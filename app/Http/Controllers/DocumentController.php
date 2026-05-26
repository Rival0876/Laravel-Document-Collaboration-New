<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    // Menampilkan daftar dokumen di dashboard
    public function index()
    {
        $documents = Document::latest()->get();
        return view('dashboard', compact('documents'));
    }

    // Membuat dokumen baru
    public function store(Request $request)
    {
        $document = Document::create([
            'title' => $request->title ?? 'Untitled Document',
            'content' => ''
        ]);

        return redirect()->route('documents.show', $document->id);
    }

    // Membuka halaman editor dokumen
    public function show(Document $document)
    {
        $versions = $document->versions()->with('user')->latest()->get();
        return view('editor', compact('document', 'versions'));
    }

    // Fitur 3: Menyimpan versi dokumen (Version History)
    public function saveVersion(Request $request, Document $document)
    {
        $request->validate([
            'content' => 'required',
            'summary' => 'nullable|string|max:255'
        ]);

        // Simpan snapshot ke tabel history
        DocumentVersion::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'content_snapshot' => $request->content,
            'change_summary' => $request->summary ?? 'Manual Save',
        ]);

        // Update juga konten utama dokumen
        $document->update(['content' => $request->content]);

        return response()->json(['success' => true, 'message' => 'Versi berhasil disimpan!']);
    }
}