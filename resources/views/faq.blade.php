@extends('layouts.app', $bladeGlobals)
@section('title', 'FAQ')
@section('meta_keywords', 'Heroes Profile FAQ, frequently asked questions, Heroes of the Storm stats')
@section('meta_description', 'Frequently asked questions about Heroes Profile — uploading replays, MMR, global stats, site features, and more.')
@section('content')
  <faq :recaptcha-site-key="'{{ $recaptchaSiteKey }}'" :patch-history="{{ json_encode($patchHistory) }}" :awards="{{ json_encode($awards) }}" :season-dates="{{ json_encode($seasonDates) }}" :faq-categories="{{ json_encode($faqCategories) }}" :faq-stopwords="{{ json_encode($faqStopwords) }}" :faq-pages="{{ json_encode($faqPages) }}"></faq>
@endsection
