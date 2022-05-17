<?
function renderChatBox() {
  global $headerScripts, $footerScripts, $quizSite, $auth, $quizUser;
  
  $footerScripts .= '<script>var userid=' . $auth->getUserId() . ';var defaultAvatar = "' . DEFAULT_AVATAR . '";</script><script src="/dist/js/chat' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js?v=' . time() . '"></script>';
  if($quizUser->isAdmin() || $quizUser->isQuizMaster()) {
    $footerScripts .= '<script src="/dist/js/admin.chat' . ($quizSite->getData('debug') != "true" ? ".min" : "") . '.js?v=' . time() . '"></script>
      <div class="modal fade" id="admin-chat-modal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title"></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer justify-content-between" style="display:none;">
              <div class="col-8">
                <input type="text" id="admin-chat-reason-input" class="form-control" style="display:none;">
              </div>
              <div class="col-3 text-right">
                <button type="button" class="btn btn-primary">Submit</button>
              </div>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->
      ';
  }
  

  /*
  CARD TOOLS:
  
  CONTACTS LIST AFTER DIRECT-CHAT-MESSAGES:
  */
  echo '
            <!-- DIRECT CHAT PRIMARY -->
            <div class="card card-prirary cardutline direct-chat direct-chat-primary">
              <div class="card-header">
                <h3 class="card-title">' . $quizSite->getData('sitename') . ' Chat</h3>

                <div class="card-tools">
                  <span data-toggle="tooltip" title="" class="badge bg-primary" style="display:none;"></span>
                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-toggle="tooltip" title="Users"
                          data-widget="chat-pane-toggle">
                    <i class="fas fa-users"></i></button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <!-- Conversations are loaded here -->
                <div class="direct-chat-messages" id="mc-chatbox">
                </div>
                <!--/.direct-chat-messages-->
                <div class="direct-chat-contacts" style="right:0;" id="qc-contactlist">
                  <ul class="contacts-list admin-list mb-0" style="border-bottom: 1px solid rgba(0,0,0,.2);">
                  </ul>
                  <ul class="contacts-list player-list">
                  </ul>
                  <!-- /.contatcts-list -->
                </div>
                <!-- /.direct-chat-pane -->
              </div>
              <!-- /.card-body -->
              <div class="card-footer">
                <div class="input-group">
                  <input type="text" name="message" placeholder="Type Message ..." class="form-control" id="mc-chatmessagebox">
                  <span class="input-group-append">
                    <button type="submit" class="btn btn-primary" id="mc-sendchatmessage">Send</button>
                  </span>
                </div>
              </div>
              <!-- /.card-footer-->
            </div>
            <!--/.direct-chat -->';
}
?>