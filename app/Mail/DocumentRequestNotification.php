<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Mail\DocumentRequestNotification;
use Illuminate\Support\Facades\Mail;

class DocumentRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DocumentRequest $document)
    {
        $this->document = $document;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->from(env('MAIL_USERNAME'))
        // ->view('template_email')
        // ->with(
        //     [
        //         'nama' => env('MAIL_USERNAME'),
        //         'website' => 'Konsultanku',
        //     ]);
        return $this->markdown('email.document_request');
    }
}
