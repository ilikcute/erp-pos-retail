<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Models\System\DocumentSequence;
use Illuminate\Http\Request;

class DocumentSequenceController extends Controller
{
    public function index()
    {
        $sequences = DocumentSequence::all();

        return response()->json([
            'success' => true,
            'data' => $sequences,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_code' => 'required|string|unique:document_sequences,document_code',
            'document_name' => 'required|string',
            'prefix' => 'required|string',
            'reset_period' => 'required|in:NONE,DAILY,MONTHLY,YEARLY',
        ]);

        $sequence = DocumentSequence::create($validated);

        return response()->json([
            'success' => true,
            'data' => $sequence,
            'message' => 'Document sequence berhasil dibuat',
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $sequence = DocumentSequence::findOrFail($id);

        $validated = $request->validate([
            'document_name' => 'string',
            'prefix' => 'string',
            'reset_period' => 'in:NONE,DAILY,MONTHLY,YEARLY',
        ]);

        $sequence->update($validated);

        return response()->json([
            'success' => true,
            'data' => $sequence->fresh(),
            'message' => 'Document sequence berhasil diupdate',
        ]);
    }
}
