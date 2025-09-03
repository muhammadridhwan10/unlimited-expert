@component('mail::message')
# New Document Submitted for Review

Hello **{{ $document->approver->name }}**,

A new document has been submitted for your review in project **{{ $project->project_name }}**.

## Document Details:
- **Document Name:** {{ $document->document_name }}
- **Category:** {{ $document->category_name }}
- **Submitted by:** {{ $submitter->name }}
- **Submission Date:** {{ $document->submission_date->format('d M Y') }}

@if($document->description)
**Description:**
{{ $document->description }}
@endif

@component('mail::button', ['url' => $reviewUrl])
Review Document
@endcomponent

You can also open the document directly:
@component('mail::button', ['url' => $document->document_link, 'color' => 'success'])
Open Document
@endcomponent

**Contributors:**
@foreach($document->contributors as $contributor)
- {{ $contributor->user->name }}
@endforeach

Please review the document at your earliest convenience.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent