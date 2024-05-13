<?php

namespace App\Http\Controllers;

use App\Events\DocumentCreatedEvent;
use App\Http\Requests\DocumentsDeleteRequest;
use App\Http\Requests\DocumentStoreRequest;
use App\Http\Requests\DocumentUpdateRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
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
        $documents = Document::orderByDesc('created_at')->paginate(10);

        return DocumentResource::collection($documents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentStoreRequest $request)
    {
        $document = Document::create([
            'description' => $request->validated('description') ?? '',
            'path' => $request->validated('file')->store('private/documents'),
        ]);

        event(new DocumentCreatedEvent($document));

        return response()->json($document, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Document $document)
    {
        if ($request->user()) {
            $url = URL::signedRoute('download', ['document' => $document->id]);
            $document->downloadLink = $url;
        }

        return response()->json($document, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentUpdateRequest $request, Document $document)
    {
        $description = $request->validated('description');
        $document->description = $description;
        $document->save();

        return response()->json($document, Response::HTTP_ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        $document->delete();

        return response()->json(
            ['message' => "Document with id $document->id was deleted."],
            Response::HTTP_OK
        );
    }

    /**
     * Download private file if available for current auth user.
     */
    public function download(Request $request, Document $document)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['error' => 'Document if forbidden for you.']);
        }

        return Storage::download($document->path, "document-$document->id");
    }

    /**
     * Delete multiple documents.
     */
    public function deleteMultiple(DocumentsDeleteRequest $request)
    {
        $ids = $request->validated('ids');
        $idsToDelete = Document::whereIn('id', $ids)->pluck('id');
        Document::destroy($idsToDelete);

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
