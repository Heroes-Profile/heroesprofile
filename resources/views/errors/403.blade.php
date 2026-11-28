{{-- Also shown for banned IPs, admin-only pages and replay downloads (with their own code, title and message) --}}
@extends('errors.layout')

@section('title', __($errorTitle ?? 'Access Denied'))
@section('meta_keywords', '403 error, access denied, forbidden')
@section('meta_description', 'You do not have permission to view this page.')

@section('error_badge', __('Error :code', ['code' => $errorCode ?? 403]))
@section('error_heading', __($errorTitle ?? 'Access Denied'))
@section('error_text', __($message ?? 'You do not have permission to view this page.'))

@section('error_actions')
    <custom-button :href="'/'" :text="'Take me home'" :alt="'Take me home'" :color="'teal'" class="font-semibold"></custom-button>
    <custom-button :href="'/Contact'" :text="'Contact us'" :alt="'Contact us'" class="font-semibold"></custom-button>
@endsection

@if (isset($ban_reason) || isset($banned_date))
    @section('error_extra')
        <div class="border-t border-surface-3 pt-5 flex flex-col gap-3">
            <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ __('Ban details') }}</span>
            <div class="rounded bg-surface-1 p-4 flex flex-col gap-2 text-sm">
                @isset($ban_reason)
                    <p><span class="text-subtext">{{ __('Reason') }}:</span> {{ $ban_reason }}</p>
                @endisset
                @isset($banned_date)
                    <p><span class="text-subtext">{{ __('Banned on') }}:</span> {{ $banned_date->format('M j, Y H:i') }} UTC</p>
                @endisset
            </div>
            <p class="text-sm text-subtext">{{ __('If you believe this is an error, please contact us.') }}</p>
        </div>
    @endsection
@endif
