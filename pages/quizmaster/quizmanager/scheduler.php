<?
$headerScripts .= '<!-- fullCalendar -->
  <link rel="stylesheet" href="/plugins/fullcalendar/main.min.css">
  <link rel="stylesheet" href="/plugins/fullcalendar-daygrid/main.min.css">
  <link rel="stylesheet" href="/plugins/fullcalendar-timegrid/main.min.css">
  <link rel="stylesheet" href="/plugins/fullcalendar-bootstrap/main.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="/plugins/toastr/toastr.min.css">';

$footerScripts .= '
<!-- jQuery UI -->
<script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Toastr -->
<script src="/plugins/toastr/toastr.min.js"></script>
<!-- fullCalendar 2.2.5 -->
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/fullcalendar/main.min.js"></script>
<script src="/plugins/fullcalendar-daygrid/main.min.js"></script>
<script src="/plugins/fullcalendar-timegrid/main.min.js"></script>
<script src="/plugins/fullcalendar-interaction/main.min.js"></script>
<script src="/plugins/fullcalendar-bootstrap/main.min.js"></script>
<!-- Page specific script -->
<script>
  $(function () {
    /* initialize the external events
     -----------------------------------------------------------------*/
    function ini_events(ele) {
      ele.each(function () {

        // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
        // it doesn\'t need to have a start or end
        var eventObject = {
          title: $.trim($(this).text()) // use the element\'s text as the event title
        }

        // store the Event Object in the DOM element so we can get to it later
        $(this).data(\'eventObject\', eventObject)

        // make the event draggable using jQuery UI
        $(this).draggable({
          zIndex        : 1070,
          revert        : true, // will cause the event to go back to its
          revertDuration: 0  //  original position after the drag
        })

      })
    }

    ini_events($(\'#external-events div.external-event\'))

    /* initialize the calendar
     -----------------------------------------------------------------*/
    //Date for the calendar events (dummy data)
    var date = new Date()
    var d    = date.getDate(),
        m    = date.getMonth(),
        y    = date.getFullYear()

    var Calendar = FullCalendar.Calendar;
    var Draggable = FullCalendarInteraction.Draggable;

    var containerEl = document.getElementById(\'external-events\');
    var calendarEl = document.getElementById(\'calendar\');

    // initialize the external events
    // -----------------------------------------------------------------

    new Draggable(containerEl, {
      itemSelector: \'.external-event\',
      eventData: function(eventEl) {
        return {
          title: eventEl.innerText,
          backgroundColor: window.getComputedStyle( eventEl ,null).getPropertyValue(\'background-color\'),
          borderColor: window.getComputedStyle( eventEl ,null).getPropertyValue(\'background-color\'),
          textColor: window.getComputedStyle( eventEl ,null).getPropertyValue(\'color\'),
          blockHTML: eventEl.outerHTML,
          quizId: eventEl.getAttribute("data-quizid"),
        };
      }
    });

    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ \'bootstrap\', \'interaction\', \'dayGrid\', \'timeGrid\' ],
      initialView: \'timeGridWeek\',
      allDaySlot: false,
      slotDuration: \'00:10:00\',
      slotLabelFormat: {
        hour: \'numeric\',
        minute: \'2-digit\',
        meridiem: \'short\',
      },
      header    : {
        left  : \'prev,next today\',
        center: \'title\',
        right : \'timeGridWeek,timeGridDay\'
      },
      events: {
        url: \'/api/schedule/get\',
        method: \'POST\',
        failure: function() {
          alert(\'there was an error while fetching events!\');
        },
      },
      defaultEventMinutes: 10,
      editable  : true,
      eventDurationEditable: false,
      droppable : true, // this allows things to be dropped onto the calendar !!!
      drop      : function(info) {
        info.draggedEl.parentNode.removeChild(info.draggedEl);
        updateQuizTime(info);
      },
      eventDrop: function(info) { updateQuizTime(info) },
      eventDragStop: function(info) {
        var event     = info.event;
        var jsEvent   = info.jsEvent;
        var blockHTML = event.extendedProps.blockHTML;

        var trashEl = $(\'.card\');
        var ofs = trashEl.offset();

        var x1 = ofs.left;
        var x2 = ofs.left + trashEl.outerWidth(true);
        var y1 = ofs.top;
        var y2 = ofs.top + trashEl.outerHeight(true);

        if (jsEvent.pageX >= x1 && jsEvent.pageX<= x2 &&
            jsEvent.pageY >= y1 && jsEvent.pageY <= y2) {
            event.remove();
            $("#external-events").html(blockHTML + $("#external-events").html());
            updateQuizTime(info, true);
        } else {
          //updateQuizTime(info);
        }
      },
    });
    calendar.changeView(\'timeGridWeek\');
    calendar.render();
  })
  function updateQuizTime(info, clear) {
    if(typeof clear === "undefined") {
      clear = false;
    }
    if(typeof info.event === "undefined") {
      var event    = info.draggedEl;
      var quizid   = event.getAttribute("data-quizid");
      var datetime = new Date(info.date).toISOString();
    } else {
      var event    = info.event;
      var quizid   = event.extendedProps.quizId;
      var datetime = new Date(event.start).toISOString();
    }
    console.log(quizid);
    console.log(datetime);

    var endpoint = clear ? "remove" : "update";

    $.post("/api/schedule/" + endpoint, {"quizid": quizid, "schedule_datetime": datetime})
    .done(function(resp) {
      if(resp.result) {
        toastr.success(\'Schedule updated successfully\');
      } else {
        toastr.error(resp.error);
      }
    })
    .fail(function(resp) {
      toastr.error(\'Server error occurred, please try again.\');
    });
  }
</script>';

$quizzes = new Quizzes();
$quizList = array();
//                        $skip=0, $length=10, $order='asc', $search='', $type='', $siteid=''
$allQuizData = $quizzes->get(0, 999, 'desc', '', 'unscheduled', $quizSite->siteid);


if(isset($allQuizData['data'])) {
  if(count($allQuizData['data']) > 0) {
    foreach($allQuizData['data'] as $quiz) {
      $quizList[] = array('id' => $quiz[0], 'label' => $quiz[1]);
    }
  }
}

renderSiteHeader();
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Scheduler</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="/">Home</a></li>
              <li class="breadcrumb-item"><a href="/quiz-manager">Quiz Manager</a></li>
              <li class="breadcrumb-item active">Scheduler</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">
            <div class="sticky-top mb-3">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Quizzes</h4>
                </div>
                <div class="card-body">
                  <!-- the events -->
                  <div id="external-events">
                    <?              
                    foreach($quizList as $quiz) {        
                      ?>
                      <div class="external-event bg-success" data-quizid="<?=$quiz['id']?>"><?=$quiz['label']?></div>
                      <?
                    }
                    ?>
                  </div>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card card-primary">
              <div class="card-body p-0">
                <!-- THE CALENDAR -->
                <div id="calendar"></div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?
renderSiteFooter();
?>