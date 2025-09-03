<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\DocumentReview;
use App\Models\DocumentReviewComment;

class DocumentCommentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $project;
    public $comment;
    public $commenter;

    public function __construct(DocumentReviewComment $comment)
    {
        $this->comment = $comment;
        $this->document = $comment->documentReview;
        $this->project = $this->document->project;
        $this->commenter = $comment->user;
    }

    public function build()
    {
        return $this->subject('New Comment on Document - ' . $this->document->document_name)
                    ->markdown('emails.document-comment')
                    ->with([
                        'document' => $this->document,
                        'project' => $this->project,
                        'comment' => $this->comment,
                        'commenter' => $this->commenter,
                        'documentUrl' => route('projects.document-review.show', [$this->project->id, $this->document->id])
                    ]);
    }
}
