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
  <!-- Quill rich text editor -->
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

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

<!-- Info Dialog - Shown after creating webinar -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="bi bi-info-circle-fill"></i> Webinar Submitted</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info" role="alert">
          <i class="bi bi-check-circle"></i> <strong>Success!</strong>
        </div>
        <p><strong>Your webinar has been submitted for approval.</strong></p>
        <p class="text-muted">The administrators will review your submission and approve or reject it based on the content quality and schedule availability.</p>
        <hr>
        <div class="info-details">
          <p><strong>What happens next?</strong></p>
          <ul>
            <li>Your webinar will appear with an <span style="background: #ff8c00; color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px;">⏳ Pending</span> status on your calendar</li>
            <li>Only you and administrators can see pending webinars</li>
            <li>Once approved, it will turn <span style="background: #118C3C; color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px;">✓ Approved</span> (green)</li>
            <li>You can edit or delete pending webinars anytime</li>
            <li>You cannot edit approved webinars - only administrators can</li>
          </ul>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-info px-4" data-bs-dismiss="modal" type="button"><i class="bi bi-check"></i> Understood</button>
      </div>
    </div>
  </div>
</div>

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
            <div class="d-flex gap-2 position-relative align-items-center">
              <input class="form-control date-input" name="start_date" type="date" required>
              <input type="hidden" name="start_time">
              <span class="selected-time badge bg-light text-dark" data-target="start"></span>
              <button type="button" class="btn btn-outline-secondary time-btn" data-target="start" title="Choose time" style="padding: 0.25rem 0.5rem;"><i class="bi bi-clock"></i></button>
              <div class="time-panel" data-target="start" hidden></div>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">End <span class="text-danger">*</span></label>
            <div class="d-flex gap-2 position-relative align-items-center">
              <input class="form-control date-input" name="end_date" type="date" required>
              <input type="hidden" name="end_time">
              <span class="selected-time badge bg-light text-dark" data-target="end"></span>
              <button type="button" class="btn btn-outline-secondary time-btn" data-target="end" title="Choose time" style="padding: 0.25rem 0.5rem;"><i class="bi bi-clock"></i></button>
              <div class="time-panel" data-target="end" hidden></div>
            </div>
          </div>
        </div>
        <div class="alert alert-info mt-2" role="alert">
          <small><i class="bi bi-info-circle"></i> New webinars are submitted for approval. Only approved webinars will be visible to everyone.</small>
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <input type="hidden" name="description">
          <div id="quillEditor" style="height:160px;"></div>
          <div class="form-text">Use the editor to provide webinar details (rich text).</div>
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
<!-- Quill -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
const currentUser = <?= json_encode($user, JSON_UNESCAPED_UNICODE) ?>;

// Notification function
function showNotification(message, type = 'success', duration = 4000) {
  const toast = document.createElement('div');
  toast.className = `notification-toast ${type}`;
  
  const iconMap = {
    'success': 'bi-check-circle-fill',
    'error': 'bi-x-circle-fill',
    'info': 'bi-info-circle-fill'
  };
  
  toast.innerHTML = `
    <i class="bi ${iconMap[type]} notification-icon"></i>
    <span class="notification-message">${message}</span>
    <span class="notification-close" onclick="this.parentElement.style.animation='slideOut 0.3s ease-out'; setTimeout(() => this.parentElement.remove(), 300);">&times;</span>
  `;
  
  document.body.appendChild(toast);
  
  if (duration > 0) {
    setTimeout(() => {
      toast.style.animation = 'slideOut 0.3s ease-out';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  }
}

document.addEventListener('DOMContentLoaded', function(){
  const calendarEl = document.getElementById('calendar');
  const modalEl = document.getElementById('eventModal');
  const modal = new bootstrap.Modal(modalEl);
  const infoModalEl = document.getElementById('infoModal');
  const infoModal = new bootstrap.Modal(infoModalEl);
  const form = document.getElementById('eventForm');

    // Initialize Quill editor
    let quill;
    function ensureQuill(){
      if (quill) return;
      const qEl = document.getElementById('quillEditor');
      if (!qEl) return;
      quill = new Quill('#quillEditor', { theme: 'snow', modules: { toolbar: [['bold','italic','underline'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['link']] } });
    }

  // Slot configuration — keep in sync with FullCalendar options below
  const SLOT_MIN = '09:00';
  const SLOT_MAX = '18:00';
  const SLOT_STEP_MIN = 30;

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,timeGridDay' },
    selectable: true,
    selectMirror: true,
    timeZone: 'local',
    weekends: false,
    firstDay: 1,
    slotMinTime: SLOT_MIN + ':00',
    slotMaxTime: SLOT_MAX + ':00',
    slotDuration: '00:30:00',
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: true },
    height: 'auto',
    contentHeight: 'auto',
    events: 'public/load_events.php',

    // Create via drag/select -> populate date + time controls
    select: function(info){
      form.reset();
      form.id.value = '';
      form.title.value = '';
      form.category_id.value = '';
      // ensure quill exists and clear editor for new event
      ensureQuill();
      if (quill) quill.root.innerHTML = '';
      // prefill separated date + time values (date-only input + hidden time)
      form.start_date.value = info.startStr.substring(0,10);
      form.start_time.value = info.startStr.substring(11,16);
      form.end_date.value = info.endStr.substring(0,10);
      form.end_time.value = info.endStr.substring(11,16);
      // update visible selected time badges
      const stBadge = document.querySelector('.selected-time[data-target="start"]');
      const enBadge = document.querySelector('.selected-time[data-target="end"]');
      if (stBadge) stBadge.textContent = form.start_time.value || '';
      if (enBadge) enBadge.textContent = form.end_time.value || '';
      document.querySelector('#eventModal .modal-title').innerText='Add Webinar';
      document.getElementById('formError').innerText='';
      modal.show();
    },

    // Custom event rendering: 50-char title + initiator on new line + mini actions
    eventContent: function(arg){
      const titleRaw = arg.event.title || '';
      const initiator = arg.event.extendedProps?.initiator_name || arg.event.extendedProps?.initiator || '';
      const shortTitle = titleRaw.length > 50 ? titleRaw.substring(0,50) + '…' : titleRaw;
      const isPending = arg.event.extendedProps?.is_pending || false;

      const titleEl = document.createElement('div');
      titleEl.className = 'ev-title';
      titleEl.textContent = shortTitle;

      const initEl = document.createElement('div');
      initEl.className = 'ev-initiator';
      initEl.textContent = initiator ? ('👤 ' + initiator) : '';

      const statusEl = document.createElement('div');
      statusEl.className = 'ev-status';
      if (isPending) {
        statusEl.innerHTML = '<span style="font-size: 10px; background: #ff8c00; color: white; padding: 2px 6px; border-radius: 3px;">⏳ Pending</span>';
      }

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
      if (isPending) wrap.appendChild(statusEl);
      if (canEdit || canDelete) wrap.appendChild(actions);

      return { domNodes: [wrap] };
    },

    // Tooltip with anti-flicker config
    eventDidMount: function(info){
      const start = info.event.start ? new Date(info.event.start) : null;
      const end   = info.event.end   ? new Date(info.event.end)   : null;
      const initiator = info.event.extendedProps?.initiator_name || info.event.extendedProps?.initiator || '';
      const category = info.event.extendedProps?.category_title || '';
      const isApproved = info.event.extendedProps?.is_approved || false;
      const isRejected = info.event.extendedProps?.is_rejected || false;
      const isPending = info.event.extendedProps?.is_pending || false;
      
      let statusBadge = '';
      if (isApproved) {
        statusBadge = '<span style="background: #118C3C; color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px;">✓ Approved</span>';
      } else if (isRejected) {
        statusBadge = '<span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px;">✗ Rejected</span>';
      } else if (isPending) {
        statusBadge = '<span style="background: #ff8c00; color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px;">⏳ Pending Approval</span>';
      }
      
      const body = `
        <div style="min-width:220px">
          <div><strong>${info.event.title}</strong></div>
          <div>${statusBadge}</div>
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
    ,
    eventsSet: function(events){
      // refresh any open time-panels after calendar events are (re)loaded
      document.querySelectorAll('.time-panel').forEach(p=>{
        if (!p.hidden){
          const t = p.dataset.target;
          showTimePanelFor(t);
        }
      });
    }
  });

  // Helper: generate list of times between min(inclusive) and max(exclusive) stepping by minutes
  function generateTimeSlots(minHHMM, maxHHMM, stepMin){
    const parts = (s)=>{ const [h,m]=s.split(':').map(Number); return h*60 + m; };
    const start = parts(minHHMM);
    const end = parts(maxHHMM);
    const out = [];
    for(let t = start; t < end; t += stepMin){
      const hh = String(Math.floor(t/60)).padStart(2,'0');
      const mm = String(t%60).padStart(2,'0');
      out.push(hh + ':' + mm);
    }
    return out;
  }

  // Show a compact time-panel for a given target (start/end) and input
  function showTimePanelFor(targetName){
    const panel = document.querySelector('.time-panel[data-target="'+targetName+'"]');
    const dateInput = form[targetName + '_date'];
    const timeHidden = form[targetName + '_time'];
    const badge = document.querySelector('.selected-time[data-target="'+targetName+'"]');
    if (!panel || !dateInput || !timeHidden) return;
    panel.innerHTML = '';
    const datePart = dateInput.value && dateInput.value.length >= 10 ? dateInput.value : (new Date()).toISOString().substring(0,10);
    const slots = generateTimeSlots(SLOT_MIN, SLOT_MAX, SLOT_STEP_MIN);

    // compute busy intervals for the date
    const dayStart = new Date(datePart + 'T00:00:00');
    const dayEnd = new Date(dayStart.getTime() + 24*60*60*1000);
    const events = calendar.getEvents();
    const busy = [];
    events.forEach(ev => {
      if (!ev.start || !ev.end) return;
      const s = ev.start;
      const e = ev.end;
      const overlapStart = s < dayStart ? dayStart : s;
      const overlapEnd = e > dayEnd ? dayEnd : e;
      if (overlapStart < overlapEnd){
        const minsStart = (overlapStart.getHours()*60 + overlapStart.getMinutes());
        const minsEnd = (overlapEnd.getHours()*60 + overlapEnd.getMinutes());
        busy.push([minsStart, minsEnd]);
      }
    });

    // render items
    slots.forEach(t => {
      const item = document.createElement('div');
      item.className = 'tp-item';
      item.dataset.value = t;
      const [hh,mm] = t.split(':').map(Number);
      const tmin = hh*60 + mm;
      // determine disabled if any busy interval covers this start instant
      let disabled = false;
      for (const b of busy){ if (tmin >= b[0] && tmin < b[1]) { disabled = true; break; } }
      if (disabled) item.classList.add('disabled');
      item.textContent = t;
      item.addEventListener('click', function(){
        if (item.classList.contains('disabled')) return;
        timeHidden.value = item.dataset.value;
        if (badge) badge.textContent = item.dataset.value;
        panel.hidden = true;
        // dispatch change for any listeners
        timeHidden.dispatchEvent(new Event('change'));
      });
      panel.appendChild(item);
    });

    // allow end at boundary (SLOT_MAX)
    const endItem = document.createElement('div');
    endItem.className = 'tp-item';
    endItem.dataset.value = SLOT_MAX;
    endItem.textContent = SLOT_MAX;
    // mark disabled if boundary overlaps
    let endDisabled = false;
    const endM = parseInt(SLOT_MAX.split(':')[0],10)*60 + parseInt(SLOT_MAX.split(':')[1],10);
    for (const b of busy){ if (endM > b[0] && endM <= b[1]) { endDisabled = true; break; } }
    if (endDisabled) endItem.classList.add('disabled');
    endItem.addEventListener('click', function(){ if (endItem.classList.contains('disabled')) return; timeHidden.value = endItem.dataset.value; if (badge) badge.textContent = endItem.dataset.value; panel.hidden = true; timeHidden.dispatchEvent(new Event('change')); });
    panel.appendChild(endItem);

    panel.hidden = false;
    // ensure quill exists when modal is open
    ensureQuill();
  }

  function hideAllTimePanels(){
    document.querySelectorAll('.time-panel').forEach(p=>p.hidden = true);
  }

  // wire up clock buttons and input focus
  document.querySelectorAll('.time-btn').forEach(btn => {
    btn.addEventListener('click', function(e){
      e.stopPropagation();
      const target = this.dataset.target;
      const panel = document.querySelector('.time-panel[data-target="'+target+'"]');
      if (panel && !panel.hidden) { panel.hidden = true; return; }
      hideAllTimePanels();
      showTimePanelFor(target);
    });
  });

  // show panel when date input is focused
  document.querySelectorAll('.date-input').forEach(inp => {
    inp.addEventListener('focus', function(){
      const base = this.name.replace('_date','');
      hideAllTimePanels();
      showTimePanelFor(base);
    });
    // when date changes, clear previously selected time and badge
    inp.addEventListener('change', function(){
      const base = this.name.replace('_date','');
      const timeHidden = form[base + '_time'];
      const badge = document.querySelector('.selected-time[data-target="'+base+'"]');
      if (timeHidden) timeHidden.value = '';
      if (badge) badge.textContent = '';
    });
  });

  // click outside to close
  document.addEventListener('click', function(e){
    if (!e.target.closest('.time-panel') && !e.target.closest('.time-btn') && !e.target.closest('.datetime-input')){
      hideAllTimePanels();
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
        // backend provides local (YYYY-MM-DDTHH:MM)
        if (data.start_local){
          form.start_date.value = data.start_local.substring(0,10);
          form.start_time.value = data.start_local.substring(11,16);
          const stBadge = document.querySelector('.selected-time[data-target="start"]'); if (stBadge) stBadge.textContent = form.start_time.value;
        } else { form.start_date.value = ''; form.start_time.value = ''; }
        if (data.end_local){
          form.end_date.value = data.end_local.substring(0,10);
          form.end_time.value = data.end_local.substring(11,16);
          const enBadge = document.querySelector('.selected-time[data-target="end"]'); if (enBadge) enBadge.textContent = form.end_time.value;
        } else { form.end_date.value = ''; form.end_time.value = ''; }
        // description: populate hidden input and quill editor if present
        form.description.value = data.description || '';
        ensureQuill();
        if (quill) quill.root.innerHTML = data.description || '';
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
      else {
        showNotification('Webinar deleted successfully.', 'success');
        calendar.refetchEvents();
      }
    })
    .catch(()=>alert('Failed to delete.'));
  }

  form.addEventListener('submit', function(e){
    e.preventDefault();
    document.getElementById('formError').innerText = '';
    const payload = {
      id: form.id.value ? parseInt(form.id.value, 10) : null,
      title: form.title.value.trim(),
      category_id: form.category_id.value ? parseInt(form.category_id.value, 10) : null
    };
    if (!payload.title) { document.getElementById('formError').innerText = 'Title is required.'; return; }
    if (!payload.category_id) { document.getElementById('formError').innerText = 'Category is required.'; return; }

    // Date/time taken from separated date + time inputs
    const sDate = form.start_date.value;
    const sTime = form.start_time.value;
    const eDate = form.end_date.value;
    const eTime = form.end_time.value;
    if (!sDate || !sTime || !eDate || !eTime) { document.getElementById('formError').innerText = 'Start and end date/time are required.'; return; }

    const startISO = sDate + 'T' + sTime;
    const endISO = eDate + 'T' + eTime;
    const newStart = new Date(startISO + ':00');
    const newEnd = new Date(endISO + ':00');
    if (!(newStart < newEnd)) { document.getElementById('formError').innerText = 'End must be after start.'; return; }

    // Validate within slot availability
    const toMins = s => { const [h,m]=s.split(':').map(Number); return h*60+m; };
    const sMin = toMins(SLOT_MIN);
    const sMax = toMins(SLOT_MAX);
    const newStartM = toMins(sTime);
    const newEndM = toMins(eTime);
    if (newStartM < sMin || newEndM > sMax) {
      document.getElementById('formError').innerText = `Allowed booking time is between ${SLOT_MIN} and ${SLOT_MAX}.`;
      return;
    }
    // Validate alignment to step
    if ((newStartM % SLOT_STEP_MIN) !== 0 || (newEndM % SLOT_STEP_MIN) !== 0) {
      document.getElementById('formError').innerText = `Times must align to ${SLOT_STEP_MIN}-minute slots.`;
      return;
    }

    // Overlap check against existing events
    const events = calendar.getEvents();
    for (const ev of events){
      if (!ev.start || !ev.end) continue;
      if (payload.id && String(ev.id) === String(payload.id)) continue; // ignore same event when editing
      if (newStart < ev.end && ev.start < newEnd) {
        document.getElementById('formError').innerText = 'Selected time overlaps another webinar.';
        return;
      }
    }

    payload.start = startISO;
    payload.end = endISO;

    // include description (get from quill if available)
    ensureQuill();
    const descHtml = (quill ? quill.root.innerHTML : (form.description.value || ''));
    form.description.value = descHtml;
    payload.description = descHtml;

    const url = payload.id ? 'public/update_event.php' : 'public/save_event.php';
    const isNewEvent = !payload.id;
    fetch(url, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    })
    .then(r=>r.json())
    .then(res=>{
      if (res.error) {
        showNotification(res.error, 'error');
      } else {
        modal.hide();
        calendar.refetchEvents();
        if (isNewEvent) {
          showNotification('Webinar submitted for approval!', 'success', 3000);
          setTimeout(() => { infoModal.show(); }, 500);
        } else {
          showNotification('Webinar updated successfully.', 'success', 5000);
        }
      }
    })
    .catch(()=>{ showNotification('Request failed. Please try again.', 'error'); });
  });

  calendar.render();
});
</script>
</body>
</html>
