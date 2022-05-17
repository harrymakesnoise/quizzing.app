var canToggleRIP = true;
var currentOpenRound = 0;
var hasAlwaysAvailableRounds = false;

function setupAlwaysAvailable() {
  if(hasAlwaysAvailableRounds) {
    $("#mc-roundkey-current").show();
    $("#mc-roundkey-na").show();
    $("#mc-roundkey-any").show();
    $("#mc-playarea-information-alwaysavailable").show();
  } else {
    $("#mc-roundkey-current").removeClass("col-md-4").addClass("col-md-6").show();
    $("#mc-roundkey-na").removeClass("col-md-4").addClass("col-md-6").show();
  }
}

function parseGameTimeline(packet) {
  var outHTML = '<hr />';
  for(var i=0;i<packet.rounds.length;i++) {
    var round = packet.rounds[i];
    var roundClass = "secondary";
    var roundDisabled = " disabled";
    if(round.questions != null) {
      roundClass = "success";
      roundDisabled = "";
      hasAlwaysAvailableRounds = true;
    }
    outHTML += '<button type="button" class="btn btn-block bg-gradient-' + roundClass + '" data-roundid="' + round.roundid + '"' + roundDisabled + '>' + round.label + '</button>';
    roundData[round.roundid] = round;
  }
  outHTML += '<hr /><button type="button" class="btn btn-block bg-gradient-warning" data-roundid="help">Game Help</button>';
  $("#mc-roundlist").html(outHTML).slideDown().promise().done(function() {
    $("#mc-roundlist").parent().parent().find(".overlay").fadeOut();
    $("#mc-playarea").find(".overlay").fadeOut();
  });
  $("#mc-roundlist .btn.btn-block").on('click', function() {
    if($(this).prop("disabled")) { return false; }
    toggleRoundPlay($(this).data("roundid"), $(this).hasClass("bg-gradient-success"));
  });
  setupAlwaysAvailable();
}

function mc_gametoggle_start_cb(packet) {
  console.log("GAMESTART");
}

function mc_gametoggle_stop_cb() {
  //When game has ended
  $("#mc-playarea-currentround").html("").remove();
  $("#mc-playarea-alwaysavailable").html("").remove();
  $(".overlay").fadeOut();
  $("#mc-roundlist").html("").remove();
  toggleGameHelp();
  $("#mc-playarea-information").html("The game has now ended.<br /><br />Thanks for taking part - the final leaderboard will be released in the next few days.");
}

function mc_roundtoggle_start_cb(packet) {
  //When round has been started
  console.log("ROUNDSTART");
  var roundButtons = $("#mc-roundlist button[data-roundid!='help']:not([class*='bg-gradient-success'])");
  roundButtons.each(function() {
    if(parseInt($(this).data('roundid')) != parseInt(currentRound.roundid)) {
      $(this).removeClass('bg-gradient-info').addClass('bg-gradient-secondary').prop('disabled', true);
    } else {
      $(this).removeClass('bg-gradient-secondary').addClass('bg-gradient-info').prop('disabled', false);
    }
  });
}

function showRoundAlert(type, roundid) {
  if(currentOpenRound != roundid) {
    $("#modal-round-alert-body").html("A new " + type + " has arrived on the live game.");
    $("#modal-round-alert").modal("show");
  }
}

function mc_roundtoggle_stop_cb() {
  //When round is cleared
  console.log("ROUNDEND");
  currentQuestion = null;
  var roundButtons = $("#mc-roundlist button[data-roundid!='help']:not([class*='bg-gradient-success'])");
  roundButtons.removeClass('bg-gradient-info').addClass('bg-gradient-secondary').prop("disabled", true);

  $("#mc-playarea-currentround-question").html('<i>The round has now finished.</i>');
}

function mc_questiontoggle_send_cb(packet) {
  //When question is sent
  console.log("QUESTIONSTART");
  $("#mc-playarea-currentround-question").html(renderQuestionHTML(currentRound.roundid, packet.currentdata.question, packet.currentdata.question.questionIndex));
}

function mc_teamanswered_cb() {}

function mc_questiontoggle_clear_cb(jsonPacket) {
  //When question is cleared
  console.log("QUESTIONEND");
  if('roundjuststarted' in jsonPacket.currentdata) {
    $("#mc-playarea-currentround-question").html("The round will begin shortly.");
  } else {
    if('lastquestion' in jsonPacket.currentdata) {
      $("#mc-playarea-currentround-question").html("Round complete.");
    } else {
      $("#mc-playarea-currentround-question").html("The next question will arrive shortly.");
    }
  }
}

function renderQuestionHTML(roundid, questionData, qCount) {
  if(typeof questionData === "undefined") { return false; }
  if(typeof roundid === "undefined") { return false; }
  if(typeof qCount === "undefined") {
    qCount = 1;
  }
  var multipleChoice = (questionData.answers != null);
  var hasPicture     = (questionData.questionimage != null);

  var returnHTML = '<div class="form-group">';
  if(questionData.label != "") {
    returnHTML += '<label for="' + questionData.questionid + '">' + qCount + '. ' + questionData.label + '</label>';
  }
  if(hasPicture) {
    returnHTML += '<br /><img src="/siteassets/' + questionData.questionimage + '"><br /><br />';
  }

  if(answeredQuestions[roundid] != null && answeredQuestions[roundid][questionData.questionid] != null) {
    returnHTML += getAnsweredHTML(answeredQuestions[roundid][questionData.questionid]);
  } else {
    if(multipleChoice) {
      returnHTML += '<div class="form-group questionBoxContainer" id="' + roundid + "-" + questionData.questionid + '-answers">';
      for(var i=1;i<=questionData.answers.length;i++) {
        var answer = questionData.answers[i-1];
        if(i % 2) {
          if(i > 1) {
            returnHTML += "</div>";
          }
          returnHTML += "<div class='row m-2'>";
        }
        returnHTML += '<div class="col-6"><div class="icheck-primary d-inline"><input type="radio" id="' + roundid + '-' + questionData.questionid + '-' + i + '" name="' + roundid + '-' + questionData.questionid + '" value="' + answer + '"><label for="' + roundid + '-' + questionData.questionid + '-' + i + '">' + answer + '</label></div></div>';
      }
      returnHTML += '</div></div>';
    } else {
      returnHTML += '<div class="form-group questionBoxContainer" id="' + roundid + "-" + questionData.questionid + '-answers"><input type="text" id="' + roundid + '-' + questionData.questionid + '" class="form-control"></div>';
    } 
    returnHTML += '<div class="form-group text-right" id="' + roundid + '-' + questionData.questionid + '-btn"><button class="btn btn-md btn-success" onClick="submitAnswer(' + roundid + ',' + questionData.questionid + ');">Submit Answer</button></div>';
  }

  returnHTML += '</div>';
  return returnHTML;
}

function toggleGameHelp() {
  if($("#mc-playarea-information").css('display') == "none") {
    $("#mc-playarea-alwaysavailable").slideUp().promise().done(function() {
      $("#mc-playarea-currentround").slideUp().promise().done(function() {
        $("#mc-playarea-information").slideDown();
        canToggleRIP = true;
      });
    });
  }
}

function toggleRoundPlay(roundid, alwaysavailable) {
  $("#modal-round-alert").modal("hide");
  if(!canToggleRIP) { return false; }
  if(typeof roundid === "undefined") {return false;}
  if(typeof alwaysavailable === "undefined") {
    var alwaysavailable = false;
  }
  if(roundid == "help") {
    toggleGameHelp();
    return false;
  }
  if(roundData[roundid] == null) {return false;}
  if(alwaysavailable && roundData[roundid].questions == null) {return false;}
  
  canToggleRIP = false;
  
  if(currentOpenRound == roundid) {
    $("#mc-playarea-alwaysavailable").slideUp().promise().done(function() {
      $("#mc-playarea-currentround").slideUp().promise().done(function() {
        $("#mc-playarea-information").slideDown();
        canToggleRIP = true;
      });
    });
    currentOpenRound = 0;
    return false;
  }
  currentOpenRound = roundid;
  
  var thisRound = roundData[roundid];
  var outputHTML = '<div class="card-body row p-0"><div class="col-md-6 text-left"><h5>' + thisRound.label + '</h5></div><div class="col-md-6 text-right"><small>' + thisRound.questioncount + ' questions</small></div></div>';
  
  if(thisRound.questions != null && thisRound.questions.length > 0) {
    for(var i=0; i<thisRound.questions.length;i++) {
      var a = i+1;
      outputHTML += renderQuestionHTML(thisRound.roundid, thisRound.questions[i], a);
    }
  }
  
  $("#mc-playarea-alwaysavailable").slideUp().promise().done(function() {
    $("#mc-playarea-currentround").slideUp().promise().done(function() {
      $("#mc-playarea-information").slideUp().promise().done(function() {
        if(alwaysavailable) {
          $("#mc-playarea-alwaysavailable").html(outputHTML).slideDown();
        } else {
          $("#mc-playarea-currentround-header").html(outputHTML);
          $("#mc-playarea-currentround").slideDown();
        }
        canToggleRIP = true;
      });
    });
  });
}

function submitAnswer(roundid, questionid) {
  if(typeof roundid === "undefined") { return false; }
  if(typeof questionid === "undefined") { return false; }
  var answerBox = $("#" + roundid + "-" + questionid);
  var answer = new Array();
  
  if(answerBox.length) {
    answer.push(answerBox.val());
  } else {
    var multiChoiceOptions = $("#" + roundid + "-" + questionid + "-answers input");
    var answers = new Array();
    multiChoiceOptions.each(function() {
      if($(this).is(':checked')) {
        answers.push($(this).val());
      }
    });
    answer = answers;
  }

  if(answer.length <= 0) {
    toastr.error("Cannot submit an empty answer!");
    return false;
  }

  sendToServer({"type": "answer-submit", "callback": "submitanswer_cb", "data": {"questionid": questionid, "roundid": roundid, "answer": JSON.stringify(answer)}});
}

function submitanswer_cb(packet) {
  if((packet.userid == userid && packet.score != null) || (packet.message != null && packet.message == "Question already answered.")) {
    $("#" + packet.roundid + "-" + packet.questionid + "-answers").html(getAnsweredHTML(packet));
    $("#" + packet.roundid + "-" + packet.questionid + "-btn").remove();
  }
}

function getAnsweredHTML(packet) {
  var html = '<div class="form-group questionBoxContainer">Your answer has been submitted.';
  // if((packet.amendedscore != null || packet.awardedscore != null) && packet.autoscorer != null && packet.autoscorer) {

  //   var awardedScore = 0;
  //   if(packet.awardedscore != null) {
  //     if(packet.awardedscore > 0) {
  //       awardedScore = packet.awardedscore;
  //     }
  //   }
  //   if(packet.amendedscore != null) {
  //     if(packet.amendedscore > 0) {
  //       awardedScore = packet.amendedscore;
  //     }
  //   }

  //   var s = awardedScore != 1 ? "s" : "";
  //   var c = awardedScore > 0 ? "" : "in";

  //   if(packet.amended != null) {
  //     html += "<br />Your score has been amended, you have now received " + awardedScore + " point" + s + " for your answer";
  //   } else {
  //     html += "<br />You were given " + awardedScore + " point" + s + " for your " + c + "correct answer";
  //     if(packet.correctanswers != null) {
  //       if(packet.correctanswers.length > 1) {
  //         html += "s ";
  //       }
  //       var answerHTML = "";
  //       for(var i=0;i<packet.correctanswers.length;i++) {
  //         if(i == packet.correctanswers.length-1 && i > 0) {
  //           answerHTML += " and ";
  //         } else {
  //           answerHTML += ", ";
  //         }
  //         answerHTML += packet.correctanswers[i];
  //       }
  //       html += answerHTML.trim(", ");
  //     } else if(packet.incorrectanswers != null) {
  //       if(packet.incorrectanswers.length > 1) {
  //         html += "s ";
  //       }
  //       var answerHTML = "";
  //       for(var i=0;i<packet.incorrectanswers.length;i++) {
  //         if(i == packet.incorrectanswers.length-1 && i > 0) {
  //           answerHTML += " and ";
  //         } else {
  //           answerHTML += ", ";
  //         }
  //         answerHTML += packet.incorrectanswers[i];
  //       }
  //       html += answerHTML.trim(", ");
  //     }
  //   }
  // }

  return html + '</div>';
}