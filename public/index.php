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
<!-- Tippy (no blinking tooltips) -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css"/>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
</head><body>
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
        <input type="hidden" name="id">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
const currentUser = <?= json_encode($user) ?>;

document.addEventListener('DOMContentLoaded', function(){
  const calendarEl = document.getElementById('calendar');
  const modalEl = document.getElementById('eventModal');
  const modal = new bootstrap.Modal(modalEl);
  const form = document.getElementById('eventForm');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
    selectable: true,
    selectMirror: true,
    select: function(info){
      form.reset();
      form.id.value = '';
      form.title.value='';
      form.start.value = info.startStr.substring(0,16);
      form.end.value = info.endStr.substring(0,16);
      document.querySelector('#eventModal .modal-title').innerText = 'Add Webinar';
      document.getElementById('formError').innerText='';
      modal.show();
    },
    events: 'load_events.php',
    eventContent: function(arg){
      const title = document.createElement('div');
      title.textContent = arg.event.title;

      const actions = document.createElement('span');
      // Small edit/delete buttons (only if allowed)
      const canDelete = arg.event.extendedProps.can_delete === true;
      const canEdit = arg.event.extendedProps.can_edit === true;

      if (canEdit) {
        const eb = document.createElement('button');
        eb.className = 'mini-btn edit';
        eb.innerHTML = '✎';
        eb.title = 'Edit';
        eb.onclick = (e)=>{ e.stopPropagation(); openEdit(arg.event); };
        actions.appendChild(eb);
      }
      if (canDelete) {
        const db = document.createElement('button');
        db.className = 'mini-btn delete';
        db.innerHTML = '🗑';
        db.title = 'Delete';
        db.onclick = (e)=>{ e.stopPropagation(); delEvent(arg.event.id); };
        actions.appendChild(db);
      }
      const wrap = document.createElement('div');
      wrap.appendChild(title);
      wrap.appendChild(actions);
      return { domNodes: [wrap] };
    },
    eventDidMount: function(info){
      // Tippy tooltip (no blinking)
      const start = new Date(info.event.start);
      const end = new Date(info.event.end);
      const content = `<strong>${info.event.title}</strong><br>${start.toLocaleString()} - ${end.toLocaleString()}<br>By: ${info.event.extendedProps.initiator_name}`;
      tippy(info.el, { content, allowHTML: true, theme: 'light-border', placement: 'top' });
    },
    eventClick: function(info){
      // Open edit as well on click (optional)
      if (info.event.extendedProps.can_edit) openEdit(info.event);
    }
  });

  function openEdit(event){
    fetch('get_event.php?id=' + event.id).then(r=>r.json()).then(data=>{
      if (data.error) { alert(data.error); return; }
      form.id.value = data.id;
      form.title.value = data.title;
      form.start.value = data.start_local;
      form.end.value = data.end_local;
      document.querySelector('#eventModal .modal-title').innerText = 'Edit Webinar';
      document.getElementById('formError').innerText='';
      modal.show();
    });
  }

  function delEvent(id){
    if (!confirm('Delete webinar?')) return;
    fetch('delete_event.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}) })
      .then(r=>r.json()).then(res=>{
        if(res.error) alert(res.error); else calendar.refetchEvents();
      });
  }

  form.onsubmit = (e)=>{
    e.preventDefault();
    const payload = {
      id: form.id.value ? parseInt(form.id.value,10) : null,
      title: form.title.value,
      start: form.start.value,
      end: form.end.value
    };
    const url = payload.id ? 'update_event.php' : 'save_event.php';
    fetch(url, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    }).then(r=>r.json()).then(res=>{
      if (res.error) { document.getElementById('formError').innerText = res.error; }
      else { modal.hide(); calendar.refetchEvents(); }
    });
  };

  calendar.render();
});
</script>
</body></html>
