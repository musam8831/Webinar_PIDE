<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$user = $_SESSION['user'];
?>
<!doctype html><html><head><meta charset="utf-8"><title>Webinar Calendar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<link href="../assets/css/styles.css" rel="stylesheet">
</head><body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container">
  <div class="card card-body shadow-sm mb-3">
    <div id="calendar"></div>
  </div>
</div>

<!-- Modal for add/edit webinar -->
<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="eventForm">
      <div class="modal-header">
        <h5 class="modal-title">Add Webinar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Title</label>
          <input class="form-control" name="title" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Start</label>
          <input class="form-control" name="start" type="datetime-local" required>
        </div>
        <div class="mb-2">
          <label class="form-label">End</label>
          <input class="form-control" name="end" type="datetime-local" required>
        </div>
        <div class="form-text">Overlapping webinars are not allowed.</div>
        <div id="formError" class="text-danger mt-2"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Save</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="viewTitle"></h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="viewBody"></div>
      <div class="modal-footer">
        <button id="deleteBtn" class="btn btn-danger">Delete</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
const currentUser = <?= json_encode($user) ?>;

document.addEventListener('DOMContentLoaded', function(){
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
    selectable: true,
    selectMirror: true,
    select: function(info){
      const m = new bootstrap.Modal(document.getElementById('eventModal'));
      const f = document.getElementById('eventForm');
      f.reset();
      f.title.value='';
      f.start.value = info.startStr.substring(0,16);
      f.end.value = info.endStr.substring(0,16);
      document.getElementById('formError').innerText='';
      m.show();

      f.onsubmit = (e)=>{
        e.preventDefault();
        fetch('save_event.php', {
          method: 'POST',
          headers: {'Content-Type':'application/json'},
          body: JSON.stringify({ title: f.title.value, start: f.start.value, end: f.end.value })
        }).then(r=>r.json()).then(res=>{
          if (res.error) { document.getElementById('formError').innerText = res.error; }
          else { m.hide(); calendar.refetchEvents(); }
        });
      };
    },
    eventMouseEnter: function(info){
      const ev = info.event;
      const tooltip = document.createElement('div');
      tooltip.className = 'event-tooltip';
      tooltip.innerHTML = '<b>'+ev.title+'</b><br>'+new Date(ev.start).toLocaleString()+' - '+new Date(ev.end).toLocaleString();
      document.body.appendChild(tooltip);
      const rect = info.el.getBoundingClientRect();
      tooltip.style.left = (rect.left + window.scrollX) + 'px';
      tooltip.style.top = (rect.top + window.scrollY - 10) + 'px';
      info.el.addEventListener('mouseleave', ()=> tooltip.remove(), {once:true});
    },
    eventClick: function(info){
      fetch('get_event.php?id=' + info.event.id).then(r=>r.json()).then(data=>{
        if (data.error) { alert(data.error); return; }
        document.getElementById('viewTitle').innerText = data.title;
        document.getElementById('viewBody').innerHTML = `
          <p><b>Initiator:</b> ${data.initiator_name} (${data.initiator_email})</p>
          <p><b>Start:</b> ${data.start}</p>
          <p><b>End:</b> ${data.end}</p>`;
        const delBtn = document.getElementById('deleteBtn');
        if (data.can_delete) {
          delBtn.style.display = 'inline-block';
          delBtn.onclick = function(){
            if (!confirm('Delete webinar?')) return;
            fetch('delete_event.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id:data.id}) })
              .then(r=>r.json()).then(res=>{ if(res.error) alert(res.error); else { calendar.refetchEvents(); bootstrap.Modal.getInstance(document.getElementById('viewModal')).hide(); } });
          };
        } else { delBtn.style.display = 'none'; }
        new bootstrap.Modal(document.getElementById('viewModal')).show();
      });
    },
    events: 'load_events.php'
  });
  calendar.render();
});
</script>
</body></html>
