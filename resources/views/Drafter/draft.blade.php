@extends('layouts.app')
@section('title', 'Drafter')

@section('content')
  I would like a landing page that is also the new draft page that requires them to make their filter choices there.
  That way there is no load time for the page and make sure users don't change settings in the middle of draft.  Simplified

  During this initial set up, they should choose which team first picks.  I think our JS is set up to handle that.

  Add loading icon when it is pulling data.

  Fix centering of Search Heroes and Role Filter.  Spread role icons out a little bit

  Clicking on roles puts them into the draft as if they were heroes.

  @if(!isset($landing))
    <section id="draft-setup">
      @include('Drafter.draftLanding')
    </section>
  @endif
  <section id="main-draft">
    <div class="draft-hero-picker">

      <div class="draft-section draft-left">

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

      <div class="hero-category-wrapper all-heroes">
        <form class="" id="hero-search">
          <div class="all-heroes-search">
            <input class="form-control" type="text" id="filter-hero" placeholder="Search Heroes">
          </div>

          <div class="role-wrapper">
            <div class="rounded-item"><a class=" hero-picture"
              role="button"
              data-html="true"
              data-toggle="popover"
              data-trigger="hover"
              data-placement="top"
              data-herorolename="tank"
              title="Tank"  >
              <img src="/images/roles/tank.PNG">
            </a>
          </div>
          <div class="rounded-item"><a class="hero-picture"
            role="button"
            data-html="true"
            data-toggle="popover"
            data-trigger="hover"
            data-placement="top"
            data-herorolename="bruiser"
            title="Bruiser"  >
            <img src="/images/roles/bruiser.PNG">
          </a>
        </div>
        <div class="rounded-item"><a class=" hero-picture"
          role="button"
          data-html="true"
          data-toggle="popover"
          data-trigger="hover"
          data-placement="top"
          data-herorolename="healer"
          title="Healer"  >
          <img src="/images/roles/healer.PNG">
        </a>
      </div>
      <div class="rounded-item"><a class=" hero-picture"
        role="button"
        data-html="true"
        data-toggle="popover"
        data-trigger="hover"
        data-placement="top"
        data-herorolename="support"
        title="Support"  >
        <img src="/images/roles/support.PNG">
      </a>
    </div>
    <div class="rounded-item"><a class="hero-picture"
      role="button"
      data-html="true"
      data-toggle="popover"
      data-trigger="hover"
      data-placement="top"
      data-herorolename="melee_assassin"
      title="Melee Assassin"  >
      <img src="/images/roles/melee assassin.PNG">
    </a>
  </div>
  <div class="rounded-item"><a class=" hero-picture"
    role="button"
    data-html="true"
    data-toggle="popover"
    data-trigger="hover"
    data-placement="top"
    data-herorolename="ranged_assassin"
    title="Ranged Assassin"  >
    <img src="/images/roles/ranged assassin.PNG">
  </a>
</div>



</div>
</form>

<div class="container rounded-item-wrapper" id="draft-hero-wrapper">
  @include('Drafter.draftPicks')
</div>
</div>



<div class="draft-section draft-right">

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
</section>

@endsection

@section('scripts')
  <script>
  $(document).ready(function() {

    var currentPickNumber=0;
    var heroesPicked = [];
    var teamOneHeroes = [];
    var teamTwoheroes = [];

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

      var team_pick = $('#first-pick-team-1').is(':checked');







      pickOrder = pickOrderTeam1;

      if(!team_pick){
        pickOrder = pickOrderTeam2;
      }




      //$('.draft-hero-picker').fadeIn();



      updatePick(pickOrder[currentPickNumber], heroesPicked, currentPickNumber);


      $('#main-draft').fadeIn();


    });



    //On click of an image
    $('.all-heroes').on('click', '.hero-picture', function(){


      $('.rounded-picture').popover('hide');
      $('#draft-hero-wrapper').html('');
      var heroname = $(this).data('heroname');
      var id = $(this).data('heroid');
      var heropicture = $(this).data('heroimg');

      if(currentPickNumber == 4 || currentPickNumber == 7 || currentPickNumber == 8 || currentPickNumber == 13 || currentPickNumber == 14){
        console.log("Team 1 Picks");
        teamOneHeroes.push(id);
      }else if(currentPickNumber == 5 || currentPickNumber == 6 || currentPickNumber == 11 || currentPickNumber == 12 || currentPickNumber == 15){
        console.log("Team 2 Picks");
        teamTwoheroes.push(id);
      }

      heroesPicked.push(id);


      var teampick = $('.highlight-player').data('team')+'-'+$('.highlight-player').data('pick');
      console.log(teampick);
      $('.compare-box.highlight-player').css('background-image', 'url(' + heropicture + ')');

      if(currentPickNumber < pickOrderTeam1.length){

        currentPickNumber++;
      }

      if(currentPickNumber > 4 && currentPickNumber != 10 && currentPickNumber != 11){
        console.log("here");
        if(id == 11){
          console.log("Cho was picked");
          $('.highlight-player').removeClass('highlight-player');
          $('#'+pickOrder[currentPickNumber]).removeClass('disabled');
          $('#'+pickOrder[currentPickNumber]).addClass('highlight-player');

          var heroname = 'Gall';
          var id = 18;
          var heropicture = '{{ asset('/images/heroes/gall.png') }}';

          if(currentPickNumber == 4 || currentPickNumber == 7 || currentPickNumber == 8 || currentPickNumber == 13 || currentPickNumber == 14){
            console.log("Team 1 Picks");
            teamOneHeroes.push(id);
          }else if(currentPickNumber == 5 || currentPickNumber == 6 || currentPickNumber == 11 || currentPickNumber == 12 || currentPickNumber == 15){
            console.log("Team 2 Picks");
            teamTwoheroes.push(id);
          }

          heroesPicked.push(id);


          var teampick = $('.highlight-player').data('team')+'-'+$('.highlight-player').data('pick');
          console.log(teampick);
          $('.compare-box.highlight-player').css('background-image', 'url(' + heropicture + ')');

          if(currentPickNumber < pickOrderTeam1.length){

            currentPickNumber++;
          }

        }else if(id == 18){
          console.log("Gall was picked");
          $('.highlight-player').removeClass('highlight-player');
          $('#'+pickOrder[currentPickNumber]).removeClass('disabled');
          $('#'+pickOrder[currentPickNumber]).addClass('highlight-player');

          var heroname = 'Cho';
          var id = 11;
          var heropicture = '{{ asset('/images/heroes/cho.png') }}';

          if(currentPickNumber == 4 || currentPickNumber == 7 || currentPickNumber == 8 || currentPickNumber == 13 || currentPickNumber == 14){
            console.log("Team 1 Picks");
            teamOneHeroes.push(id);
          }else if(currentPickNumber == 5 || currentPickNumber == 6 || currentPickNumber == 11 || currentPickNumber == 12 || currentPickNumber == 15){
            console.log("Team 2 Picks");
            teamTwoheroes.push(id);
          }

          heroesPicked.push(id);


          var teampick = $('.highlight-player').data('team')+'-'+$('.highlight-player').data('pick');
          console.log(teampick);
          $('.compare-box.highlight-player').css('background-image', 'url(' + heropicture + ')');

          if(currentPickNumber < pickOrderTeam1.length){

            currentPickNumber++;
          }

        }

      }

      if(currentPickNumber <= 15){
        updatePick(pickOrder[currentPickNumber], heroesPicked, currentPickNumber);
      }
    });

    function updatePick(pickOrder, heroesPicked, currentPickNumber){
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


      parameters =
      {
        'data' : formData,
        'heroesPicked' : heroesPicked,
        'teamPicks' :teamPick,
        'currentPickNumber' : currentPickNumber
      }

      var url;
      if(currentPickNumber <= 3 || currentPickNumber == 9 || currentPickNumber == 10){
        url = '/getDraftBanData';
      }else if(currentPickNumber == 4 || currentPickNumber == 5){
        url = '/getInitialDraftData';
      }else{
        url = '/getCompositionData';
      }
      $.ajax({
        url: url,
        data: parameters,
        //type: "POST",
        success: function(results){
          $('#draft-hero-wrapper').html(results);
          $('.rounded-picture').popover();
        }
      });
    }


    $('.pick-label input').click(function(){
      $('.pick-label').addClass('disabled');
      if($(this).is(':checked')){
        $(this).parent('.pick-label').removeClass('disabled');
      }
    });


    /*
    $('#hero-search').submit(function(e){
    e.preventDefault();

    $('html, body').animate({
    scrollTop: $(".hero-wrapper").offset().top
  }, 2000);

});
*/

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
