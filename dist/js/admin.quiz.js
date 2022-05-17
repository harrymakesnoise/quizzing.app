var timerRunning = false;
var roundSettings = new Object();
var scoreOverride = new Object();
var questionTimerCountdown = null;
var questionTimerCountdownVal = 0;
var changeCount = 0;
var afterTimerAction = 1;
var depthQuestionAnswers = new Object();
var currentInspectedRoundId = 0;
var currentInspectedQuestionId = 0;
var currentInspectedLoaded = false;
var questionTimerVal = 0;
var answeredDataFromServer = new Object();
var renderedAnswers = new Object();
var teamsAnsweredData = new Object();

function game_control_sendtosite() {
  sendToServer();
}

function game_control_clearpackets() {
  quizPackets = new Array();
  $("#sendtosite-control").find("tr").each(function() {
    if($(this).attr("id")) {
      $(this).find("td").html("-");
      $(this).fadeOut();
    }
  });
  $("#sendtosite-control").slideUp();
}

function mc_gametoggle_start() {
  return {"type": "game-event", "command": "start-game"};
}

function mc_gametoggle_start_cb(packetdata) {
  if(packetdata.currentdata != null) {
    if(packetdata.currentdata.rounds != null) {
      var rHTML = "";
      var qHTML = "";
      for(var k in packetdata.currentdata.rounds) {
        var round = packetdata.currentdata.rounds[k];
        if(!('questions' in round)) {
          rHTML += "<option value='" + round.roundid + "' data-label='" + encodeURI(round.label) + "'>" + round.label + "</option>";
        }
      }
      $("#mc-roundlist").html(rHTML).trigger("change");
      $("#mc-questionlist").html(qHTML).trigger("change");
    }
  }
}

function parseGameTimeline(packet) {
  var outHTML = "";
  var selectorHTML = "";
  for(var i=0;i<packet.rounds.length;i++) {
    var round = packet.rounds[i];
    selectorHTML += "<optgroup label='" + round.label + "'>";
    outHTML += "<h4>" + round.label + "</h4><ol>";
    for(var a=0;a<round.questions.length;a++) {
      var question = round.questions[a];
      outHTML += "<li class='text-black-50' id='mc-quiztimeline-" + round.roundid + "-" + question.questionid + "'>" + question.label + "</li>";
      selectorHTML += "<option value='" + round.roundid + "-" + question.questionid + "'>" + question.label + "</option>";
    }
    outHTML += "</ol>";
    selectorHTML += "</optgroup>";
    roundData[round.roundid] = round;
  }
  $("#mc-questioninfo-selector").html(selectorHTML).select2({'theme': 'bootstrap4'});
  $("#mc-quiztimeline").html(outHTML);
}

function mc_gametoggle_stop() {
  return {"type": "game-event", "command": "end-game"};
}

function mc_gametoggle_stop_cb() {
  //When game has ended
}

function mc_roundtoggle_start() {
  return {"type": "game-event", "command": "start-round", "data": $("#"+$("#mc-roundtoggle-start").data("extradata")).val()};
}

function mc_roundtoggle_stop() {
  return {"type": "game-event", "command": "end-round"};
}

function mc_roundtoggle_stop_cb() {
  $("#mc-questionlist").html("");
  currentQuestion = null;
}

function mc_roundtoggle_start_cb(packetdata) {
  timerRunning = false;
  
  var qHTML = "";
  
  var round = roundData[parseInt(currentRound.roundid)];
  if(round != null) {
    if(round.questions != null) {
      qHTML += "<optgroup label='" + round.label + "'>";
      for(var i in round.questions) {
        var question = round.questions[i];
        qHTML += "<option value='" + question.questionid + "' data-label='" + encodeURI(question.label) + "'>" + question.questionIndex + ". " + question.label + "</option>";
      }
    }
    $("#mc-roundlist option").removeAttr("selected");
    $("#mc-roundlist option[value='" + round.roundid + "']").attr("selected", "selected");
    $("#mc-questionlist").html(qHTML).trigger("change");
    
    roundSettings = packetdata.currentdata.scoring;
    
    if(packetdata.currentdata.overridescoring != null) {
      scoreOverride = packetdata.currentdata.overridescoring;
    }
    updateSeenQuestions();
    toggleRoundSettings();
  }
}

function addScoreRow() {
  var outputElement = $("#round-mod-score-dynamicrows");
  var outputLength = outputElement.children().length+1;
  var nextNumber = outputLength;
  var sup = "th";
  switch(nextNumber.toString().slice(-1)) {
    case "1": sup = "st"; break;
    case "2": sup = "nd"; break;
    case "3": sup = "rd"; break;
  }
  var newLabel = nextNumber + "<sup>" + sup + "</sup>";
  var newId = nextNumber;
  var disabledStr = (!$("#round-mod-auto-scorer").prop("checked") ? " disabled" : "");
  if(roundSettings[currentRound.roundid] != null) {
    var value = roundSettings[currentRound.roundid]['round-mod-speed-score-' + newId] != null ? roundSettings[currentRound.roundid]['round-mod-speed-score-' + newId] : 0;
  } else {
    var value = 0;
  }
  var outputHTML = '<tr><td class="align-middle"><label for="round-mod-speed-score-' + newId + '" style="font-weight:normal;">' + newLabel + ' team to answer correctly</label></td><td class="align-middle"><div class="input-group"><input class="form-control" type="number" id="round-mod-speed-score-' + newId + '" min="0" step="1"' + disabledStr + '><div class="input-group-append"><span class="input-group-text">pts</span></div></div></td></tr>';

  $("#round-mod-score-dynamicrows").append(outputHTML); 
  $("#round-mod-score-remove").fadeIn();
  attachInputListeners();
  
  $("#round-mod-speed-score-" + newId).val(value);
}

function removeScoreRow() {
  var outputElement = $("#round-mod-score-dynamicrows");
  outputElement.children().last()[0].remove();
  var outputLength = outputElement.children().length;
  if(outputLength <= 0) {
    $("#round-mod-score-remove").fadeOut();
  }
  $("#mc-scoreoverride-send").show();
}

function getCompareRoundScores() {
  var localObj = roundSettings[currentRound.roundid];
  if(localObj != null) {
    var deleteKeys = ["round-mod-always-available", "round-mod-end-after", "round-mod-endroundafter-value"];
    for(var i=0;i<deleteKeys.length;i++) {
      var key = deleteKeys[i];
      if(Object.keys(localObj).indexOf(key) >= 0) {
        delete localObj[key];
      }
    }
    return localObj;
  }
  return {};
}

var changedScoresFromDefault = false;
function toggleRoundSettings() {
  $("#round-mod-score-dynamicrows").html("");
  var thisRoundSettings = new Object();
  if(roundSettings[currentRound.roundid] != null) {
    thisRoundSettings = roundSettings[currentRound.roundid];
  } else {
    thisRoundSettings = roundSettings.default;
  }

  var altObj = getCompareRoundScores();
  if(scoreOverride != null && Object.keys(scoreOverride).length > 0 && JSON.stringify(scoreOverride) != JSON.stringify(altObj)) {
    changedScoresFromDefault = true;
    thisRoundSettings = scoreOverride;
    $("#mc-scoreoverride-reset").show();
  } else {
    changedScoresFromDefault = false;
    $("#mc-scoreoverride-reset").hide();
  }
  for(var k in thisRoundSettings) {
    var inputPrefix = "round-mod-speed-score-";
    if(k.substring(0,inputPrefix.length) == inputPrefix) {
      var scoreNumber = k.substring(inputPrefix.length,inputPrefix.length+1);
      if(!isNaN(scoreNumber)) {
        addScoreRow();
      }
    }
  }
  var inputs = $("#roundModifierContainer input,#roundModifierContainer select,#roundModifierContainer button");
  inputs.each(function() {
    if(!$(this).hasClass("btn") && thisRoundSettings != null) {
      var val = null;
      if(thisRoundSettings[$(this).attr("id")] != null) {
        val = thisRoundSettings[$(this).attr("id")]; 
      }
      if(val != null) {
        if($(this).attr("type") == "checkbox") {
          $(this).prop("disabled", false).change();
          $(this).prop("checked", val).change();
        } else if($(this).hasClass("select2bs4")) {
          $(this).val(val).trigger("change");
        } else {
          $(this).val(val);
        }
      }
    }
  });
  inputs.each(function() {
    if($(this).attr("type") != "checkbox") {
      $(this).prop("disabled", !$("#round-mod-auto-scorer").prop("checked"));
    }
  });
  setTimeout(function() {
    inputs.each(function() {
      $(this).trigger("change");
    });
  }, 100);
}

function mc_questiontoggle_send() {
  return {"type": "game-event", "command": "send-question", "data": {"round": $("#"+$("#mc-roundtoggle-start").data("extradata")).val(), "question": $("#"+$("#mc-questiontoggle-send").data("extradata")).val()}};
}

function mc_questiontoggle_clear() {
  return {"type": "game-event", "command": "end-question"};
}

function calcScoreOverrideAllowed() {
  var scoreModEnabled = $("#round-mod-auto-scorer").prop('checked');
  if(currentRound != null && currentQuestion == null) {
    $("#round-mod-speedscore-buttons button,#roundModifierContainer input,#roundModifierContainer select").prop("disabled", !scoreModEnabled);
    $("#round-mod-auto-scorer").prop('disabled', false);
    $("#mc-score-config-alert").fadeOut();
  } else {
    $("#round-mod-speedscore-buttons button,#roundModifierContainer input,#roundModifierContainer select").prop("disabled", true);
    $("#round-mod-auto-scorer").prop('disabled', true);
    $("#mc-score-config-alert").fadeIn();
  }
}

function mc_scoreoverride_send() {
  var obj = {"type": "game-event", "command": "update-score-template", "data": {}};
  var inputs = $("#roundModifierContainer input,#roundModifierContainer select");
  inputs.each(function() {
    var val = $(this).val();
    if($(this).attr("type") == "checkbox") {
      val = $(this).prop("checked");
    }
    obj.data[$(this).attr("id")] = val;
  });
  return obj;
}

function mc_scoreoverride_send_cb(packetdata) {
  scoreOverride = packetdata.currentdata.overridescoring;
  $("#mc-scoreoverride-send").hide();
  toggleRoundSettings();
}
function mc_scoreoverride_reset_cb(packetdata) {
  scoreOverride = new Object();
  toggleRoundSettings();
}

function mc_scoreoverride_reset() {
  if(!changedScoresFromDefault) {
    scoreOverride = new Object();
  
    toggleRoundSettings();
  } else {
    return {"type": "game-event", "command": "update-score-template", "data": roundSettings[currentRound.roundid]};
  }
}

function mc_questiontoggle_clear_cb() {
}

function showRoundAlert() {}

function mc_questiontoggle_send_cb() {
  timerRunning = false;

  if(currentRound == null) {
    return false;
  }
  
  $("#mc-questionlist option").removeAttr("selected");
    
  $("#mc-quiztimeline li").removeClass("text-bold");

  $("#mc-quiztimeline-" + currentRound.roundid + "-" + currentQuestion.questionid).addClass("text-bold");
  

  if((currentInspectedRoundId == 0 || currentInspectedRoundId == currentRound.roundid) && (currentInspectedQuestionId == 0 || currentInspectedQuestionId == currentQuestion.questionid)) {
    renderQuestionInformation(currentRound.roundid, currentQuestion.questionid);
    $("#mc-questioninfo-selector option").removeAttr("selected");
    $("#mc-questioninfo-selector option[value='" + currentRound.roundid + "-" + currentQuestion.questionid + "']").attr("selected", "selected").trigger("change");
    renderedAnswers = new Object();
  }
  
  $("#mc-questionlist option[value=" + currentQuestion.questionid + "]").attr("selected", "selected").parent().val(currentQuestion.questionid).trigger('change');
  $("#mc-teamsanswered .status span").html('Waiting').removeClass('badge-success').removeClass('badge-warning').addClass('badge-secondary');
}

function renderQuestionInformation(roundid, questionid) {
  if(roundData[roundid] != null) {
    var rdata  = roundData[roundid].questions;
    var html = "";
    for(var i=0;i<rdata.length;i++) {
      if(rdata[i].questionid == questionid) {
        qdata = rdata[i];
        html = "<h4>" + qdata.label + "</h4>";
        if(qdata.correctanswers != null) {
          if(typeof qdata.correctanswers === "array") {
            html += "<p>Correct answer(s): <strong>" + qdata.correctanswers.join(",") + "</strong>";
          } else {
            html += "<p>Correct answer(s): <strong>" + qdata.correctanswers + "</strong>";
          }
        }
      }
    }

    $("#mc-questioninfo").html(html);
    questionAnswersTable.clear().draw();

    quizServer.server.send(JSON.stringify({"type": "game-event", "command": "get-answers", "callback": "mc_getanswers_cb", "data": {"roundid": roundid, "questionid": questionid}}));
  }
}

function mc_getanswers_cb(packet) {
  mc_teamanswered_cb(packet.gameupdate);
}

function mc_teamanswered_cb(packet) {
  if(packet.gameupdate != null) {
    packet = packet.gameupdate;
  }
  if(packet.teamsanswered != null) {
    packet = packet.teamsanswered;
  }
  
  for (var userid in packet) {
    for (var roundid in packet[userid]) {
      for (var questionid in packet[userid][roundid]) {
        var user = new Object();
        if(seenGameData.allusers != null) {
          if(seenGameData.allusers[userid] != null) {
            user = seenGameData.allusers[userid];
          }
        }
        var newPacket = {"user": user, "data": packet[userid][roundid][questionid]};
        var look = roundid + "-" + questionid;
        if(teamsAnsweredData != null) {
          if(teamsAnsweredData[look] == null) {
            teamsAnsweredData[look] = new Object();
          }
          if(teamsAnsweredData[look][userid] != null) {
            if(JSON.stringify(newPacket) != JSON.stringify(teamsAnsweredData[look][userid])) {
              questionAnswersTable.rows( function ( idx, data, node ) {
                  return parseInt(data[0]) === parseInt(userid);
              } )
              .remove()
              .draw(false);
              delete renderedAnswers[userid];
            }
          }
          teamsAnsweredData[look][userid] = newPacket;
        }
      }
    }
  }
  refreshQuestionInfoAnswers();
  makeAnsweredCounts();
}

function refreshQuestionInfoAnswers() {
  var roundid = currentInspectedRoundId;
  var questionid = currentInspectedQuestionId;
  if(roundid == 0 && questionid == 0) {
    if(currentRound != null && currentQuestion != null) {
      roundid = currentRound.roundid;
      questionid = currentQuestion.questionid;
    }
  }

  if(teamsAnsweredData != null) {
    var look = roundid + "-" + questionid;
    if(teamsAnsweredData[look] != null) {
      for(var i in teamsAnsweredData[look]) {
        var mainPacket = teamsAnsweredData[look][i];
        if(renderedAnswers[i] == null) {
          var answerPacket = mainPacket.data;
          var userPacket = mainPacket.user;
          if(answerPacket.awardedscore != null || answerPacket.amendedscore != null) {
            var awardedScore = 0;
            if(answerPacket.awardedscore != null) {
              if(answerPacket.awardedscore != 0) {
                awardedScore = answerPacket.awardedscore;
              }
            }
            if(answerPacket.amendedscore != null) {
              if(answerPacket.amendedscore != 0) {
                awardedScore = answerPacket.amendedscore;
              }
            }
            var answerStr = "";
            var btn = '<input type="number" step="1" value="' + awardedScore + '" id="mc-adjustanswer-' + userPacket.userid + '-' + roundid + '-' + questionid + '"><button class="btn btn-md btn-success" onClick="mc_adjustanswer(' + userPacket.userid + ', ' + roundid + ', ' + questionid + ', $(\'#mc-adjustanswer-' + userPacket.userid + '-' + roundid + '-' + questionid + '\').val());$(this).remove();return false;">Adjust Score</button>';
            if(answerPacket.incorrectanswers != null) {
              if(answerPacket.incorrectanswers.length > 0) {
                answerStr = answerPacket.incorrectanswers.join(",");
              }
            }
            if(answerPacket.correctanswers != null) {
              if(answerPacket.correctanswers.length > 0) {
                answerStr = answerPacket.correctanswers.join(",");
              }
            }

            questionAnswersTable.row.add( [
                userPacket.userid,
                userPacket.username,
                answerStr,
                awardedScore,
                btn
              ] ).draw( false );
          }
          renderedAnswers[i] = mainPacket;
        }
      }
    }
  }
}

function makeAnsweredCounts() {
  if(seenGameData.connectedusers != null) {
    var totalTeams = Object.keys(seenGameData.connectedusers).length;
    var totalAnswered = 0;

    if(currentRound != null && currentQuestion != null) {
      var look = currentRound.roundid + "-" + currentQuestion.questionid;
      if(teamsAnsweredData[look] != null) {
        totalAnswered = Object.keys(teamsAnsweredData[look]).length;
        for(var userid in teamsAnsweredData[look]) {
          var badgeClass = "badge-warning"
          if(teamsAnsweredData[look][userid].data.correctanswers != null) {
            if(teamsAnsweredData[look][userid].data.correctanswers.length > 0) {
              badgeClass = "badge-success";
            }
          }
          $("#mc-teamsanswered-" + userid + "-status").html("Answered").removeClass("badge-secondary").removeClass("badge-success").removeClass("badge-warning").addClass(badgeClass);
        }
      }
    }

    var sum = totalTeams-totalAnswered;
    $("#mc-teamsanswered-count").html((totalTeams <= totalAnswered ? totalTeams : totalAnswered));
    $("#mc-teamsanswered-teamtotal").html(totalTeams);
    $("#mc-teamsanswered-remain").html((sum < 0 ? 0 : sum));
  }
}

function mc_adjustanswer(teamid,roundid,questionid,score) {
  sendToServer({"type": "game-event", "command": "update-answer-score", "data": {"roundid": roundid, "questionid": questionid, "score": score}, "user": {"userid": teamid}});
}

/*function addAnswersToQuestionInfo(packet, roundid, questionid, notFromTeamAnswered) {
  if(typeof notFromTeamAnswered === "undefined") {
    notFromTeamAnswered = true;
  }
  var look = roundid + '-' + questionid;
  if(answeredDataFromServer[look] != null) {
    packet = $.extend(true, packet, answeredDataFromServer[look]);
  }
  answeredDataFromServer[look] = packet;

  if(currentInspectedRoundId > 0 && currentInspectedQuestionId > 0) {
    if(roundid != currentInspectedRoundId || questionid != currentInspectedQuestionId) {
      console.log("CURRENTINSPECTED");
      console.log(roundid + " / " + currentInspectedRoundId);
      console.log(questionid + " / " + currentInspectedQuestionId);
      return false;
    }
  } else {
    if(currentRound != null && currentQuestion != null) {
      if(roundid != currentRound.roundid || questionid != currentQuestion.questionid) {
        console.log("CURRENTDATA");
        console.log(roundid + " / " + currentRound.roundid);
        console.log(questionid + " / " + currentQuestion.questionid);
        return false;
      }
    }
  }

  if(roundid != null && questionid != null) {
    for(var userid in packet) {
      if(packet[userid][roundid] != null) {
        if(packet[userid][roundid][questionid] != null) {
          if(seenGameData.allusers != null) {
            if(seenGameData.allusers[userid] != null) {
              var user = seenGameData.allusers[userid];
              var data = packet[userid][roundid][questionid]; 
              if(data.awardedscore != null || data.amendedscore != null) {
                var awardedScore = 0;
                if(data.awardedscore != null) {
                  if(data.awardedscore > 0) {
                    awardedScore = data.awardedscore;
                  }
                }
                if(data.amendedscore != null) {
                  if(data.amendedscore > 0) {
                    awardedScore = data.amendedscore;
                  }
                }
                var answerStr = "";
                var btn = "";
                if(data.incorrectanswers != null) {
                  if(data.incorrectanswers.length > 0) {
                    answerStr = data.incorrectanswers.join(",");
                    btn = '<button class="btn btn-md btn-success" onClick="mc_acceptanswer(' + userid + ', ' + roundid + ', ' + questionid + ');$(this).remove();return false;">Accept Answer</button>';
                  }
                }
                if(data.correctanswers != null) {
                  if(data.correctanswers.length > 0) {
                    answerStr = data.correctanswers.join(",");
                    btn = '<i>None</i>';
                  }
                }

                var container = $("#mc-questionanswers tr[data-teamid='" + userid + "']");
                if(container.length) {
                  questionAnswersTable.row("#mc-questionanswers tr[data-teamid='" + userid + "']").remove().draw(false);
                }
                questionAnswersTable.row.add( [
                    userid,
                    user.username,
                    answerStr,
                    awardedScore,
                    btn
                  ] ).draw( false );
              }
            }
          }
        }
      }
    }
  }
}*/

function mc_questiontimer_send() {
  questionTimerVal = parseInt($("#mc-questiontimer").val());
  if(questionTimerVal > 0) {
    
    return {"type": "game-event", "command": "timer", "data": {"time": questionTimerVal, "action": afterTimerAction}};
  }
}

function initialiseTimer(secs) {
  clearTimeout(questionTimerCountdown);
  
  if(questionTimerCountdownVal >= 0) {
    $("#mc-questiontimer").val(questionTimerCountdownVal);
  }
  
  if(questionTimerCountdownVal >= 0) {
    questionTimerCountdownVal -= 1;
    if(!timerRunning) { return false; }
    questionTimerCountdown = setTimeout(initialiseTimer, 1000);
  } else {
    clearTimeout(questionTimerCountdown);
    $("#mc-questiontimer").val(questionTimerVal);
  }
}

function mc_questiontimer_send_cb(packetdata) {
  if(packetdata.currentdata.timerrunning != null) {
    timerRunning = true;
    questionTimerCountdownVal = parseInt(packetdata.currentdata.timerrunning)-1;
    questionTimerCountdown = setTimeout(initialiseTimer, 1000);
  } else {
    timerRunning = false;
    seenGameData.timerrunning = null;
    resetControlButtons(seenGameData);
  }
}

function mc_questiontimer_cancel_cb() {
  timerRunning = false;
}

function resetControlButtons(packetdata) { 
  if(packetdata.currentdata == null) {
    packetdata.currentdata = packetdata;
  }
  
  var isTimerRunning = false;
  if(packetdata.currentdata.timerrunning != null) {
    isTimerRunning = true;
  }
  
  $("[data-disable]").each(function() {
    var rules = JSON.parse($(this).attr("data-disable"));
    var shouldDisable = false;
    for(var k in rules) {
      if(packetdata.currentdata[k] != null) {
        if(rules[k] == true) {
          shouldDisable = true;
        }
      } else {
        if(rules[k] == false) {
          shouldDisable = true;
        }
      }
    }
    $(this).prop("disabled", shouldDisable);
  });
}

function checkInputChanges() {
  var obj = new Object();
  var inputs = $("#roundModifierContainer input");
  inputs.each(function() {
    var val = $(this).val();
    if($(this).attr("type") == "checkbox") {
      val = $(this).prop("checked");
    }
    obj[$(this).attr("id")] = val;
  });
  if((Object.keys(scoreOverride).length > 0 && JSON.stringify(obj) != JSON.stringify(scoreOverride)) || (Object.keys(scoreOverride).length == 0 && JSON.stringify(obj) != JSON.stringify(getCompareRoundScores()))) {
    $("#mc-scoreoverride-send").show();
    $("#mc-scoreoverride-reset").show();
  } else {
    $("#mc-scoreoverride-send").hide();
    if(Object.keys(scoreOverride).length > 0) {
      $("#mc-scoreoverride-reset").show();
    } else {
      $("#mc-scoreoverride-reset").hide();
    }
  }
}

function attachInputListeners() {
  $("#roundModifierContainer input").on('change keyup', function() {
    changeCount++;
    if(changeCount <= $("#roundModifierContainer input").length+1) {
      return false;
    }
    setTimeout(checkInputChanges, 100);
  });
}

function changeQuestionTimer(option) {
  if(typeof option === "undefiend") {
    option = null;
  }
  
  if(option != null) {
    $(".input-group-append li").removeClass("active");
    $(".input-group-append li a").removeClass("text-white");
    $("#mc-questiontimer-opt" + option).addClass("active");
    $("#mc-questiontimer-opt" + option + " a").addClass("text-white");
    afterTimerAction = option;
  }
  
  var optionSelected = $(".input-group-append li.active a");
  var timerMsg = $("#mc-questiontimer").val();
  timerMsg += " seconds. After countdown finishes: ";
  if(optionSelected.length) {
    timerMsg += optionSelected.html();
  } else {
    timerMsg += "Clear Current Question";
  }
  timerMsg += ".";
  
  $("#mc-questiontimer-summary").val(timerMsg);
}


function updateSeenQuestions() {
  var roundlistupdate = false;
  var questionlistupdate = false;
  for(var k in seenQuestions) {
    var split = k.split("-");
    var roundid = parseInt(split[0]);
    var questionid = parseInt(split[1]);
    
    var ele = $("#mc-roundlist option[value='" + roundid + "']");
    if(ele.length) {
      if(ele.html().substr(0, 4) != '&gt;') {
        ele.html('&gt; ' + ele.html());
        roundlistupdate = true;
      }
    }
    
    if(currentRound != null && roundid == currentRound.roundid) {
      var ele = $("#mc-questionlist option[value='" + questionid + "']");
      if(ele.length) {
        if(ele.html().substr(0, 4) != '&gt;') {
          ele.html('&gt; ' + ele.html());
          questionlistupdate = true;
        }
      }
    }
    var ele = $("#mc-quiztimeline-" + k);
    if(ele.length) {
      if(!ele.hasClass('text-cyan')) {
        ele.addClass('text-cyan');
      }
    }
  }
  if(roundlistupdate) {
    $("#mc-roundlist").select2('destroy').select2({theme: "bootstrap4"});
  }
  if(questionlistupdate) { 
    $("#mc-questionlist").select2('destroy').select2({theme: "bootstrap4"});
  }
}

$(document).ready(function() {
  $("#mc-questiontimer").on('change keyup', function() {
    changeQuestionTimer();
  });
  $("#round-mod-auto-scorer").on('change', function(){
    if ($(this).prop("checked")) {
      $("#roundModifierContainer input[type!=\"checkbox\"],#roundModifierContainer select,#roundModifierContainer button").prop("disabled", false);
    } else {
      $("#roundModifierContainer input[type!=\"checkbox\"],#roundModifierContainer select,#roundModifierContainer button").prop("disabled", true);
    }
  });
  $("#mc-questioninfo-selector").on('change', function() {
    var parts = ($(this).val()).toString().split("-");
    if(currentInspectedLoaded) {
      currentInspectedRoundId = parseInt(parts[0]);
      currentInspectedQuestionId = parseInt(parts[1]);
      if(currentRound != null && currentQuestion != null) {
        if(currentRound.roundid == currentInspectedRoundId && currentInspectedQuestionId == currentQuestion.questionid) {
          currentInspectedRoundId = 0;
          currentInspectedQuestionId = 0;
        }
      }
    } else {
      currentInspectedLoaded = true;
    }
    renderedAnswers = new Object();
    renderQuestionInformation(parts[0], parts[1]);
  });
  $("button").click(function(e) {
    if($(this).attr("id") != "") {
      e.preventDefault();
      if($(this).prop("disabled") == "true") {
        return false;
      }

      var controls  = $("#"+$(this).data("controls"));
      var label     = $(this).data("label");
      var extradata = $("#"+$(this).data("extradata")+" option:selected");
      if(extradata.length) {
        label += ": " + decodeURI(extradata.data('label'));
      } else {
        extradata = $("#"+$(this).data("extradata"));
        if(extradata.length) {
          label += ": " + extradata.val();
        }
      }

      var func = $(this).attr("id");
      if(!(typeof func === "undefined")) {
        func = func.toString();
        func = func.replace(/-/g, "_");
        var fn = window[func];
        var packet;
        if(typeof fn === "function") {
          packet = fn();
        }

        if(packet != null) {
          var cb = window[func + "_cb"];
          if(typeof cb === "function") {
            packet.callback = func + "_cb";
          }
          if($("#mc-auto-confirm-commands").prop("checked")) {
            sendToServer(packet);
          } else {
            if(controls.length) {
              $("#sendtosite-control").slideDown().promise().done(function() {
                $(controls).find("td").html(label);
                $(controls).fadeIn();
              });
              quizPackets.push(packet);
            }
          }
        }
      }
    }
  });
  attachInputListeners();
  toggleRoundSettings();
});