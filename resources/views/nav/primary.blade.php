@if(checkAlertForHeader())
  <div class="heading-alert alert alert-danger">{!! getAlertHeader() !!}</div>
@endif
<nav class="primary-nav navbar navbar-dark bg-dark">
  <a class="navbar-brand" href="/">
    Heroes
    <img src="/images/logo/heroesprofilelogo.png" width="34" height="30" class="d-inline-block align-top" alt="">
    Profile
  </a>

  <div class="header-buttons">
    <a class="header-button btn btn-sm btn-primary" href="https://api.heroesprofile.com"  target="_blank">Heroes Profile API</a>
  {{--  <a class="header-button btn btn-sm btn-secondary" href="/Series" target="_blank">Amateur Series</a>--}}
    <a class="header-button btn btn-sm btn-danger" href="https://www.patreon.com/heroesprofile" target="_blank">Become a Patreon</a>
  </div>


</nav>
<nav class="secondary-nav navbar navbar-expand-lg navbar-dark">

  <button class="navbar-toggler ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse ml-auto" id="navbarSupportedContent">
    <ul class="navbar-nav ml-auto">

      <li class="nav-item">
        <a class="nav-link active" href="/Drafter" id="drafter-link">Drafter</a>
      </li>

      <li class="nav-item">
        <a class="nav-link active" href="/docs" id="drafter-link">Documentation</a>
      </li>
</div>


</nav>
