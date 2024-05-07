<?php

namespace App\Observers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentObserver
{
    public function deleting(Document $document)
    {
        Storage::delete($document->path);
    }
}
