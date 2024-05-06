<?php

namespace App\Listeners;

use App\Events\DocumentCreatedEvent;
use App\Mail\DocumentCreatedEmail;
use Illuminate\Support\Facades\Mail;

class DocumentCreated
{
    /**
     * Handle the event.
     */
    public function handle(DocumentCreatedEvent $event): void
    {
        Mail::to(config('mail.from.address'))->send(new DocumentCreatedEmail($event->document));
    }
}
