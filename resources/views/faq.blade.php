@extends('layouts.app', $bladeGlobals)
@section('title', __('Heroes Profile FAQ'))
@section('meta_keywords', 'Heroes Profile FAQ, frequently asked questions, Heroes of the Storm stats')
@section('meta_description', 'Frequently asked questions about Heroes Profile — uploading replays, MMR, global stats, site features, and more.')
{{-- Every question and answer, for search engines; answers keep only the tags Google reads --}}
@php
  $faqPage = [
      '@context' => 'https://schema.org',
      '@type' => 'FAQPage',
      'mainEntity' => collect($faqCategories)->flatMap(fn ($category) => $category['items'])->map(fn ($item) => [
          '@type' => 'Question',
          'name' => $item['q'],
          'acceptedAnswer' => [
              '@type' => 'Answer',
              'text' => trim(strip_tags($item['a'], '<a><b><strong><i><em><br><p><ul><ol><li>')),
          ],
      ])->values()->all(),
  ];
@endphp
@push('structured_data')
  <script type="application/ld+json">{!! json_encode($faqPage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
  <faq :recaptcha-site-key="'{{ $recaptchaSiteKey }}'" :patch-history="{{ json_encode($patchHistory) }}" :awards="{{ json_encode($awards) }}" :season-dates="{{ json_encode($seasonDates) }}" :faq-categories="{{ json_encode($faqCategories) }}" :faq-stopwords="{{ json_encode($faqStopwords) }}" :faq-pages="{{ json_encode($faqPages) }}"></faq>
@endsection
