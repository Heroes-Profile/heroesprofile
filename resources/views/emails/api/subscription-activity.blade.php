@component('mail::message')
# {{ $event }}

- **User:** {{ $userName }} ({{ $userEmail }})
@if ($plan)
- **Plan:** {{ $plan }}
@endif
@if ($endsAt)
- **Access ends:** {{ $endsAt }}
@endif
- **Recorded:** {{ now()->toDayDateTimeString() }} UTC
@endcomponent
