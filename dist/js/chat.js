var isSendingMessage = false;
var lastChatMsgObj   = new Object();
var lastSentMsg      = "";
var notifyNewChats   = true;
var newMessageCount  = 0;
var seenChatMsgs     = new Array();
var sfxChat = new Audio('/siteassets/chat-message.mp3');
sfxChat.autoplay = false;
sfxChat.loop     = false;
sfxChat.muted    = true;
sfxChat.volume   = 0.6;

function addPlayerToChat(packet) {
  var container = $("#qc-contactlist li[data-teamid='" + packet.userid + "']");

  var username = packet.username;
  var avatarurl = packet.avatarurl;

  var usernameSection = username;
  var list = ".player-list";
  if(packet.banned != null || packet.muted != null) {
    usernameSection += '<small class="contacts-list-date float-right btn-group">';
    if(packet.muted != null && packet.muted) {
      usernameSection += '<label class="btn btn-sm btn-warning">MUTED</label>';
    }
    if(packet.banned != null && packet.banned) {
      usernameSection += '<label class="btn btn-sm btn-danger">BANNED</label>';
    }
    if(packet.player != null) {
      if(packet.player) {
        usernameSection += '<label class="btn btn-sm btn-info">PLAYER</label>';
      } else {
        usernameSection += '<label class="btn btn-sm btn-primary">QUIZMASTER</label>';
        list = '.admin-list';
      }
    }
    usernameSection += '</small>';
  }
  var onlineStatus = (packet.online != null && packet.online ? 'Online' : 'Offline');
  $("[data-teamid='" + packet.userid + "'] [data-fieldname='username']").html(username);
  $("[data-teamid='" + packet.userid + "'] [data-fieldname='avatar']").attr("src", "/siteassets/user-images/" + avatarurl);
  if(container.length) {
    container.find(".contacts-list-img").attr("src", '/siteassets/user-images/' + avatarurl);
    container.find(".contacts-list-name").html(usernameSection);
    container.find(".contacts-list-online").html(onlineStatus);
  } else {
    $("#qc-contactlist " + list).append('<li data-teamid="' + packet.userid + '"><a href="" onClick="return false;"><img class="contacts-list-img" data-fieldname="avatar" src="/siteassets/user-images/' + avatarurl + '"><div class="contacts-list-info"><span class="contacts-list-name" data-fieldname="username">' + usernameSection + '</span><span class="contacts-list-online">' + onlineStatus + '</span></div><!-- /.contacts-list-info --></a></li>');

  }
}

function receiveMessage(packet) {  
  if(packet.data != null) {
    packet = packet.data;
  }

  if(seenChatMsgs.indexOf(packet.chatid) >= 0) {
    return false;
  }

  seenChatMsgs.push(packet.chatid);
  
  var date = new Date(packet.time);
  var hours = convert12Hrs(date.getHours());
  
  var sameUserAndTime = false;
  if(lastChatMsgObj.userid != null && lastChatMsgObj.userid == packet.userid && lastChatMsgObj.time != null) {
    var lastDate = new Date(lastChatMsgObj.time);
    lastDate.setMinutes(lastDate.getMinutes()+2);
    
    if(lastDate >= date) {
      sameUserAndTime = true;
    }
  }
  
  var userData = seenGameData.allusers[packet.userid];
  var username = userData.username;
  var avatarurl = userData.avatarurl;
  
  var dateStr = date.getDate() + " " + monthToDesc(date.getMonth()) + " / " + hours.hour + ":" + date.getMinutes() + hours.ampm;
  packet.avatar = "";
  var msgClass = "";
  var isSelf = false;
  if(packet.userid == userid) {
    msgClass = " right";
    lastSentMsg = packet.message;
    isSelf = true;
    $("#mc-chatmessagebox").val("");
    setTimeout(function() {
      isSendingMessage = false;
    }, 2000);
  }
  
  if(sameUserAndTime) {
    var eles = $("#mc-chatbox .direct-chat-msg[data-teamid='" + packet.userid + "']");
    if(eles.length <= 0) {
      sameUserAndTime = false;
    } else {
      var eletwo = eles.last().find(".direct-chat-text");
      if(eletwo.length <= 0) {
        sameUserAndTime = false;
      } else {
        var lastDate = new Date(eletwo.first().data("msgtime"));
        var thisDate = new Date(packet.time);
        lastDate.setMinutes(lastDate.getMinutes()+2);
        if(lastDate >= thisDate) {
          eles.last().append('<div class="direct-chat-text" data-msgid="' + packet.chatid + '" data-msgtime="' + packet.time + '">' + packet.message + '</div>');
        } else {
          sameUserAndTime = false;
        }
      }
    }
  }
  
  if(!sameUserAndTime) {
    var html = '<div class="direct-chat-msg'+msgClass+'" data-teamid="' + packet.userid + '"><div class="direct-chat-infos clearfix"><span class="direct-chat-name float-left" data-fieldname="username">' + username + '</span><span class="direct-chat-timestamp float-right">' + dateStr + '</span></div><!-- /.direct-chat-infos --><img class="direct-chat-img" data-fieldname="avatar" src="/siteassets/user-images/' + avatarurl + '" alt="' + username + '"><!-- /.direct-chat-img --><div class="direct-chat-text" data-msgid="' + packet.chatid + '" data-msgtime="' + packet.time + '">' + packet.message + '</div><!-- /.direct-chat-text --></div><!-- /.direct-chat-msg -->';
    $("#mc-chatbox").append(html);
  }
  
  lastChatMsgObj = packet;
  $("#mc-chatbox").scrollTop($("#mc-chatbox").prop("scrollHeight"));
  $("#mc-chatbox .direct-chat-text").click(function() { if(typeof window['adminChatCmds'] === "function") {adminChatCmds($(this).data("msgid"), $(this).parent().data("teamid"), $(this).parent().find(".direct-chat-name").text()); } });
  if(!isSelf) {
    sfxChat.pause();
    sfxChat.currentTime = 0;

    if(notifyNewChats) {
      newMessageCount++;
      var title = newMessageCount.toString() + " new message" + (newMessageCount != 1 ? 's' : '');
      $(".direct-chat .card-tools span").html(newMessageCount.toString()).attr("title", title).fadeIn();
      $(".direct-chat .card-tools span").tooltip('dispose').tooltip();
    }

    setTimeout(function() {
      //sfxChat.play();
      //sfxChat.muted = false;
    }, 10);
  }
}

function convert12Hrs(hours24) {
  var ampm = "am";
  var hours = parseInt(hours24);
  
  if(hours == 0) {
    hours = "12";
  } else if(hours > 12) {
    hours = hours-12;
    ampm = "pm";
  }
  
  return {"hour": hours, "ampm": ampm};
}

function monthToDesc(month) {
  month = parseInt(month);
  if(month == 0) { return "Jan"; }
  if(month == 1) { return "Feb"; }
  if(month == 2) { return "Mar"; }
  if(month == 3) { return "Apr"; }
  if(month == 4) { return "May"; }
  if(month == 5) { return "Jun"; }
  if(month == 6) { return "Jul"; }
  if(month == 7) { return "Aug"; }
  if(month == 8) { return "Sep"; }
  if(month == 9) { return "Oct"; }
  if(month == 10) { return "Nov"; }
  if(month == 11) { return "Nov"; }
}

function sendMessage() {
  if(isSendingMessage) { return false; }
  isSendingMessage = true;
  
  var val = $("#mc-chatmessagebox").val();
  val = val.replace((!/^[\w\-\s]+$/), "");
  
  if(lastSentMsg == val) {
    return false;
  }
  if(val.length > 0) {  
    var json = {"type": "chat-message", "data": val, "callback": "sendmessage_cb"};
    quizPackets.push(json);
    sendToServer();
  }
}

function removeMessage(packetdata) {
  console.log(packetdata);
  if(packetdata.chatid != null && packetdata.userid != null) {
    var container = $("#mc-chatbox .direct-chat-msg[data-teamid='" + packetdata.userid + "']").find(".direct-chat-text[data-msgid='" + packetdata.chatid + "']");
    if(container.parent().find(".direct-chat-text").length == 1) {
      container.parent().remove();
    } else {
      container.remove();
    }
  }
}

$(document).ready(function() {
  $("#mc-sendchatmessage").on('click', function() {
    sendMessage();
  });

  $("#mc-chatmessagebox").on('keydown', function(event) {
    if(event.which == 13) 
      sendMessage();
  });
  
  $('[data-toggle="tooltip"]').tooltip();

  $(".direct-chat").on('click focus', function() {
    $(".direct-chat .card-tools span").fadeOut();
    notifyNewChats = false;
  });
  $(".direct-chat").on('focusout', function() {
    newMessageCount = 0;
    notifyNewChats = true;
  });
})