function loadGames() {
  $.get('/api/schedule/get/').done(function(data) {
    var html = '<div class="col-lg-6"><div class="card"><div class="card-body"><h4>[quizname]</h4><!--<p class="card-text">[quizdesc]</p>--><p class="card-text">[quizplay]</p></div></div></div><!-- /.col-md-6 -->';
    $("#gameList").html("");
    if(data.length > 0) {
      for(var i=0;i<data.length;i++) {
        var thisHTML = ''+html;
        thisHTML = thisHTML.replace("[quizname]", data[i].title);
        thisHTML = thisHTML.replace("[quizdesc]", "description");
        thisHTML = thisHTML.replace("[quizplay]", '<br /><a class="btn btn-md btn-success" href="/play/' + data[i].quizId + '">Play</a>');
        $("#gameList").append(thisHTML);
      }
    } else {
      var thisHTML = ''+html;
      thisHTML = thisHTML.replace("[quizname]", "None found.");
      thisHTML = thisHTML.replace("[quizdesc]", "");
      thisHTML = thisHTML.replace("[quizplay]", "Only games in progress or that begin in the next 30 minutes will appear here.");
      $("#gameList").html(thisHTML);
    }
  }).fail(function(error) {
    alert(error);
  });
}