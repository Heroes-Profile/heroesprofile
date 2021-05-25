@extends('layouts.app')
@section('title', "Profile")

@section('content')


  <body>
    <div class="container-fluid">
      <div class="row">


        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div class="card">
                  <div class="card-header">Dashboard</div>

                  <div class="card-body">
                    @if (session('status'))
                      <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                      </div>
                    @endif
                    {{ Auth::user()->battletag }}
                    You are logged in!
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  </body>




@endsection
@section('scripts')
  <script>
  $(document).ready(function() {
  });
  </script>
@endsection
