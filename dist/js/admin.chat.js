var adminCmdPacket   = {};
var adminCmdMsgId    = 0;
var adminCmdUsername = "";
var adminCmdUserId   = 0;

function changeAdminChatCmds(val) {
  if(typeof val === "undefined") {
    return false;
  }
  
  var showInputField = true;
  var inputFieldPlaceholder = "";
  var inputFieldValue = "";
  var changeValue = "";
  
  switch(val) {
    case 'delete': adminCmdPacket = {"type": "delete-message", "data": {"msgid": adminCmdMsgId, "userid": adminCmdUserId}}; showInputField = false; break;
    case 'removephoto': adminCmdPacket = {"type": "change-user", "user": {"userid": adminCmdUserId, "siteid": seenGameData.siteid, "avatar": defaultAvatar}}; showInputField = false; break;
    case 'changeuser': adminCmdPacket = {"type": "change-user", "user": {"userid": adminCmdUserId, "siteid": seenGameData.siteid}}; changeValue = "username"; inputFieldPlaceholder = "New name"; inputFieldValue = adminCmdUsername; break;
    case 'ban': adminCmdPacket = {"type": "change-user", "user": {"userid": adminCmdUserId, "siteid": seenGameData.siteid, "banned": true}}; changeValue = "banreason"; inputFieldPlaceholder = "Ban reason"; break;
    case 'mute': adminCmdPacket = {"type": "change-user", "user": {"userid": adminCmdUserId, "siteid": seenGameData.siteid, "muted": true}}; changeValue = "mutereason"; inputFieldPlaceholder = "Mute reason"; break;
    case 'unmute': adminCmdPacket = {"type": "change-user", "user": {"userid": adminCmdUserId, "siteid": seenGameData.siteid, "muted": false}}; showInputField = false; break;
    case 'unban': adminCmdPacket = {"type": "change-user", "user": {"userid": adminCmdUserId, "siteid": seenGameData.siteid, "banned": false}}; showInputField = false; break;
  }
  
  $("#admin-chat-modal .modal-footer").fadeIn();
  if(showInputField) {
    $("#admin-chat-modal .modal-footer input").prop("placeholder", inputFieldPlaceholder).val(inputFieldValue).fadeIn().change(function() {
      if(val != "changeuser") {
        adminCmdPacket.data[changeValue] = $(this).val();
      } else {
        adminCmdPacket.user[changeValue] = $(this).val();
      }
    });
  } else {
    $("#admin-chat-modal .modal-footer input").fadeOut();
  }
}

function adminChatOption(val, label) {
  if(typeof val === "undefined") {
    return false;
  }
  if(typeof label === "undefined") {
    label = val.toString();
  }
  return '<div class="form-check"><input type="radio" class="form-check-input" name="adminChatOpt" value="' + val + '" id="adminChatOpt-' + val + '"><label class="form-check-label" for="adminChatOpt-' + val + '">' + label + '</label></div>';
}

function adminChatCmds(msgid, seluserid, selusername) {
  if(typeof msgid === "undefined" || typeof seluserid === "undefined" || typeof selusername === "undefined") {
    return false;
  }
  
  adminCmdMsgId = msgid;
  adminCmdUsername = selusername;
  adminCmdUserId = seluserid;
  
  var msg = $("div[data-msgid='" + msgid + "']").text();
  var outHTML = '<strong>Message:</strong> <i>' + msg + '</i><br /><br /><div class="form-group">' + adminChatOption("delete", "Delete This Message") + adminChatOption("changeuser", "Change Username") + adminChatOption("removephoto", "Remove Profile Picture");
  if(userid != adminCmdUserId) {
    if(seenGameData.allusers[seluserid].muted != null) {
      if(seenGameData.allusers[seluserid].muted) {
        outHTML += adminChatOption("unmute", "Un-Mute User");
      } else {
        outHTML += adminChatOption("mute", "Mute User - user will remain muted until an admin unmutes them.");    
      }
    } else {
      outHTML += adminChatOption("mute", "Mute User - user will remain muted until an admin unmutes them.");
    }
    if(seenGameData.allusers[seluserid].banned != null) {
      if(seenGameData.allusers[seluserid].banned) {
        outHTML += adminChatOption("unban", "Un-Ban User");
      } else {
        outHTML += adminChatOption("ban", "Ban User - will ban the user from this game, permanently.");
      }
    } else {
      outHTML += adminChatOption("ban", "Ban User - will ban the user from this game, permanently.");
    }
  }
  outHTML += '</div>';
  
  $("#admin-chat-modal").modal('show');
  $("#admin-chat-modal .modal-header h4").html("Chat Manager: " + adminCmdUsername);
  $("#admin-chat-modal .modal-body").html(outHTML);
  $("#admin-chat-modal .modal-footer").hide();
  
  $("#admin-chat-modal input[type='radio']").on('click change', function() {
    changeAdminChatCmds($(this).val());
  });
  $("#admin-chat-modal .modal-footer button").click(function() {
    quizPackets.push(adminCmdPacket);
    console.log(adminCmdPacket);
    sendToServer();
  });
}