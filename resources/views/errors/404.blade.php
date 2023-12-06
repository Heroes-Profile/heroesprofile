@extends('layouts.app', ['regions' => [
    1 => 'NA',
    2 => 'EU',
    3 => 'KR',
    /* 4 => 'UNK', */
    5 => 'CN',
]])


@section('title', 'Error')
@section('meta_keywords', '404 error, page not found, not found error, website error, error page, site not found')
@section('meta_description', 'The page you were looking for could not be found.')

@section('content')
<h1 class="p-20 text-center font-logo">You got lost</h1>
<div class="flex justify-center items-center text-center  bg-gray-100">
  <a href="/"><img class="max-w-md w-full h-auto pb-20" src="/images/miscellaneous/404.png" alt="Town Portal"></a>
</div>

<div style="font-size: 10px;">Image referenced from https://static.heroesofthestorm.com/comics/secrets-of-the-storm/en-us-09ab7fb85c.pdf</div>
@endsection
