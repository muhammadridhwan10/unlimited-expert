<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\DocumentReview;

class DocumentStatusUpdatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $project;
    public $reviewer;
    public $statusAction;
    public $comment;

    public function __construct(DocumentReview $document, $statusAction, $comment = null, $reviewer = null)
    {
        $this->document = $document;
        $this->project = $document->project;
        $this->reviewer = $reviewer ?? auth()->user();
        $this->statusAction = $statusAction;
        $this->comment = $comment;
    }

    public function build()
    {
        $subject = 'Document ' . ucfirst($this->statusAction) . ' - ' . $this->document->document_name;
        
        return $this->subject($subject)
                    ->markdown('emails.document-status-updated')
                    ->with([
                        'document' => $this->document,
                        'project' => $this->project,
                        'reviewer' => $this->reviewer,
                        'statusAction' => $this->statusAction,
                        'comment' => $this->comment,
                        'documentUrl' => route('projects.document-review.show', [$this->project->id, $this->document->id])
                    ]);
    }
}
