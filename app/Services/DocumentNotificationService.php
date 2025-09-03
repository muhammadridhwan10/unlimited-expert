<?php

// app/Services/DocumentNotificationService.php
namespace App\Services;

use App\Models\DocumentReview;
use App\Models\DocumentReviewComment;
use App\Mail\DocumentSubmittedNotification;
use App\Mail\DocumentStatusUpdatedNotification;
use App\Mail\DocumentCommentNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DocumentNotificationService
{
    /**
     * Send notification when document is submitted
     */
    public function notifyDocumentSubmitted(DocumentReview $document)
    {
        try {
            // Notify approver
            $approverEmail = $document->approver->email;
            Mail::to($approverEmail)->send(new DocumentSubmittedNotification($document));
            
            Log::info("Document submitted notification sent to approver: {$approverEmail}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send document submitted notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification when document status is updated (approved/rejected/revision)
     */
    public function notifyDocumentStatusUpdated(DocumentReview $document, $statusAction, $comment = null, $reviewer = null)
    {
        try {
            $recipients = $this->getDocumentRecipients($document, 'status_update');
            
            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->send(
                    new DocumentStatusUpdatedNotification($document, $statusAction, $comment, $reviewer)
                );
            }
            
            Log::info("Document status updated notification sent for document ID: {$document->id}, action: {$statusAction}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send document status updated notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification when new comment is added
     */
    public function notifyDocumentComment(DocumentReviewComment $comment)
    {
        try {
            $recipients = $this->getDocumentRecipients($comment->documentReview, 'comment');
            
            // Don't send notification to the commenter
            $recipients = $recipients->filter(function ($user) use ($comment) {
                return $user->id !== $comment->user_id;
            });

            foreach ($recipients as $recipient) {
                Mail::to($recipient->email)->send(new DocumentCommentNotification($comment));
            }
            
            Log::info("Document comment notification sent for document ID: {$comment->document_review_id}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send document comment notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all relevant recipients for document notifications
     */
    private function getDocumentRecipients(DocumentReview $document, $notificationType = 'all')
    {
        $recipients = collect();

        switch ($notificationType) {
            case 'status_update':
                // Send to submitter and contributors
                $recipients->push($document->submitter);
                
                // Add contributors
                foreach ($document->contributors as $contributor) {
                    $recipients->push($contributor->user);
                }
                break;

            case 'comment':
                // Send to submitter, approver, and contributors
                $recipients->push($document->submitter);
                $recipients->push($document->approver);
                
                // Add contributors
                foreach ($document->contributors as $contributor) {
                    $recipients->push($contributor->user);
                }
                break;

            case 'all':
            default:
                // Send to everyone involved
                $recipients->push($document->submitter);
                $recipients->push($document->approver);
                
                // Add contributors
                foreach ($document->contributors as $contributor) {
                    $recipients->push($contributor->user);
                }
                break;
        }

        // Remove duplicates and inactive users
        return $recipients->unique('id')->filter(function ($user) {
            return $user && $user->is_active && $user->email;
        });
    }

    /**
     * Send bulk notification for multiple documents
     */
    public function notifyBulkDocuments($documents, $action, $data = [])
    {
        try {
            foreach ($documents as $document) {
                switch ($action) {
                    case 'submitted':
                        $this->notifyDocumentSubmitted($document);
                        break;
                    case 'status_updated':
                        $this->notifyDocumentStatusUpdated(
                            $document, 
                            $data['status'] ?? 'updated', 
                            $data['comment'] ?? null,
                            $data['reviewer'] ?? null
                        );
                        break;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send bulk notifications: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user should receive notifications (preferences)
     */
    private function shouldReceiveNotification($user, $type)
    {
        // You can extend this to check user preferences
        // For now, all active users receive notifications
        return $user && $user->is_active;
    }

    /**
     * Get notification summary for dashboard
     */
    public function getNotificationSummary($userId)
    {
        try {
            // Documents pending approval by this user
            $pendingApprovals = DocumentReview::where('approver_id', $userId)
                ->whereIn('status', ['submitted', 'under_review'])
                ->count();

            // Documents submitted by this user awaiting feedback
            $awaitingFeedback = DocumentReview::where('submitted_by', $userId)
                ->whereIn('status', ['submitted', 'under_review'])
                ->count();

            // Documents requiring revision by this user
            $needsRevision = DocumentReview::where('submitted_by', $userId)
                ->where('status', 'revision_required')
                ->count();

            return [
                'pending_approvals' => $pendingApprovals,
                'awaiting_feedback' => $awaitingFeedback,
                'needs_revision' => $needsRevision,
                'total_notifications' => $pendingApprovals + $awaitingFeedback + $needsRevision
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get notification summary: " . $e->getMessage());
            return [
                'pending_approvals' => 0,
                'awaiting_feedback' => 0,
                'needs_revision' => 0,
                'total_notifications' => 0
            ];
        }
    }
}