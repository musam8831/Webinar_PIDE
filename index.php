<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = $_SESSION['user']; // expects ['id','name','email','role']

// Fetch categories for the form dropdown
$categoriesStmt = $pdo->query('SELECT id, title FROM categories WHERE is_active=1 ORDER BY title ASC');
$categories = $categoriesStmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>PIDE Webinar Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- FullCalendar -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">

  <!-- Tippy tooltip theme -->
  <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light-border.css"/>

  <!-- App styles (your existing) -->
  <link href="./assets/css/styles.css" rel="stylesheet">

  <link href="./assets/css/PIDETheme.css" rel="stylesheet">

</head>
<body>

<?php include 'public/navbar.php'; ?>

<main class="calendar-shell">
  <div class="card calendar-card">
    <div class="card-header">
      <h5 class="calendar-title">
        <i class="bi bi-calendar3"></i> PIDE Webinar Booking Calendar
      </h5>
    </div>
    <div class="card-body">
      <div id="calendar"></div>
    </div>
  </div>
  <div class="footer-text">
    © <?php echo date("Y"); ?> Pakistan Institute of Development Economics (PIDE)
  </div>
</main>

<!-- Add/Edit Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="eventForm">
      <div class="modal-header">
        <h5 class="modal-title">Add Webinar</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id">
        <div class="mb-3">
          <label class="form-label">Webinar Title <span class="text-danger">*</span></label>
          <input class="form-control form-control-lg" name="title" placeholder="Enter webinar title" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Category <span class="text-danger">*</span></label>
          <select class="form-control form-control-lg" name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Start <span class="text-danger">*</span></label>
            <input class="form-control" name="start" type="datetime-local" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">End <span class="text-danger">*</span></label>
            <input class="form-control" name="end" type="datetime-local" required>
          </div>
        </div>
        <div class="alert alert-info mt-2" role="alert">
          <small><i class="bi bi-info-circle"></i> New webinars are submitted for approval. Only approved webinars will be visible to everyone.</small>
        </div>
        <div class="form-text mt-2">⚠️ Overlapping webinars are not allowed.</div>
        <div id="formError" class="text-danger mt-2"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-save px-4" type="submit"><i class="bi bi-save"></i> Submit</button>
        <button class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" type="button">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- FullCalendar (index.global) -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<!-- Tippy -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<script>
const currentUser = <?= json_encode($user, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function(){
  const calendarEl = document.getElementById('calendar');
  const modalEl = document.getElementById('eventModal');
  const modal = new bootstrap.Modal(modalEl);
  const form = document.getElementById('eventForm');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,timeGridDay' },
    selectable: true,
    selectMirror: true,
    timeZone: 'local',          // display in browser local time
    weekends: false,            // Mon-Fri
    firstDay: 1,                // week starts Monday
    slotMinTime: "08:00:00",
    slotMaxTime: "17:00:00",
    slotDuration: "00:30:00",
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: true },

    // Load events
    events: 'public/load_events.php',

    // Create via drag/select
    select: function(info){
      form.reset();
      form.id.value = '';
      form.title.value = '';
      form.category_id.value = '';
      // Pre-fill local date-times (YYYY-MM-DDTHH:MM)
      form.start.value = info.startStr.substring(0,16);
      form.end.value   = info.endStr.substring(0,16);
      document.querySelector('#eventModal .modal-title').innerText='Add Webinar';
      document.getElementById('formError').innerText='';
      modal.show();
    },

    // Custom event rendering: 50-char title + initiator on new line + mini actions
    eventContent: function(arg){
      const titleRaw = arg.event.title || '';
      const initiator = arg.event.extendedProps?.initiator_name || arg.event.extendedProps?.initiator || '';
      const shortTitle = titleRaw.length > 50 ? titleRaw.substring(0,50) + '…' : titleRaw;

      const titleEl = document.createElement('div');
      titleEl.className = 'ev-title';
      titleEl.textContent = shortTitle;

      const initEl = document.createElement('div');
      initEl.className = 'ev-initiator';
      initEl.textContent = initiator ? ('👤 ' + initiator) : '';

      const actions = document.createElement('div');
      actions.className = 'ev-actions';

      const canEdit   = arg.event.extendedProps?.can_edit === true;
      const canDelete = arg.event.extendedProps?.can_delete === true;

      if (canEdit) {
        const eb = document.createElement('button');
        eb.type = 'button';
        eb.className = 'mini-btn edit';
        eb.title = 'Edit';
        eb.innerHTML = '<i class="bi bi-pencil-square"></i>';
        eb.onclick = (e)=>{ e.stopPropagation(); openEdit(arg.event); };
        actions.appendChild(eb);
      }
      if (canDelete) {
        const db = document.createElement('button');
        db.type = 'button';
        db.className = 'mini-btn delete';
        db.title = 'Delete';
        db.innerHTML = '<i class="bi bi-trash"></i>';
        db.onclick = (e)=>{ e.stopPropagation(); delEvent(arg.event.id); };
        actions.appendChild(db);
      }

      const wrap = document.createElement('div');
      wrap.appendChild(titleEl);
      if (initiator) wrap.appendChild(initEl);
      if (canEdit || canDelete) wrap.appendChild(actions);

      return { domNodes: [wrap] };
    },

    // Tooltip with anti-flicker config
    eventDidMount: function(info){
      const start = info.event.start ? new Date(info.event.start) : null;
      const end   = info.event.end   ? new Date(info.event.end)   : null;
      const initiator = info.event.extendedProps?.initiator_name || info.event.extendedProps?.initiator || '';
      const category = info.event.extendedProps?.category_title || '';
      const body = `
        <div style="min-width:220px">
          <div><strong>${info.event.title}</strong></div>
          <div>${start ? start.toLocaleString() : ''} ${end ? (' - ' + end.toLocaleString()) : ''}</div>
          ${category ? ('<div><i class="bi bi-tag"></i> ' + category + '</div>') : ''}
          ${initiator ? ('<div>👤 ' + initiator + '</div>') : ''}
        </div>
      `;
      tippy(info.el, {
        content: body,
        allowHTML: true,
        theme: 'light-border',
        placement: 'top',
        appendTo: document.body,
        interactive: true,
        hideOnClick: false,
        delay: [150, 0],
        duration: [0, 0],
        offset: [0, 8]
      });
    },

    // Click-to-edit shortcut
    eventClick: function(info){
      if (info.event.extendedProps?.can_edit) openEdit(info.event);
    }
  });

  function openEdit(event){
    fetch('public/get_event.php?id='+encodeURIComponent(event.id))
      .then(r=>r.json())
      .then(data=>{
        if (data.error){ alert(data.error); return; }
        form.id.value    = data.id;
        form.title.value = data.title;
        form.category_id.value = data.category_id || '';
        form.start.value = data.start_local; // backend provides local for convenience
        form.end.value   = data.end_local;
        document.querySelector('#eventModal .modal-title').innerText='Edit Webinar';
        document.getElementById('formError').innerText='';
        modal.show();
      })
      .catch(()=>alert('Failed to load event.'));
  }

  function delEvent(id){
    if(!confirm('Delete webinar?')) return;
    fetch('public/delete_event.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ id })
    })
    .then(r=>r.json())
    .then(res=>{
      if(res.error) alert(res.error);
      else calendar.refetchEvents();
    })
    .catch(()=>alert('Failed to delete.'));
  }

  form.addEventListener('submit', function(e){
    e.preventDefault();
    const payload = {
      id: form.id.value ? parseInt(form.id.value, 10) : null,
      title: form.title.value.trim(),
      category_id: form.category_id.value ? parseInt(form.category_id.value, 10) : null,
      start: form.start.value,
      end: form.end.value
    };
    if (!payload.title) { document.getElementById('formError').innerText = 'Title is required.'; return; }
    if (!payload.category_id) { document.getElementById('formError').innerText = 'Category is required.'; return; }

    const url = payload.id ? 'public/update_event.php' : 'public/save_event.php';
    fetch(url, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    })
    .then(r=>r.json())
    .then(res=>{
      if (res.error) {
        document.getElementById('formError').innerText = res.error;
      } else {
        modal.hide();
        calendar.refetchEvents();
        alert(payload.id ? 'Webinar updated successfully.' : 'Webinar submitted for approval.');
      }
    })
    .catch(()=>{ document.getElementById('formError').innerText = 'Request failed. Try again.'; });
  });

  calendar.render();
});
</script>
</body>
</html>
