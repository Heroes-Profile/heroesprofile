<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title') | Heroes Profile</title>
  <meta name = "keywords" content = "@yield('meta_keywords')" />
  <meta name = "description" content = "@yield('meta_description')" />
  <meta property="og:image" content="">


  @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body>
  <div id="app">
<nav class="bg-gray-100 p-4">
  <div class="flex items-center space-x-4">

    <a class="text-blue-600 hover:text-blue-800" href="/">This is where the Heroes (logo) Profile would go</a>

    <div class="relative group inline-block">
      <a class="text-blue-600 hover:text-blue-800 cursor-pointer">Global Hero Stats</a>
      <div class="absolute left-0 hidden mt-0 space-y-2 bg-white border border-gray-300 rounded-md group-hover:block z-51">
        <a href="/Global/Hero" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Hero Stats</a>
        <a href="/Global/Talents" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Talent Stats</a>
        <a href="/Global/Hero/Maps" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Map Stats</a>
        <a href="/Global/Matchups" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Matchup Stats</a>
        <a href="/Global/Matchups/Talents" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Matchup Talent Stats</a>
        <a href="/Global/Compositions" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Compositional Stats</a>
        <a href="/Global/Draft/General" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Draft Stats</a>
        <a href="/Global/Party" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Party Stats</a>
        <a href="/Global/Extra" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Extra Stats</a>
      </div>
    </div>

    <a class="text-blue-600 hover:text-blue-800" href="/Global/Leaderboard">Leaderboards</a>

    <div class="relative group inline-block">
      <a class="text-blue-600 hover:text-blue-800 cursor-pointer">Zemill</a>
      <div class="absolute left-0 hidden mt-0 space-y-2 bg-white border border-gray-300 rounded-md group-hover:block z-51">
        <a href="/Profile/Zemill/67280/1" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Profile</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Friends And Foes</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Heroes</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Matchups</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Roles</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Maps</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Talents</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">MMR Breakdown</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Match History</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Compare</a>
      </div>
    </div>

    <div class="relative group inline-block">
      <a class="text-blue-600 hover:text-blue-800 cursor-pointer">Tools</a>
      <div class="absolute left-0 hidden mt-0 space-y-2 bg-white border border-gray-300 rounded-md group-hover:block z-51">
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Talent Builder</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Compare</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Drafter</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Game Data</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Replay Uploader</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Activity Graphs</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Auto Battler</a>
      </div>
    </div>


    @auth
      <!-- Navigation for authenticated users -->
      <a class="text-blue-600 hover:text-blue-800" href="/Profile/Settings">Profile Settings</a>
      <a class="text-blue-600 hover:text-blue-800" href="/Battlenet/Logout">Profile Logout</a>
    @endauth

    @guest
      <!-- Navigation for guests -->
      <a class="text-blue-600 hover:text-blue-800" href="/Authenticate/Battlenet">Login</a>
    @endguest
  </div>
</nav>



  <main class="py-4">
    @yield('content')
  </main>

<div class="footer-wrapper">
    <div class="footer container-boxed">
      <div class="container container-flex">

      <div class="logo">
        <img alt="Heroes Profile Logo" src=""/>
      </div>

      <div class="content ">
        <div class="footer-nav">

        </div>
        <div>{{ session('maxReplayID') }} replays | Patch {{ session('latestPatch') }} | Up to date as of: {{ session('latestGameDate') }}</div>
        <div class="copyright">Skill Tree Development, LLC | <a href="https://heroesprofile.com">Heroes Profile</a></div>
        </div>

      </div>

      <div class="disclaimer">
      </div>
    </div>
  </div>
</div>
</body>
</html>