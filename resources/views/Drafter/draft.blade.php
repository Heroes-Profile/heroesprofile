@extends('layouts.app')
@section('title', 'Drafter')

@section('content')
  @include('filters.globals')

  @if(!isset($landing))
    <section id="draft-setup">
      @include('Drafter.draftLanding')
    </section>
  @endif

  <section class="top-buttons">
    <div class="undo-button btn btn-secondary" id="undo-button">
      <i class="fas fa-reply"></i> Undo
    </div>

    <div class="show-filters-button btn btn-secondary" id="show-filters-button">
      <i class="fas fa-filter"></i> Show Filters
    </div>
  </section>
  <section id="main-draft">
    <div class="draft-hero-picker">

      <div class="draft-section draft-left" id="team1bans">
        <div class="draft-bans draft-bans-left">
          <span>Bans</span>
          <div class="flex-space-around">
            <div class="draft-single-ban compare-box disabled" id="team1-ban1" data-team="team1" data-pick="ban1" >
            </div>
            <div class="draft-single-ban compare-box disabled" id="team1-ban2" data-team="team1" data-pick="ban2">
            </div>
            <div class="draft-single-ban compare-box disabled" id="team1-ban3" data-team="team1" data-pick="ban3">
            </div>
          </div>
        </div>
      </div>


      <div class="hero-category-wrapper all-heroes" id="mainHeroInfo">
        <form class="" id="hero-search">
          <div class="all-heroes-search">
            <input class="form-control" type="text" id="filter-hero" placeholder="Search Heroes">
          </div>
          <div class="role-wrapper">
            <div class="rounded-item">
              <a class=" hero-picture"
              role="button"
              data-html="true"
              data-toggle="popover"
              data-trigger="hover"
              data-placement="top"
              data-rolename="Tank"
              title="Tank"  >
              <img src="/images/roles/tank.PNG">
            </a>
          </div>
          <div class="rounded-item">
            <a class="hero-picture"
            role="button"
            data-html="true"
            data-toggle="popover"
            data-trigger="hover"
            data-placement="top"
            data-rolename="Bruiser"
            title="Bruiser"  >
            <img src="/images/roles/bruiser.PNG">
          </a>
        </div>
        <div class="rounded-item">
          <a class=" hero-picture"
          role="button"
          data-html="true"
          data-toggle="popover"
          data-trigger="hover"
          data-placement="top"
          data-rolename="Healer"
          title="Healer"  >
          <img src="/images/roles/healer.PNG">
        </a>
      </div>
      <div class="rounded-item">
        <a class=" hero-picture"
        role="button"
        data-html="true"
        data-toggle="popover"
        data-trigger="hover"
        data-placement="top"
        data-rolename="Support"
        title="Support"  >
        <img src="/images/roles/support.PNG">
      </a>
    </div>
    <div class="rounded-item">
      <a class="hero-picture"
      role="button"
      data-html="true"
      data-toggle="popover"
      data-trigger="hover"
      data-placement="top"
      data-rolename="Melee Assassin"
      title="Melee Assassin"  >
      <img src="/images/roles/melee assassin.PNG">
    </a>
  </div>
  <div class="rounded-item">
    <a class=" hero-picture"
    role="button"
    data-html="true"
    data-toggle="popover"
    data-trigger="hover"
    data-placement="top"
    data-rolename="Ranged Assassin"
    title="Ranged Assassin"  >
    <img src="/images/roles/ranged assassin.PNG">
  </a>
</div>
</div>
</form>
</div>

<div class="draft-section draft-right" id="team2bans">
  <div class="draft-bans draft-bans-right">
    <span>Bans</span>
    <div class="flex-space-around">
      <div class="draft-single-ban compare-box disabled" id="team2-ban1" data-team="team2" data-pick="ban1">
      </div>
      <div class="draft-single-ban compare-box disabled" id="team2-ban2" data-team="team2" data-pick="ban2">
      </div>
      <div class="draft-single-ban compare-box disabled" id="team2-ban3" data-team="team2" data-pick="ban3">
      </div>
    </div>
  </div>
</div>
</div>
<div class="draft-hero-picker">

  <div class="relative-container all-heroes" id="mainHeroContent">
    <div class="loader"><i class="fas fa-circle-notch fa-spin"></i></div>

    <div class="container rounded-item-wrapper" id="draft-hero-wrapper">

      @include('Drafter.draftPicks')
    </div>
  </div>

  <div class="draft-section draft-left" id="team1picks">
    <div class="compare-box disabled" id="team1-pick1" data-team="team1" data-pick="pick1" >
    </div>
    <div class="compare-box disabled" id="team1-pick2" data-team="team1" data-pick="pick2">
    </div>
    <div class="compare-box disabled" id="team1-pick3" data-team="team1" data-pick="pick3">
    </div>
    <div class="compare-box disabled" id="team1-pick4" data-team="team1" data-pick="pick4">
    </div>
    <div class="compare-box disabled" id="team1-pick5" data-team="team1" data-pick="pick5">
    </div>
  </div>

  <div class="draft-section draft-right" id="team2picks">
    <div class="hero-ban-wrapper">
      <div class="compare-box disabled" id="team2-pick1" data-team="team2" data-pick="pick1">
      </div>
      <div class="compare-box disabled" id="team2-pick2" data-team="team2" data-pick="pick2">
      </div>
      <div class="compare-box disabled" id="team2-pick3" data-team="team2" data-pick="pick3">
      </div>
      <div class="compare-box disabled" id="team2-pick4" data-team="team2" data-pick="pick4">
      </div>
      <div class="compare-box disabled" id="team2-pick5" data-team="team2" data-pick="pick5">
      </div>
    </div>
  </div>
</div>


</div>
</section>

@endsection

@section('scripts')
  <script>
  $(document).ready(function() {
    if($('#timeframe').val() == "Major"){
      $('#minor_timeframe').selectpicker('hide');
      $('#major_timeframe').selectpicker('show');
    }else{
      $('#major_timeframe').selectpicker('hide');
      $('#minor_timeframe').selectpicker('show');
    }
    $('#undo-button').hide();
    $('#show-filters-button').hide();



    $('#timeframe').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
      if($('#timeframe').val() == "Major"){
        $('#minor_timeframe').selectpicker('hide');
        $('#major_timeframe').selectpicker('show');
      }else{
        $('#major_timeframe').selectpicker('hide');
        $('#minor_timeframe').selectpicker('show');
      }
    });







    var currentPickNumber=0;
    var heroesPicked = [];
    var teamOneHeroes = [];
    var teamTwoheroes = [];
    var filteredroles = Array();
    var allroles = Array("Tank", "Bruiser", "Healer", "Support", "Melee Assassin", "Ranged Assassin");
    var pickOrderTeam1 = [
      "team1-ban1",
      "team2-ban1",
      "team1-ban2",
      "team2-ban2",
      "team1-pick1",
      "team2-pick1",
      "team2-pick2",
      "team1-pick2",
      "team1-pick3",
      "team2-ban3",
      "team1-ban3",
      "team2-pick3",
      "team2-pick4",
      "team1-pick4",
      "team1-pick5",
      "team2-pick5"
    ];

    var pickOrderTeam2 = [
      "team2-ban1",
      "team1-ban1",
      "team2-ban2",
      "team1-ban2",
      "team2-pick1",
      "team1-pick1",
      "team1-pick2",
      "team2-pick2",
      "team2-pick3",
      "team1-ban3",
      "team2-ban3",
      "team1-pick3",
      "team1-pick4",
      "team2-pick4",
      "team2-pick5",
      "team1-pick5"
    ];

    var pickOrder;

    $('#main-draft').hide();

    $('#submit-draft').click(function(e){
      $('#draft-setup').hide();
      $('#show-filters-button').show();
      $('#basic_search').hide();



      var team_pick = $('#first-pick-team-1').is(':checked');
      pickOrder = pickOrderTeam1;

      if(!team_pick){
        pickOrder = pickOrderTeam2;
      }









      updatePick(pickOrder[currentPickNumber], heroesPicked, currentPickNumber);
      $('#main-draft').fadeIn();
    });




    $('.show-filters-button').click(function(){
      $('#basic_search').slideToggle();

      if($('#show-filters-button').text().trim() == "Show Filters"){
        $("#show-filters-button").html("<i class='fas fa-filter'></i> Hide Filters");
      }else{
        $("#show-filters-button").html("<i class='fas fa-filter'></i> Show Filters");
      }

    });

    $('.undo-button').click(function(){

      if(currentPickNumber == 1){
        $('#undo-button').hide();
      }

      $('#draft-hero-wrapper').attr('disabled');
      $('.loading').show();
      $('#draft-hero-wrapper .rounded-picture').popover('hide');
      $('#'+pickOrder[currentPickNumber]).remove('disabled');
      $('#'+pickOrder[currentPickNumber]).removeClass('highlight-player');

      currentPickNumber--;

      if(currentPickNumber == 4 || currentPickNumber == 7 || currentPickNumber == 8 || currentPickNumber == 13 || currentPickNumber == 14){
        teamOneHeroes.pop();
      }else if(currentPickNumber == 5 || currentPickNumber == 6 || currentPickNumber == 11 || currentPickNumber == 12 || currentPickNumber == 15){
        teamTwoheroes.pop();
      }

      heroesPicked.pop();
      $('#'+pickOrder[currentPickNumber]).css('background-image', '');

      updatePick(pickOrder[currentPickNumber], heroesPicked, currentPickNumber);




    });


    //On click of an image
    $('.all-heroes').on('click', '#draft-hero-wrapper .hero-picture', function(){
      if(currentPickNumber >= 0){
        $('#undo-button').show();
      }else{
        $('#undo-button').hide();
      }
      if($('#draft-hero-wrapper').attr('disabled') != "disabled" ) {
        $('#draft-hero-wrapper .rounded-picture').popover('hide');
        $("#draft-hero-wrapper").attr('disabled', 'disabled');

        var heroname = $(this).data('heroname');
        var id = $(this).data('heroid');
        var heropicture = $(this).data('heroimg');

        if(currentPickNumber == 4 || currentPickNumber == 7 || currentPickNumber == 8 || currentPickNumber == 13 || currentPickNumber == 14){
          teamOneHeroes.push(id);
        }else if(currentPickNumber == 5 || currentPickNumber == 6 || currentPickNumber == 11 || currentPickNumber == 12 || currentPickNumber == 15){
          teamTwoheroes.push(id);
        }

        heroesPicked.push(id);


        var teampick = $('.highlight-player').data('team')+'-'+$('.highlight-player').data('pick');
        $('.compare-box.highlight-player').css('background-image', 'url(' + heropicture + ')');

        if(currentPickNumber < pickOrderTeam1.length){

          currentPickNumber++;
        }

        if(currentPickNumber > 4 && currentPickNumber != 10 && currentPickNumber != 11){
          if(id == 11){
            $('.highlight-player').removeClass('highlight-player');
            $('#'+pickOrder[currentPickNumber]).removeClass('disabled');
            $('#'+pickOrder[currentPickNumber]).addClass('highlight-player');

            var heroname = 'Gall';
            var id = 18;
            var heropicture = '{{ asset('/images/heroes/gall.png') }}';

            if(currentPickNumber == 4 || currentPickNumber == 7 || currentPickNumber == 8 || currentPickNumber == 13 || currentPickNumber == 14){
              teamOneHeroes.push(id);
            }else if(currentPickNumber == 5 || currentPickNumber == 6 || currentPickNumber == 11 || currentPickNumber == 12 || currentPickNumber == 15){
              teamTwoheroes.push(id);
            }

            heroesPicked.push(id);


            var teampick = $('.highlight-player').data('team')+'-'+$('.highlight-player').data('pick');
            $('.compare-box.highlight-player').css('background-image', 'url(' + heropicture + ')');

            if(currentPickNumber < pickOrderTeam1.length){

              currentPickNumber++;
            }

          }else if(id == 18){
            $('.highlight-player').removeClass('highlight-player');
            $('#'+pickOrder[currentPickNumber]).removeClass('disabled');
            $('#'+pickOrder[currentPickNumber]).addClass('highlight-player');

            var heroname = 'Cho';
            var id = 11;
            var heropicture = '{{ asset('/images/heroes/cho.png') }}';

            if(currentPickNumber == 4 || currentPickNumber == 7 || currentPickNumber == 8 || currentPickNumber == 13 || currentPickNumber == 14){
              teamOneHeroes.push(id);
            }else if(currentPickNumber == 5 || currentPickNumber == 6 || currentPickNumber == 11 || currentPickNumber == 12 || currentPickNumber == 15){
              teamTwoheroes.push(id);
            }

            heroesPicked.push(id);


            var teampick = $('.highlight-player').data('team')+'-'+$('.highlight-player').data('pick');
            $('.compare-box.highlight-player').css('background-image', 'url(' + heropicture + ')');

            if(currentPickNumber < pickOrderTeam1.length){

              currentPickNumber++;
            }

          }

        }

        if(currentPickNumber <= 15){
          updatePick(pickOrder[currentPickNumber], heroesPicked, currentPickNumber);
        }
      }


    });

    function updatePick(pickOrder, heroesPicked, currentPickNumber){
      $('#draft-hero-wrapper').html();
      $('.highlight-player').removeClass('highlight-player');
      $('#'+pickOrder).removeClass('disabled');
      $('#'+pickOrder).addClass('highlight-player');
      updateDraftHeroes(heroesPicked, currentPickNumber);
    }

    function updateDraftHeroes(heroesPicked, currentPickNumber){
      var formData = $('#basic_search').serializeArray();


      var teamPick;
      if(currentPickNumber == 4 || currentPickNumber == 7 || currentPickNumber == 8 || currentPickNumber == 13 || currentPickNumber == 14){
        teamPick = teamOneHeroes;
      }else if(currentPickNumber == 5 || currentPickNumber == 6 || currentPickNumber == 11 || currentPickNumber == 12 || currentPickNumber == 15){
        teamPick = teamTwoheroes;
      }

      var url;
      if(currentPickNumber <= 3){
        url = '/getDraftBanData';
      }else if(currentPickNumber == 4 || currentPickNumber == 5){
        url = '/getInitialDraftData';
      }else if(currentPickNumber == 9 || currentPickNumber == 10){
        url = '/getCompositionData';


        if(currentPickNumber == 9){
          teamPick = teamOneHeroes;
        }else{
          teamPick = teamTwoheroes;
        }



      }else{
        url = '/getCompositionData';
      }


      parameters =
      {
        'data' : formData,
        'heroesPicked' : heroesPicked,
        'teamPicks' :teamPick,
        'currentPickNumber' : currentPickNumber
      }
      //$("#draft-hero-wrapper").html('');
      $('.loader').show();
      $('#draft-hero-wrapper .rounded-picture').popover('hide');
      //$('#draft-hero-wrapper').html('');
      $.ajax({
        url: url,
        data: parameters,
        type: "POST",
        success: function(results){
          $("#draft-hero-wrapper").removeAttr('disabled');
          $('#draft-hero-wrapper .rounded-picture').popover('hide');
          $('#draft-hero-wrapper').html(results);
          $('.rounded-picture').popover();
          $('.loader').hide();
          filterroles();
        }
      });
    }

    $('#filter-hero').on('input', function() {
      if($('#filter-hero').val() != ''){

        $('.rounded-item-wrapper .rounded-item').each(function(){

          var filterhero = removeDiacritics($('#filter-hero').val().toLowerCase()).replace(/[^\w\s]/gi, '');
          var heroname = removeDiacritics($(this)[0].firstChild.attributes[2].nodeValue.toLowerCase()).replace(/[^\w\s]/gi, '');

          if(heroname.indexOf(filterhero) != -1){
            $(this).show();
          }
          else{
            $(this).hide();
          }


        });
      }else{
        $('.popup-trigger').show();
      }
    });


    $('.role-wrapper .hero-picture').click(function(){

      var rolename = $(this).data('rolename');
      if($(this).hasClass('roleselected')){
        $(this).removeClass('roleselected');
        var rolehref = $(this).find('img').attr('src').replace('-highlighted.PNG', '.PNG');
        $(this).find('img').attr('src', rolehref);

      }
      else {
        $(this).addClass('roleselected');

        var rolehref = $(this).find('img').attr('src').replace('.PNG', "-highlighted.PNG");
        $(this).find('img').attr('src', rolehref);
      }
      filteredroles = Array();
      $('.role-wrapper .hero-picture').each(function(){
        if($(this).hasClass('roleselected')){
          filteredroles.push($(this).data('rolename'));
        }
      });
      filterroles();
    });


    function filterroles(){

      if(filteredroles.length > 0){
        $.each( allroles, function( index, value ){
          if(filteredroles.indexOf(value) > -1){

            $('*[data-herorolename="'+value+'"]').parent().show();
          }
          else{
            $('*[data-herorolename="'+value+'"]').parent().hide();
          }

        });
      } else{
        $('#draft-hero-wrapper .rounded-item').show();
      }

    }


    function removeDiacritics(input)
    {
      var output = "";

      var normalized = input.normalize("NFD");
      var i=0;
      var j=0;

      while (i<input.length)
      {
        output += normalized[j];

        j += (input[i] == normalized[j]) ? 1 : 2;
        i++;
      }

      return output;
    }

    //DOC Ready Function Ending Bracket
  });
</script>

@endsection
