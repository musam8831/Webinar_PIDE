<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$year = (int)($_GET['year'] ?? date('Y'));
?>
<!doctype html><html><head><meta charset="utf-8"><title>Yearly Dashboard - <?= $year ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/styles.css" rel="stylesheet">
<style>
.heatmap { display:flex; flex-wrap:wrap; gap:2px; max-width: 820px; }
.heatday { width:14px; height:14px; border-radius:2px; display:inline-block; }
.heatlegend { display:flex; gap:6px; align-items:center; margin-top:8px; }
.month-grid { display:grid; grid-template-columns: repeat(3,1fr); gap:12px; }
.mini-month { border:1px solid #e3e3e3; padding:8px; background:#fff; }
.mini-month table { width:100%; font-size:12px; }
.day-cell { width:28px; height:24px; text-align:right; padding:2px; }
.badge-count { font-size:10px; }
.weekly-grid { width:100%; border-collapse: collapse; background:#fff; }
.weekly-grid th, .weekly-grid td { border:1px solid #e7e7e7; vertical-align: top; padding:6px; font-size:12px; min-height:68px; }
.weekly-grid th { background:#f8f9fa; position: sticky; top:0; z-index:1; }
.week-label { width:64px; text-align:center; font-weight:600; }
.cell-date { font-weight:600; font-size:12px; display:block; margin-bottom:4px; }
.event-pill { background:#e6f0ff; border:1px solid #c7dcff; border-radius:6px; padding:2px 6px; margin:2px 0; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.event-pill small { opacity:0.8; }
.hidden { display:none; }
.sticky-tools { position: sticky; top:0; z-index: 5; background:#fff; padding:8px 0; }
</style>
</head><body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container">
  <div class="d-flex align-items-center mb-3">
    <a class="btn btn-outline-secondary btn-sm" href="yearly_dashboard.php?year=<?= $year-1 ?>">&laquo; <?= $year-1 ?></a>
    <h5 class="mx-3 my-0"><?= $year ?></h5>
    <a class="btn btn-outline-secondary btn-sm" href="yearly_dashboard.php?year=<?= $year+1 ?>"><?= $year+1 ?> &raquo;</a>
    <div class="btn-group ms-3" role="group">
      <button id="btnHeatmap" class="btn btn-sm btn-primary">Heatmap</button>
      <button id="btnMini" class="btn btn-sm btn-outline-primary">Mini Calendars</button>
      <button id="btnWeekly" class="btn btn-sm btn-outline-primary">Weekly Grid</button>
    </div>
  </div>

  <div id="heatmapView">
    <h6>Heatmap</h6>
    <div id="heatmap" class="heatmap"></div>
    <div class="heatlegend"><span>Less</span><div style="width:80px;display:flex;gap:4px;"><div style="width:14px;height:14px;background:#f0f0f0"></div><div style="width:14px;height:14px;background:#cfe9ff"></div><div style="width:14px;height:14px;background:#7fbfff"></div><div style="width:14px;height:14px;background:#2b8cff"></div></div><span>More</span></div>
  </div>

  <div id="calendarView" class="hidden">
    <h6>Mini Calendars</h6>
    <div class="month-grid" id="monthsGrid"></div>
  </div>

  <div id="weeklyGridView" class="hidden">
    <div class="d-flex justify-content-between align-items-center sticky-tools">
      <h6 class="mb-0">Weekly Grid (Mon–Fri × 52 weeks)</h6>
      <div><small class="text-muted">Click a day to see full details.</small></div>
    </div>
    <div class="table-responsive">
      <table class="weekly-grid table">
        <thead>
          <tr><th class="week-label">Week</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th></tr>
        </thead>
        <tbody id="weeklyGridBody"></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="dayModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title" id="dayModalTitle"></h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body" id="dayModalBody"></div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const year = <?= $year ?>;
let currentView = 'heatmap';
function dateToYMD(d){ return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); }
function getColorForCount(count){ if(count<=0) return '#f0f0f0'; if(count===1) return '#cfe9ff'; if(count<=3) return '#7fbfff'; return '#2b8cff'; }

async function fetchYearEvents() {
  const res = await fetch('fetch_year_events.php?year=' + year);
  return res.json();
}
function showDayDetails(ymd, events) {
  document.getElementById('dayModalTitle').innerText = ymd + ' — ' + (events.length ? (events.length + ' webinar(s)') : 'No webinars');
  const body = document.getElementById('dayModalBody'); body.innerHTML='';
  if(!events || events.length===0){ body.innerHTML='<p>No webinars scheduled.</p>'; }
  else {
    const ul = document.createElement('ul');
    events.forEach(ev => { const li = document.createElement('li'); li.innerHTML = '<b>'+ev.title+'</b><br><small>'+ev.start+' → '+ev.end+' • '+ev.initiator+'</small>'; ul.appendChild(li); });
    body.appendChild(ul);
  }
  new bootstrap.Modal(document.getElementById('dayModal')).show();
}

/* Heatmap */
function buildHeatmap(eventsByDate){
  const wrap = document.getElementById('heatmap'); wrap.innerHTML='';
  const start = new Date(year,0,1); const end = new Date(year+1,0,1);
  for (let d = new Date(start); d < end; d.setDate(d.getDate()+1)) {
    const ymd = dateToYMD(d); const cnt = eventsByDate[ymd]?eventsByDate[ymd].length:0;
    const el = document.createElement('div'); el.className='heatday'; el.style.background=getColorForCount(cnt); el.title=ymd+(cnt?(' - '+cnt+' webinar(s)'):''); el.onclick=()=>showDayDetails(ymd, eventsByDate[ymd]||[]); wrap.appendChild(el);
  }
}

/* Mini-calendars */
function buildMiniCalendars(eventsByDate){
  const grid = document.getElementById('monthsGrid'); grid.innerHTML='';
  for(let m=0;m<12;m++){
    const div = document.createElement('div'); div.className='mini-month';
    const dt = new Date(year,m,1); const monthName = dt.toLocaleString(undefined,{month:'long'});
    const table = document.createElement('table'); const thead = document.createElement('thead');
    thead.innerHTML = '<tr><th colspan="7" class="text-center">'+monthName+'</th></tr><tr><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th></tr>';
    table.appendChild(thead); const tbody=document.createElement('tbody');
    let row=document.createElement('tr'); const firstDay = new Date(year,m,1).getDay();
    for(let i=0;i<firstDay;i++) row.appendChild(td(''));
    const days = new Date(year,m+1,0).getDate();
    for(let d=1; d<=days; d++){
      if(row.children.length===7){ tbody.appendChild(row); row=document.createElement('tr'); }
      const ymd = year+'-'+String(m+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
      const cnt = eventsByDate[ymd]?eventsByDate[ymd].length:0;
      const c = td('<div class="day-cell">'+d+(cnt?(' <span class="badge bg-primary badge-count">'+cnt+'</span>'):'')+'</div>');
      if (cnt){ c.style.cursor='pointer'; c.onclick=()=>showDayDetails(ymd, eventsByDate[ymd]); }
      row.appendChild(c);
    }
    while(row.children.length<7) row.appendChild(td(''));
    tbody.appendChild(row); table.appendChild(tbody); div.appendChild(table); grid.appendChild(div);
  }
  function td(html){ const el=document.createElement('td'); el.innerHTML=html; return el; }
}

/* Weekly Grid (Mon–Fri × 52 weeks) */
function buildWeeklyGrid(eventsByDate){
  const body = document.getElementById('weeklyGridBody'); body.innerHTML='';
  const start = new Date(year,0,1); while(start.getDay()!==1){ start.setDate(start.getDate()+1); } // first Monday
  for(let w=0; w<52; w++){
    const tr = document.createElement('tr'); const th=document.createElement('th'); th.className='week-label'; th.textContent='W'+(w+1); tr.appendChild(th);
    for(let d=0; d<5; d++){
      const date = new Date(start); date.setDate(start.getDate()+w*7+d);
      const td = document.createElement('td');
      if (date.getFullYear()!==year){ tr.appendChild(td); continue; }
      const ymd = dateToYMD(date);
      const label = document.createElement('span'); label.className='cell-date'; label.textContent = date.toLocaleString(undefined, {month:'short', day:'numeric'}); td.appendChild(label);
      const todays = eventsByDate[ymd]||[];
      if (todays.length===0){ const empty=document.createElement('div'); empty.className='text-muted'; empty.style.fontSize='11px'; empty.textContent='—'; td.appendChild(empty); }
      else {
        const maxShow=3;
        todays.slice(0,maxShow).forEach(ev=>{ const pill=document.createElement('a'); pill.href='#'; pill.className='event-pill'; pill.onclick=(e)=>{e.preventDefault(); showDayDetails(ymd, todays);}; pill.innerHTML='<strong>'+ev.title+'</strong> <small>'+ev.start+'–'+ev.end+'</small>'; td.appendChild(pill); });
        if(todays.length>maxShow){ const more=document.createElement('a'); more.href='#'; more.onclick=(e)=>{e.preventDefault(); showDayDetails(ymd, todays);}; more.innerHTML='<small>+'+(todays.length-maxShow)+' more</small>'; td.appendChild(more); }
      }
      td.style.cursor='pointer'; td.addEventListener('click', ()=> showDayDetails(ymd, todays));
      tr.appendChild(td);
    }
    body.appendChild(tr);
  }
}

function setView(view){
  currentView = view;
  document.getElementById('heatmapView').classList.toggle('hidden', view!=='heatmap');
  document.getElementById('calendarView').classList.toggle('hidden', view!=='mini');
  document.getElementById('weeklyGridView').classList.toggle('hidden', view!=='weekly');
  document.getElementById('btnHeatmap').className = 'btn btn-sm ' + (view==='heatmap'?'btn-primary':'btn-outline-primary');
  document.getElementById('btnMini').className = 'btn btn-sm ' + (view==='mini'?'btn-primary':'btn-outline-primary');
  document.getElementById('btnWeekly').className = 'btn btn-sm ' + (view==='weekly'?'btn-primary':'btn-outline-primary');
}
document.getElementById('btnHeatmap').onclick=()=>setView('heatmap');
document.getElementById('btnMini').onclick=()=>setView('mini');
document.getElementById('btnWeekly').onclick=()=>setView('weekly');

(async ()=>{
  const data = await fetch('fetch_year_events.php?year='+year).then(r=>r.json());
  const map = {}; data.forEach(ev=>{ if(!map[ev.date]) map[ev.date]=[]; map[ev.date].push(ev); });
  buildHeatmap(map); buildMiniCalendars(map); buildWeeklyGrid(map); setView('heatmap');
})();
</script>
</body></html>
