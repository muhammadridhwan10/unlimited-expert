<?php

namespace App\Http\Controllers;

use App\Models\DocumentReview;
use App\Models\DocumentReviewCategory;
use App\Models\DocumentContributor;
use App\Models\DocumentReviewComment;
use App\Models\Project;
use App\Models\User;
use App\Services\DocumentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Utility;
use Illuminate\Support\Str;

class DocumentReviewController extends Controller
{

    protected $notificationService;

    public function __construct(DocumentNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function create($projectId)
    {
        if(!Auth::user()->can('create project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $project = Project::findOrFail($projectId);
        
        // Get all users that can be approvers (partners, managers, etc)
        $approvers = User::where('type', '!=', 'client')
            ->where('is_active', 1)
            ->get()
            ->pluck('name', 'id');

        // Get all users that can be contributors
        $contributors = User::where('type', '!=', 'client')
            ->where('type', '!=', 'staff_client')
            ->where('is_active', 1)
            ->get()
            ->pluck('name', 'id');

        // Get available categories for this project
        $categories = DocumentReviewCategory::getAvailableForProject($projectId);

        return view('projects.document-review.create', compact('project', 'approvers', 'contributors', 'categories'));
    }

    public function store(Request $request, $projectId)
    {
        if(!Auth::user()->can('create project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        // Custom validation rules
        $rules = [
            'document_name' => 'required|string|max:255',
            'document_link' => 'required|url',
            'submission_date' => 'required|date',
            'approver_id' => 'required|exists:users,id',
            'contributors' => 'required|array|min:1',
            'contributors.*' => 'exists:users,id',
            'description' => 'nullable|string'
        ];

        // Conditional validation for category
        if ($request->category_id === 'custom') {
            $rules['custom_category_name'] = 'required|string|max:100';
            $rules['custom_category_color'] = 'nullable|string|max:7';
            $rules['custom_category_icon'] = 'nullable|string|max:50';
        } else {
            $rules['category_id'] = 'required|exists:document_review_categories,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $project = Project::findOrFail($projectId);

        // Handle category
        $categoryId = $request->category_id;
        
        if ($request->category_id === 'custom' && $request->custom_category_name) {
            // Create custom category
            $customCategory = DocumentReviewCategory::createCustomCategory(
                $request->custom_category_name,
                $projectId,
                Auth::id(),
                [
                    'description' => "Custom category: {$request->custom_category_name}",
                    'color' => $request->custom_category_color ?? '#6c757d',
                    'icon' => $request->custom_category_icon ?? 'ti-tag'
                ]
            );
            $categoryId = $customCategory->id;
        }

        // Create document review
        $documentReview = DocumentReview::create([
            'project_id' => $projectId,
            'document_name' => $request->document_name,
            'document_link' => $request->document_link,
            'category_id' => $categoryId,
            'description' => $request->description,
            'submission_date' => $request->submission_date,
            'submitted_by' => Auth::id(),
            'approver_id' => $request->approver_id,
            'status' => 'submitted',
            'created_by' => Auth::id()
        ]);

        // Add contributors
        foreach($request->contributors as $contributorId) {
            DocumentContributor::create([
                'document_review_id' => $documentReview->id,
                'user_id' => $contributorId,
                'role' => $request->contributor_roles[$contributorId] ?? null
            ]);
        }

        // Add initial comment if provided
        if($request->initial_comment) {
            $initialComment = DocumentReviewComment::create([
                'document_review_id' => $documentReview->id,
                'user_id' => Auth::id(),
                'comment' => $request->initial_comment,
                'type' => 'general'
            ]);

            // Send comment notification to approver and contributors
            $this->notificationService->notifyDocumentComment($initialComment);
        }

        // Log the submission
        $documentReview->addLog('submitted', 'Work/Document submitted for review');

        // Send notification
        $notificationSent = $this->notificationService->notifyDocumentSubmitted($documentReview);

        $message = __('Work/Document submitted for review successfully!');
        if (!$notificationSent) {
            $message .= ' ' . __('(Email notification could not be sent)');
        }

        return redirect()->route('projects.document-review.index', $projectId)
            ->with('success', $message);
    }

    public function show($projectId, $documentId)
    {
        if(!Auth::user()->can('view project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $project = Project::findOrFail($projectId);
        $document = DocumentReview::with([
            'submitter', 
            'approver', 
            'category',
            'contributors.user', 
            'comments.user', 
            'logs.user'
        ])->findOrFail($documentId);

        return view('projects.document-review.show', compact('project', 'document'));
    }

    public function index($projectId, Request $request)
    {
        if(!Auth::user()->can('manage project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $project = Project::findOrFail($projectId);
        $documents = DocumentReview::where('project_id', $projectId)
            ->with(['submitter', 'approver', 'category', 'contributors.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Handle AJAX request
        if ($request->ajax() || $request->get('ajax')) {
            $limit = $request->get('limit', 5);
            $limitedDocuments = $documents->take($limit)->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'document_name' => $doc->document_name,
                    'category_name' => $doc->category_name,
                    'description' => $doc->description,
                    'document_link' => $doc->document_link,
                    'status' => $doc->status,
                    'submitter_name' => $doc->submitter->name,
                    'submission_date' => $doc->submission_date->format('Y-m-d'),
                ];
            });

            return response()->json([
                'success' => true,
                'documents' => $limitedDocuments
            ]);
        }

        return view('projects.document-review.index', compact('project', 'documents'));
    }

    // Category management methods
    public function getCategories($projectId)
    {
        $categories = DocumentReviewCategory::getAvailableForProject($projectId);
        
        return response()->json([
            'categories' => $categories->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'is_predefined' => $category->is_predefined,
                    'usage_count' => $category->getUsageCount()
                ];
            })
        ]);
    }

    public function createCategory(Request $request, $projectId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50'
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $category = DocumentReviewCategory::createCustomCategory(
            $request->name,
            $projectId,
            Auth::id(),
            $request->only(['description', 'color', 'icon'])
        );

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'color' => $category->color,
                'icon' => $category->icon,
                'is_predefined' => $category->is_predefined
            ]
        ]);
    }

    public function updateCategory(Request $request, $projectId, $categoryId)
    {
        $category = DocumentReviewCategory::findOrFail($categoryId);

        // Only allow editing custom categories
        if ($category->is_predefined) {
            return response()->json(['error' => 'Cannot edit predefined categories'], 403);
        }

        // Only allow editing by creator or admin
        if ($category->created_by !== Auth::id() && !Auth::user()->can('edit project')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50'
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $category->update($request->only(['name', 'description', 'color', 'icon']));

        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }

    public function deleteCategory($projectId, $categoryId)
    {
        $category = DocumentReviewCategory::findOrFail($categoryId);

        if (!$category->canBeDeleted()) {
            return response()->json(['error' => 'Cannot delete this category'], 403);
        }

        if ($category->created_by !== Auth::id() && !Auth::user()->can('delete project')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $category->delete();

        return response()->json(['success' => true]);
    }

    public function getCategoryStats($projectId)
    {
        $stats = DocumentReviewCategory::getCategoryStats($projectId);
        
        return response()->json([
            'categories' => $stats->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                    'icon' => $category->icon,
                    'document_reviews_count' => $category->document_reviews_count,
                    'is_predefined' => $category->is_predefined
                ];
            })
        ]);
    }

    // Rest of the existing methods remain the same...
    public function approve(Request $request, $projectId, $documentId)
    {
        $document = DocumentReview::findOrFail($documentId);
        
        if($document->approver_id !== Auth::id() && !Auth::user()->can('edit project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $document->approve(Auth::id(), $request->comment);

        $notificationSent = $this->notificationService->notifyDocumentStatusUpdated(
            $document, 
            'approved', 
            $request->comment, 
            Auth::user()
        );

        return redirect()->back()->with('success', __('Work/Document approved successfully!'));
    }

    public function reject(Request $request, $projectId, $documentId)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
            'comment' => 'required|string'
        ]);

        if($validator->fails()) {
            return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
        }

        $document = DocumentReview::findOrFail($documentId);
        
        if($document->approver_id !== Auth::id() && !Auth::user()->can('edit project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $document->reject($request->rejection_reason, Auth::id(), $request->comment);

        $notificationSent = $this->notificationService->notifyDocumentStatusUpdated(
            $document, 
            'rejected', 
            $request->comment, 
            Auth::user()
        );

        return redirect()->back()->with('success', __('Work/Document rejected successfully!'));
    }

    public function requireRevision(Request $request, $projectId, $documentId)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string'
        ]);

        if($validator->fails()) {
            return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
        }

        $document = DocumentReview::findOrFail($documentId);
        
        if($document->approver_id !== Auth::id() && !Auth::user()->can('edit project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $document->requireRevision($request->comment, Auth::id());

        $notificationSent = $this->notificationService->notifyDocumentStatusUpdated(
            $document, 
            'revision_required', 
            $request->comment, 
            Auth::user()
        );

        return redirect()->back()->with('success', __('Revision requested successfully!'));
    }

    public function underReview(Request $request, $projectId, $documentId)
    {

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string'
        ]);

        if($validator->fails()) {
            return redirect()->back()->with('error', Utility::errorFormat($validator->getMessageBag()));
        }

        $document = DocumentReview::findOrFail($documentId);
        
        if($document->approver_id !== Auth::id() && !Auth::user()->can('edit project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $document->underReview($request->comment, Auth::id());

        $notificationSent = $this->notificationService->notifyDocumentStatusUpdated(
            $document, 
            'under_review', 
            $request->comment, 
            Auth::user()
        );

        return redirect()->back()->with('success', __('Review started successfully!'));
    }

    public function addComment(Request $request, $projectId, $documentId)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $document = DocumentReview::findOrFail($documentId);
        
        $comment = DocumentReviewComment::create([
            'document_review_id' => $documentId,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'type' => 'general'
        ]);

        $document->addLog('commented', 'Added a comment');

        $notificationSent = $this->notificationService->notifyDocumentComment($comment);

        return response()->json([
            'success' => true,
            'notification_sent' => $notificationSent,
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'user_name' => $comment->user->name,
                'created_at' => $comment->created_at->format('d M Y H:i'),
                'type' => $comment->type_name
            ]
        ]);
    }

    public function destroy($projectId, $documentId)
    {
        if(!Auth::user()->can('delete project')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $document = DocumentReview::findOrFail($documentId);
        
        if($document->status === 'approved') {
            return redirect()->back()->with('error', __('Cannot delete approved work/document.'));
        }

        $document->delete();

        return redirect()->route('projects.document-review.index', $projectId)
            ->with('success', __('Work/Document deleted successfully!'));
    }

    public function getStatistics($projectId)
    {
        $stats = DocumentReview::getStatusStatistics($projectId);
        return response()->json($stats);
    }

    public function getPendingApprovals(Request $request)
    {
        $projectId = $request->get('project_id');
        $documents = DocumentReview::getPendingForApprover(Auth::id(), $projectId);
        
        $formattedDocuments = $documents->map(function($doc) {
            return [
                'id' => $doc->id,
                'document_name' => $doc->document_name,
                'category_name' => $doc->category_name,
                'document_link' => $doc->document_link,
                'status' => $doc->status,
                'submission_date' => $doc->submission_date->format('Y-m-d'),
                'project' => [
                    'id' => $doc->project->id,
                    'project_name' => $doc->project->project_name
                ],
                'submitter' => [
                    'id' => $doc->submitter->id,
                    'name' => $doc->submitter->name
                ]
            ];
        });

        return response()->json($formattedDocuments);
    }
}