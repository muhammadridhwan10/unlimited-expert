@component('mail::message')
# New Comment on Document

Hello,

**{{ $commenter->name }}** has added a new comment on document "**{{ $document->document_name }}**" in project **{{ $project->project_name }}**.

## Document Details:
- **Document Name:** {{ $document->document_name }}
- **Category:** {{ $document->category_name }}
- **Current Status:** {{ ucfirst(str_replace('_', ' ', $document->status)) }}

## Comment:
@component('mail::panel')
{{ $comment->comment }}

*— {{ $commenter->name }}*  
*{{ $comment->created_at->format('d M Y H:i') }}*
@endcomponent

@component('mail::button', ['url' => $documentUrl])
View & Respond
@endcomponent

@component('mail::button', ['url' => $document->document_link, 'color' => 'success'])
Open Document
@endcomponent

This notification was sent to:
- Document submitter
- Approver
- All contributors

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent