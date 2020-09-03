function extraHeader(results){
  //Average Win Rate Header
  var count = 0;
  var average_win_rate = 0;
  average_win_rate = results.reduce(function (s, a) {
      count++;
      return s + parseFloat(a.win_rate);
  }, 0);
  $('#avg_win_rate').text(function() {
      return (average_win_rate / count).toFixed(2);
  });

  //Average Win Rate Confidence Header
  var average_win_rate_confidence = 0;
  average_win_rate_confidence = results.reduce(function (s, a) {
      return s + parseFloat(a.win_rate_confidence);
  }, 0);
  $('#avg_win_rate_confidence').text(function() {
      return (average_win_rate_confidence / count).toFixed(2);
  });

  //Average Win Rate Change Header
  var count_positive = 0;
  var count_negative = 0;
  var average_change_positive = 0;
  average_change_positive = results.reduce(function (s, a) {
    if(parseFloat(a.change) >= 0){
      count_positive++;
      return s + parseFloat(a.change);
    }else{
      return s;
    }
  }, 0);
  var average_change_negative = 0;
  average_change_negative = results.reduce(function (s, a) {
    if(parseFloat(a.change) < 0){
      count_negative++;
      return s + parseFloat(a.change);
    }else{
      return s;
    }
  }, 0);
  $('#avg_change').text(function() {
      return (average_change_positive / count_positive).toFixed(2) + " | " + (average_change_negative / count_negative).toFixed(2);
  });

  //Average Popularity Header
  var average_popularity = 0;
  average_popularity = results.reduce(function (s, a) {
      return s + parseFloat(a.popularity);
  }, 0);
  $('#avg_popularity').text(function() {
      return (average_popularity / count).toFixed(2);
  });


  //Average Pick Rate Header
  var average_pick_rate = 0;
  average_pick_rate = results.reduce(function (s, a) {
      return s + parseFloat(a.pick_rate);
  }, 0);
  $('#avg_pick_rate').text(function() {
      return (average_pick_rate / count).toFixed(2);
  });

  //Average Ban Rate Header
  var average_ban_rate = 0;
  average_ban_rate = results.reduce(function (s, a) {
      return s + parseFloat(a.ban_rate);
  }, 0);
  $('#avg_ban_rate').text(function() {
      return (average_ban_rate / count).toFixed(2);
  });

  //Average Influence Header
  var count_positive = 0;
  var count_negative = 0;
  var average_influence_positive = 0;
  average_influence_positive = results.reduce(function (s, a) {
    if(parseFloat(a.influence) >= 0){
      count_positive++;
      return s + parseFloat(a.influence);
    }else{
      return s;
    }
  }, 0);
  var average_influence_negative = 0;
  average_influence_negative = results.reduce(function (s, a) {
    if(parseFloat(a.influence) < 0){
      count_negative++;
      return s + parseFloat(a.influence);
    }else{
      return s;
    }
  }, 0);
  $('#avg_influence').text(function() {
      return (average_influence_positive / count_positive).toFixed(0) + " | " + (average_influence_negative / count_negative).toFixed(0);
  });

  //Average Games Played Header
  var average_games_played = 0;
  average_games_played = results.reduce(function (s, a) {
      return s + parseFloat(a.games_played);
  }, 0);
  $('#avg_games_played').text(function() {
      return (average_games_played / count).toFixed(0);
  });

  //Average Wins Header
  var average_wins = 0;
  average_wins = results.reduce(function (s, a) {
      return s + parseFloat(a.wins);
  }, 0);
  $('#avg_wins').text(function() {
      return (average_wins / count).toFixed(0);
  });

  //Average Losses Header
  var average_losses = 0;
  average_losses = results.reduce(function (s, a) {
      return s + parseFloat(a.losses);
  }, 0);
  $('#avg_losses').text(function() {
      return (average_losses / count).toFixed(0);
  });

  //Average Games Banned Header
  var average_games_banned = 0;
  average_games_banned = results.reduce(function (s, a) {
      return s + parseFloat(a.games_banned);
  }, 0);
  $('#avg_games_banned').text(function() {
      return (average_games_banned / count).toFixed(0);
  });
}
