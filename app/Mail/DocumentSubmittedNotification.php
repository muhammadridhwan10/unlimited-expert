<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\DocumentReview;

class DocumentSubmittedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $project;
    public $submitter;

    public function __construct(DocumentReview $document)
    {
        $this->document = $document;
        $this->project = $document->project;
        $this->submitter = $document->submitter;
    }

    public function build()
    {
        return $this->subject('New Document Submitted for Review - ' . $this->project->project_name)
                    ->markdown('emails.document-submitted')
                    ->with([
                        'document' => $this->document,
                        'project' => $this->project,
                        'submitter' => $this->submitter,
                        'reviewUrl' => route('projects.document-review.show', [$this->project->id, $this->document->id])
                    ]);
    }
}
