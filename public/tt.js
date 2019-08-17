function extractState() {
 var select = document.forms['extrasForm'].modules.options;
 var extras = [];
 for (i=0; i<select.length; i++)
  extras.push(select[i].value);

 return {
  course: document.getElementById('CourseID').value,
  year: document.getElementById('Year').value,
  extras: extras,
  h_lec: document.getElementById('h_lec').checked == true,
  h_tut: document.getElementById('h_tut').checked == true,
  h_lab: document.getElementById('h_lab').checked == true,
 }
}
function generateQueryString(state) {
 var queryStr = '';
 if (state.course) {
  queryStr += 'course='+state.course;
  queryStr += '&year='+state.year;
 }
 for (i=0; i<state.extras.length; i++)
  queryStr += '&extras%5B'+i+'%5D='+state.extras[i];
 if (state.h_lec)  queryStr += '&h_lec=true';
 if (state.h_tut)  queryStr += '&h_tut=true';
 if (state.h_lab)  queryStr += '&h_lab=true';
 return queryStr;
}
function restoreState(state) {
 document.getElementById('CourseID').value = state.course;
 document.getElementById('Year').value = state.year;
 removeOptions();
 for (i=0; i<state.extras.length; i++)
  addOption(state.extras[i]);
 document.getElementById('h_lec').checked = state.h_lec;
 document.getElementById('h_tut').checked = state.h_tut;
 document.getElementById('h_lab').checked = state.h_lab;
}
var LoadBehavior = {
 PUSH: 1,
 REPLACE: 2,
 SKIP: 3
}
function loadTable(stateHandling) {
 var state = extractState();
 var queryStr = generateQueryString(state);
 frames['tableFrame'].document.body.innerHTML='<br /><br /><br /><br /><center><span style="font-family: Optima, Sans-Serif; font-weight: bold; color: white">Loading...</span></center>';
 frames['tableFrame'].location.href='tt.php?'+queryStr;
 switch (stateHandling) {
  case LoadBehavior.SKIP:
   // nothing to do!
   break;
  case LoadBehavior.REPLACE:
   window.history.replaceState(state, 'UL Timetable', '/?'+queryStr)
   break;
  default:
   window.history.pushState(state, 'UL Timetable', '/?'+queryStr)
 }
 if (document.getElementById('options').checked == true)
  document.location.href='tt.ical.php?'+queryStr;
}
function addOption(module) {
 var select = document.forms['extrasForm'].modules.options;
 var moduleInput = document.forms['extrasForm'].module;
 var moduleValue = (typeof module === 'undefined') ? document.forms['extrasForm'].module.value : module;
 var pattern = /[a-z]{2}[0-9]{4}/i;
 var exists = false;
 for (i=0; i<select.length; i++) if (select[i].value == moduleValue.toUpperCase()) exists = true;

 if (!exists) {
  if (moduleValue.match(pattern))
   select[select.length] = new Option(moduleValue.toUpperCase(), moduleValue.toUpperCase());
  else
   alert('Invalid module code');
  }
  if (typeof module === 'undefined') {
   moduleInput.value = "";
  }
}
function removeOptions() {
 var select = document.forms['extrasForm'].modules.options;
 for (i=select.length-1; i>=0; i--) if (select[i].selected) select[i] = null;
}
document.addEventListener('DOMContentLoaded', function() {
 frames['tableFrame'].window.addEventListener('DOMContentLoaded', function() {
  loadTable(LoadBehavior.REPLACE);
 });

 window.onpopstate = function(event) {
  restoreState(event.state);
  loadTable(LoadBehavior.SKIP);
 };
});
