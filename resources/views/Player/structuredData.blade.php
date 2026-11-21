{{-- A player page's structured data: the profile it belongs to, and breadcrumbs from the site to this page.
     $page is this page's name ('Party Win Rates', 'Artanis'...), left out on the profile itself. --}}
@php
  $profileUrl = url('/Player/' . rawurlencode($battletag) . '/' . $blizz_id . '/' . $region);
  $playerName = $battletag . ' (' . ($bladeGlobals['regions'][$region] ?? $region) . ')';
  $crumbs = [
      ['name' => 'Heroes Profile', 'url' => url('/')],
      ['name' => $playerName, 'url' => $profileUrl],
  ];
  if (! empty($page)) {
      $crumbs[] = ['name' => $page, 'url' => url()->current()];
  }
  $structuredData = [
      [
          '@context' => 'https://schema.org',
          '@type' => 'ProfilePage',
          'url' => url()->current(),
          'mainEntity' => [
              '@type' => 'Person',
              'name' => $battletag,
              'alternateName' => $playerName,
              'identifier' => $blizz_id . '-' . $region,
              'url' => $profileUrl,
          ],
      ],
      [
          '@context' => 'https://schema.org',
          '@type' => 'BreadcrumbList',
          'itemListElement' => collect($crumbs)->values()->map(fn ($crumb, $index) => [
              '@type' => 'ListItem',
              'position' => $index + 1,
              'name' => $crumb['name'],
              'item' => $crumb['url'],
          ])->all(),
      ],
  ];
@endphp
@push('structured_data')
  <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
