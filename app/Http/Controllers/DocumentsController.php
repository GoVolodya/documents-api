<?php

namespace App\Http\Controllers;

use App\Events\DocumentCreatedEvent;
use App\Http\Requests\DocumentsDeleteRequest;
use App\Http\Requests\DocumentStoreRequest;
use App\Http\Requests\DocumentUpdateRequest;
use App\Models\Document;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = Document::orderByDesc('created_at')->get();
        return response()->json($documents, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentStoreRequest $request)
    {
        $user = $request->user();
        $document = Document::create([
            'description' => $request->validated('description') ?? '',
            'path' => $request->validated('file')->store('private/documents'),
            'user_id' => $user->id,
        ]);
        event(new DocumentCreatedEvent($document));

        return response()->json($document, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        try {
            $document = Document::findOrFail($id);

            if ($request->user()) {
                $url = URL::signedRoute('download', ['id' => $id]);
                $document->downloadLink = $url;
            }

            return response()->json($document, Response::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json(['error' => "Document with id $id not found."], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentUpdateRequest $request, string $id)
    {
        try {
            $document = Document::findOrFail($id);
            $description = $request->validated('description');
            $document->description = $description;
            $document->save();
            return response()->json($document, Response::HTTP_ACCEPTED);
        } catch (Exception $exception) {
            return response()->json(['error' => "Document with id $id not found."], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $document = Document::findOrFail($id);
            $document->delete();
            return response()->json(['message' => "Document with id $id was deleted."], Response::HTTP_ACCEPTED);
        } catch (Exception $exception) {
            return response()->json(['error' => "Document with id $id not found."], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Download private file if available for current auth user.
     */
    public function download(Request $request, string $id)
    {
        try {
            $document = Document::findOrFail($id);

            if (!$request->hasValidSignature()) {
                return response()->json(['error' => 'Document if forbidden for you.']);
            }

            return Storage::download($document->path, "document-$id");
        } catch (Exception $exception) {
            return response()->json(['error' => "Document with id $id not found."], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Delete multiple documents.
     */
    public function deleteMultiple(DocumentsDeleteRequest $request)
    {
        $ids = $request->validated('ids');
        Document::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Documents were deleted'], Response::HTTP_OK);
    }

    /**
     * Show the users documents token and creates new if not exists.
     */
    public function showToken()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'You must login to see the token.']);
        }

        if ($user->currentAccessToken()) {
            return response()->json(['token' => $user->currentAccessToken()->plainTextToken]);
        }

        $token = $user->createToken('documents_token')->plainTextToken;

        return response()->json(['token' => $token]);
    }
}
