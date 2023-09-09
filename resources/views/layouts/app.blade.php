<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
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
<body class="bg-black text-white">
  <div id="app">
<nav class="bg-gray-dark text-white p-4 z-50 relative">
  <div class="flex items-center space-x-4 items-center">

    <a class="text-blue-600 hover:text-blue-800 flex items-center font-logo text-2xl mr-auto" href="/">Heroes<img class="w-10 mx-2" src="/images/logo/heroesprofilelogo.png"/>Profile</a>

    <div class="relative group inline-block z-50 ">
      <a class="text-blue-600 hover:text-blue-800 cursor-pointer">Global Hero Stats</a>
      <div class="absolute left-0  hidden   group group-hover:block hover:block z-50 pt-5">
        <div class="bg-blue border border-gray-300 rounded-md">
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
    </div>

    <a class="text-blue-600 hover:text-blue-800" href="/Global/Leaderboard">Leaderboards</a>

    <div class="relative group inline-block">
      <a class="text-blue-600 hover:text-blue-800 cursor-pointer">Zemill</a>

      <div class="absolute left-0  hidden   group group-hover:block hover:block z-50 pt-5">
        <div class="bg-blue border border-gray-300 rounded-md">
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
    </div>

    <div class="relative group inline-block">
      <a class="text-blue-600 hover:text-blue-800 cursor-pointer">Tools</a>
      <div class="absolute left-0  hidden   group group-hover:block hover:block z-50 pt-5">
        <div class="bg-blue border border-gray-300 rounded-md">
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Talent Builder</a>
        <a href="/Compare" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Compare</a>
        <a href="https://drafter.heroesprofile.com/Drafter" target="_blank" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Drafter</a>
        <a href="https://dev.heroesprofile.com/" target="_blank" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Game Data</a>
        <a href="https://api.heroesprofile.com/upload" target="_blank" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Replay Uploader</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Activity Graphs</a>
        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Auto Battler</a>
      </div>
    </div>
    </div>
   
    <custom-button :href="'#'" :text="'API'" :alt="'API'"  :size="'small'" :color="'teal'"></custom-button>
    <custom-button :href="'#'" :text="'Replay Uploader'" :alt="'Replay Uploader'"  :size="'small'" :color="'blue'"></custom-button>
     <custom-button :href="'#'" :text="'Patreon'" :alt="'Patreon'"  :size="'small'" :color="'red'"></custom-button>

    

    @auth
      <!-- Navigation for authenticated users -->

      <a class="text-blue-600 hover:text-blue-800" href="/Profile/Settings">Profile Settings</a>
      <a class="text-blue-600 hover:text-blue-800" href="/Battlenet/Logout">Profile Logout</a>
    @endauth

    @guest
      <!-- Navigation for guests -->
      <custom-button :href="'/Authenticate/Battlenet'" :text="'Login'" :alt="'Login'" :size="'small'" :color="'teal'" ></custom-button>
      
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