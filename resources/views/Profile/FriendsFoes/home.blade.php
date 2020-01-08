<?php
namespace App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//Auth::loginUsingId(1, true);

/*
if (Auth::check()) {
    $user = Auth::user();
    print_r($user);
}
*/

$player_instance = \ProfileData::instance("Zemill#1940", 67280, 1, "", "");
$friendsFoes = $player_instance->getPlayerFriendsAndFoes();

$game_type = "";
$region = 1;
?>

<section>

  <h2>Friends And Foes Top 50</h2>
<div class="friendfoewrapper">
  <div class="table-wrapper">
    <table  id = "friends_chart" class="primary-data-table general-table">
      <thead>
        <?php
          if($game_type != ""){
            echo "<th colspan=5>Friends</th>";
          }else{
            echo "<th colspan=4>Friends</th>";
          }
        ?>
      </thead>
      <thead>
        <th id = "friend_player_column">Player<i class="fas fa-sort-down"></i></th>
        <th id = "friend_favorite_hero_column">Favorite Hero<i class="fas fa-sort-down"></i></th>
        <th id = "friend_games_played_column">Games Played With<i class="fas fa-sort-down"></i></th>
        <?php
          if($game_type != ""){
            ?>
              <th id = "friend_mmr_column">MMR<i class="fas fa-sort-down"></i></th>
            <?php
          }
        ?>
        <th id = "friend_win_rate_column">Win Rate With Friend<i class="fas fa-sort-down"></i></th>
      </thead>
    <tbody>
      <?php
      $count = 50;
      foreach($friendsFoes[0] as $f_blizz_id => $friend_for_data){
        if($count == 0){
          break;
        }
        $count--;
        arsort($friend_for_data["hero"]);
        echo "<tr>" .
        "<td><a href=" . "\"" . "/Profile/?blizz_id=" . $f_blizz_id . "&battletag=" . explode('#', $friendsFoes[2][$f_blizz_id])[0] .
        "&region=" . $region . "\"" . ">" . explode('#', $friendsFoes[2][$f_blizz_id])[0] . "</a></td>" .

        "<td><a href=" . "\"" . "/Hero/Single/?blizz_id=" . $f_blizz_id . "&battletag=" . explode('#', $friendsFoes[2][$f_blizz_id])[0] .
        "&region=" . $region . "&hero=" . key($friend_for_data["hero"]) . "\"" . ">" . key($friend_for_data["hero"]) . "</a></td>" .

        "<td>" . $friend_for_data["games_played"] . "</td>";
        if($game_type != ""){
          echo "<td>" . $data->getPlayerMMR($game_type, $f_blizz_id, $region) . "</td>";
        }
        echo "<td>" . number_format(($friend_for_data["wins"] / $friend_for_data["games_played"]) * 100, 2);
        echo "</td></tr>";
      }

       ?>
    </tbody>

  </table>
  </div>




  <div class="table-wrapper">
    <table  id = "foe_chart" class="primary-data-table general-table foes-table">
      <thead>
        <th colspan=5>Foes</th>
      </thead>
      <thead>
        <th id = "foe_player_column">Player<i class="fas fa-sort-down"></i></th>
        <th id = "foe_favorite_hero_column">Favorite Hero<i class="fas fa-sort-down"></i></th>
        <th id = "foe_games_played_column">Games Played With<i class="fas fa-sort-down"></i></th>
        <?php
          if($game_type != ""){
            ?>
              <th id = "foe_mmr_column">MMR<i class="fas fa-sort-down"></i></th>
            <?php
          }
        ?>
        <th id = "foe_win_rate_column">Foe's Win Rate Against You<i class="fas fa-sort-down"></i></th>
      </thead>
    <tbody>
      <?php
      $count = 50;
      foreach($friendsFoes[1] as $f_blizz_id => $friend_for_data){
        if($count == 0){
          break;
        }
        $count--;

        arsort($friend_for_data["hero"]);
        echo "<tr>" .
        //"<td><a href=" . "\"" . "/Profile/?blizz_id=" . $blizz_id . "&battletag=" . explode('#', $friendsFoes[2][$blizz_id])[0] . "&region=" . $region . ">" . explode('#', $friendsFoes[2][$blizz_id])[0] . "</a></td>" .
        "<td><a href=" . "\"" . "/Profile/?blizz_id=" . $f_blizz_id . "&battletag=" . explode('#', $friendsFoes[2][$f_blizz_id])[0] .
        "&region=" . $region . "\"" . ">" . explode('#', $friendsFoes[2][$f_blizz_id])[0] . "</a></td>" .
        "<td><a href=" . "\"" . "/Hero/Single/?blizz_id=" . $f_blizz_id . "&battletag=" . explode('#', $friendsFoes[2][$f_blizz_id])[0] .
        "&region=" . $region . "&hero=" . key($friend_for_data["hero"]) . "\"" . ">" . key($friend_for_data["hero"]) . "</a></td>" .
        "<td>" . $friend_for_data["games_played"] . "</td>";
        if($game_type != ""){
            echo "<td>" . $data->getPlayerMMR($game_type, $f_blizz_id, $region) . "</td>";
          }
        echo "<td>" . number_format(($friend_for_data["wins"] / $friend_for_data["games_played"]) * 100, 2);
        echo "</td></tr>";
      }

       ?>
    </tbody>

  </table>
  </div>
</div>

</section>
