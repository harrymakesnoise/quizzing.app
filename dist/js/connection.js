var quizServer    = new Object();
var quizPackets   = new Array();
var awaitResponse = new Array();
var initialLoaded = false;
var currentQuestion = new Object();
var currentRound = new Object();
var allScores = new Object();
var lastLeaderboard = new Object();
var allScoresArray = new Array();
var roundData = new Object();
var lastConnectedUsers = new Object();
var seenGameData = new Object();
var seenQuestions = new Object();
var answeredQuestions = new Object();
var userScoreUpdates = new Array();
var shouldReconnect = true;
var sfxQuestion = new Audio('/siteassets/question-change.mp3');
var hasAuthed = false;
var leaderboardUpdating = false;
var reconnectTimer;
var pingPong;

sfxQuestion.autoplay = false;
sfxQuestion.muted    = true;
sfxQuestion.loop     = false;
sfxQuestion.volume   = 0.6;

function getFullGameData(packet) {
  if(packet.currentdata != null) {
    var newPacket = seenGameData;
    for(var k in packet.currentdata) {
      newPacket[k] = packet.currentdata[k];
    }
    packet.currentdata = newPacket;
  }
  if(packet.gameupdate != null) {
    for(var k in packet.gameupdate) {
      packet.currentdata[k] = packet.gameupdate[k];
    }
  }
  return packet;
}

function ping_cb(packet) {
  var pingReceived = new Date();
  var pingSent = new Date(packet.date);

  var diff = pingReceived.getTime() - pingSent.getTime();
  $("#mc-ping-out").html("<b>Ping</b> " + diff + "ms. | ");
}

function ping(iv) {
	if(typeof iv === "undefined") {
		iv = 20000;
	}
  clearInterval(pingPong);

  pingPong = setInterval(function() {
    var pingSent = new Date();
    sendToServer({"type": "ping", "date": pingSent.toISOString()});
    ping();
  }, iv);
}

function connectToServer() {
  toastr.info('Connecting to Game');
  quizServer.server = new WebSocket("wss://" + window.location.hostname + "/websocket");
  //quizServer.server = new WebSocket("ws://127.0.0.1:8080");
  quizServer.server.onopen = function(e) {
    quizServer.server.send(JSON.stringify({"userAuth": userAuth, "gameid": gameid}));
    toastr.info('Authenticating with Game');
  };
  
  quizServer.server.onmessage = function(event) {
    var jsonPacket = JSON.parse(event.data);
    if(jsonPacket.request != null) {
      for(var i=awaitResponse.length-1;i>=0;i--) {
        var packet = awaitResponse[i];
        if(packet.toString() == jsonPacket.request.toString()) {
          awaitResponse.splice(i, 1);
        }
      }
    }
    updateServerStatus();
    if(jsonPacket.type != null) {
      if(jsonPacket.type == "ping") {
        ping_cb(jsonPacket);
        return false;
      }
      if(jsonPacket.type == "user-change") {
        if(jsonPacket.user.username != null) {
            $("[data-teamid='" + jsonPacket.user.userid + "'] [data-fieldname='username']").html(jsonPacket.user.username);
        }
        if(jsonPacket.user.avatarurl != null) {
            $("[data-teamid='" + jsonPacket.user.userid + "'] [data-fieldname='avatar']").attr("src", "/siteassets/user-images/" + jsonPacket.user.avatarurl);
        }
        return false;
      }
    }
    if(jsonPacket.currentdata != null) {
      seenGameData = jsonPacket.currentdata;
      if(!hasAuthed) {
        hasAuthed = true;
        toastr.success('Logged into Game, enjoy!');
        if(quizPackets.length > 0) {
          sendToServer();
        }
        ping(1);
      }
    }
    if(jsonPacket.gameupdate != null) {
      jsonPacket.currentdata = jsonPacket.gameupdate;
    }
    if(jsonPacket.gametimeline != null) {
      parseGameTimeline(jsonPacket.gametimeline);
    }
    if(jsonPacket.answeredquestions != null) {
      answeredQuestions = jsonPacket.answeredquestions;
    }
    if(jsonPacket.currentdata != null) {
      if(jsonPacket.currentdata.ended != null) {
        mc_gametoggle_stop_cb();
      }
      if(jsonPacket.currentdata.started != null) {
        var localPacket = getFullGameData(jsonPacket);
        mc_gametoggle_start_cb(localPacket);
        if(localPacket.currentdata.started != null) {
          if($("#mc-player-waiting").css("display") != "none") {
            $("#mc-player-waiting").slideUp().promise().done(function() {
              $("#mc-player-gamearea").slideDown();
            });
          } else {
            $("#mc-player-gamearea").slideDown();
          }
        } else {
          $("#mc-player-gamearea").hide();
          $("#mc-player-waiting").slideDown();
        }
      }
      if(jsonPacket.currentdata.chatbox != null && typeof window["receiveMessage"] === "function") {
        for(var i=0;i<jsonPacket.currentdata.chatbox.length;i++) {
          receiveMessage(jsonPacket.currentdata.chatbox[i]);
        }
      }
      if('round' in jsonPacket.currentdata) {
        currentRound = jsonPacket.currentdata.round;
      }
      if('question' in jsonPacket.currentdata) {
        currentQuestion = jsonPacket.currentdata.question;
      }
      if('round' in jsonPacket.currentdata) {
        var localPacket = getFullGameData(jsonPacket);
        if(jsonPacket.currentdata.round != null) {
          mc_roundtoggle_start_cb(localPacket);
          showRoundAlert('round', localPacket.currentdata.round.roundid);
        } else {
          mc_roundtoggle_stop_cb();
        }
      }
      if('question' in jsonPacket.currentdata) {
        if(currentRound != null) {
          if(jsonPacket.currentdata.question != null) {
            mc_questiontoggle_send_cb(jsonPacket);
            var localPacket = getFullGameData(jsonPacket);
            showRoundAlert('question', localPacket.currentdata.round.roundid);
            //sfxQuestion.pause();
            //sfxQuestion.currentTime = 0;
            setTimeout(function() {
              //sfxQuestion.play();
              //sfxQuestion.muted = false;
            }, 10);
          } else {
            mc_questiontoggle_clear_cb(jsonPacket);
          }
        }
      }
      if('allusers' in jsonPacket.currentdata) {
        for(var k in jsonPacket.currentdata.allusers) {
          if(jsonPacket.currentdata.leaderboard != null) {
            if(jsonPacket.currentdata.leaderboard[jsonPacket.currentdata.allusers[k].userid] != null) {
              jsonPacket.currentdata.allusers[k].score = jsonPacket.currentdata.leaderboard[jsonPacket.currentdata.allusers[k].userid];
            }
          }

          if(jsonPacket.currentdata.allusers[k].player != null) {
            if(jsonPacket.currentdata.allusers[k].player) {
              addPlayerToGame(jsonPacket.currentdata.allusers[k]);
            }
          }
          addPlayerToChat(jsonPacket.currentdata.allusers[k]);
        }
      }
      if('connectedusers' in jsonPacket.currentdata) {
        applyPlayerScores();
      }
      if('seenquestions' in jsonPacket.currentdata && typeof window['updateSeenQuestions'] === "function") {
        seenQuestions = jsonPacket.currentdata.seenquestions;
        updateSeenQuestions();
      }
      if('teamsanswered' in jsonPacket.currentdata) {
        if(typeof window["mc_teamanswered_cb"] === "function") {
          mc_teamanswered_cb(jsonPacket.currentdata);
        }
      }
      if('timerrunning' in jsonPacket.currentdata) {
        if(typeof window['start_countdown'] === "function") {
          if(parseInt(jsonPacket.currentdata.timerrunning) > 0) {
            start_countdown(parseInt(jsonPacket.currentdata.timerrunning));
          } else {
            $("#countdown").fadeOut();
            clearInterval(countdownInterval);
          }
        }
      }
      if('userscore' in jsonPacket.currentdata) {
    		if(seenGameData.leaderboard != null) {
          var userid = jsonPacket.currentdata.userscore.userid;
          var newScore = jsonPacket.currentdata.userscore.score;

    			seenGameData.leaderboard[userid] = newScore;
    		}
        applyPlayerScores();
      }
      if('leaderboard' in jsonPacket.currentdata) {
        applyPlayerScores();
      }
      if(jsonPacket.request != null) {
        if(jsonPacket.request.callback != null) {
          var fn = window[jsonPacket.request.callback];
          if(typeof fn === "function") {
            fn(jsonPacket);
          }
        }
      }
      if(typeof window['resetControlButtons'] === "function") {
        var localPacket = getFullGameData(jsonPacket);
        resetControlButtons(localPacket);
        $("#mainControlWrapper .overlay").fadeOut();
      }
      if(typeof window['calcScoreOverrideAllowed'] === "function") {
        calcScoreOverrideAllowed();
      }
    }
    if(jsonPacket.type != null) {
      if(jsonPacket.type == "chat-message") {
        if(typeof window["receiveMessage"] === "function") {
          receiveMessage(jsonPacket);
        }
      }
      if(jsonPacket.type == "chat-remove") {
        if(typeof window["removeMessage"] === "function") {
          removeMessage(jsonPacket);
        }
      }
      if(jsonPacket.type == "answer-submit") {
        submitanswer_cb(jsonPacket);
      }
      if(jsonPacket.type == "error") {
        toastr.error(jsonPacket.message);
        if(jsonPacket.disconnect) {
          shouldReconnect = false;
          showErrorOverlay(jsonPacket.message);
          quizServer.server.close();
        }
        if(jsonPacket.request.type == "chat-message") {
          isSendingMessage = false;
        }
      }
      if(jsonPacket.type == "remove-player") {
        removePlayerFromGame(jsonPacket.userid);
      }
    }
  };

  quizServer.server.onclose = function(event) {
    clearInterval(pingPong);
    hasAuthed = false;
    if (event.wasClean) {
      console.log("[close] Connection closed cleanly, code=${event.code} reason=${event.reason}");
    } else {
      if(shouldReconnect) {
        clearTimeout(reconnectTimer);
        toastr.error("You're offline. Retrying to connect in 3 seconds."); 
        reconnectTimer = setTimeout(function() {
          connectToServer();
          sendToServer();
        }, 3000);
      } else {
        toastr.error("You have been disconnected."); 
      }
      console.log("[close] lost connetion?")
    }
  };
  quizServer.server.onerror = function(error) {

  };
}

function sendToServer(singleMsg) {
  if(typeof singleMsg === "undefined") {
    var singleMsg = new Object();
  }
  if(hasAuthed) {
  	if(quizServer.server.readyState == WebSocket.OPEN) {
	    if(Object.keys(singleMsg).length > 0) {
	      quizServer.server.send(JSON.stringify(singleMsg));
	      awaitResponse.push(singleMsg);
        if(singleMsg.type != null) {
          if(singleMsg.type != "ping") {
            $(".sendtosite-control-overlay").html('<i class="fas fa-2x fa-sync fa-spin"></i>').fadeIn();
          }
        }
	    } else {
	      for(var i=0;i<quizPackets.length;i++) {
	        awaitResponse.push(quizPackets[i]);
	        quizServer.server.send(JSON.stringify(quizPackets[i]));
	        quizPackets.splice(i, 1);
	      }
	      $(".sendtosite-control-overlay").html('<i class="fas fa-2x fa-sync fa-spin"></i>').fadeIn();
	    }
	  } else {
	    if(Object.keys(singleMsg).length > 0) {
	      quizPackets.push(singleMsg);
	    }
	    quizServer.server.close();
	    connectToServer();
	  }
	}
}

function updateServerStatus() {
  if(awaitResponse.length == 0) {
    syncFinished();
  } else {
    console.log("Awaiting " + awaitResponse.length + " packets");
  }
}

function syncFinished(delay) {
  if(typeof delay === "undefined") {
    delay = 1;
  }
  delay = delay*1000;
  quizPackets = new Array();
	$(".sendtosite-control-overlay").html('<i class="fas fa-2x fa-check"></i>');
  $("#admin-chat-modal").modal("hide");
  setTimeout(function() {
    $("#sendtosite-control").find("tr").each(function() {
      if($(this).attr("id")) {
        $(this).find("td").html("-");
        $(this).fadeOut();
      }
    });
    $(".sendtosite-control-overlay").fadeOut();
    $("#sendtosite-control").slideUp();
  }, delay);
}

function showErrorOverlay(message) {
  $("body").append('<div class="modal fade" id="modal-error" data-backdrop="static" data-keyboard="false"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title">Game Disconnected</h4></div><div class="modal-body"><p>' + message + '</p></div><div class="modal-footer justify-content-between"><button type="button" class="btn btn-primary" onClick=\'window.location="//' + window.location.hostname + '";window.location.href="//' + window.location.hostname + '";\'>Go Home</button></div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div><!-- /.modal -->');
  $("#modal-error").modal("show");
}

function isJson(item) {
  item = typeof item !== "string"
    ? JSON.stringify(item)
    : item;

  try {
    item = JSON.parse(item);
  } catch (e) {
    return false;
  }

  if (typeof item === "object" && item !== null) {
    return true;
  }

  return false;
}