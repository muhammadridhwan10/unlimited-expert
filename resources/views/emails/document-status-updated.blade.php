@component('mail::message')
# Document {{ ucfirst($statusAction) }}

Hello **{{ $document->submitter->name }}**,

Your document "**{{ $document->document_name }}**" in project **{{ $project->project_name }}** has been **{{ $statusAction }}** by {{ $reviewer->name }}.

## Document Details:
- **Document Name:** {{ $document->document_name }}
- **Category:** {{ $document->category_name }}
- **Status:** {{ ucfirst(str_replace('_', ' ', $document->status)) }}
- **Reviewed by:** {{ $reviewer->name }}
- **Date:** {{ now()->format('d M Y H:i') }}

@if($comment)
## Reviewer's Comment:
{{ $comment }}
@endif

@if($document->status == 'approved')
@component('mail::panel')
🎉 **Congratulations!** Your document has been approved and is ready for use.
@endcomponent
@elseif($document->status == 'rejected')
@component('mail::panel')
❌ **Document Rejected.** Please review the feedback and make necessary changes before resubmitting.
@endcomponent
@elseif($document->status == 'revision_required')
@component('mail::panel')
✏️ **Revision Required.** Please make the requested changes and update your document.
@endcomponent
@endif

@component('mail::button', ['url' => $documentUrl])
View Document Details
@endcomponent

@if($document->document_link)
@component('mail::button', ['url' => $document->document_link, 'color' => 'success'])
Open Document
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent