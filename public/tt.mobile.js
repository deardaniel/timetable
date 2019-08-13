function loadTable() {
 document.getElementById('loader').style.display = 'block';
 var select = document.forms['extrasForm'].modules.options;
 var queryStr = 'course='+document.getElementById('CourseID').value;
 queryStr += '&year='+document.getElementById('Year').value;
 for (i=0; i<select.length; i++) queryStr += '&extras%5B'+i+'%5D='+select[i].value;
 if (document.getElementById('h_lec').checked == true) queryStr += '&h_lec=true';
 if (document.getElementById('h_tut').checked == true) queryStr += '&h_tut=true';
 if (document.getElementById('h_lab').checked == true) queryStr += '&h_lab=true';
 if (document.getElementById('options').checked == true)
  document.location.href='tt.ical.php?'+queryStr;
 else
  document.location.href='tt.php?'+queryStr;
}
function addOption() {
 var select = document.forms['extrasForm'].modules.options;
 var module = document.forms['extrasForm'].module;
 var pattern = /[a-z]{2}[0-9]{4}/i;
 var exists = false;
 document.forms['extrasForm'].modules.style.display = 'block';
 for (i=0; i<select.length; i++) if (select[i].value == module.value.toUpperCase()) exists = true;

 if (!exists) {
  if (module.value.match(pattern)) {
   var o = new Option(module.value.toUpperCase(), module.value.toUpperCase());
   o.selected = true;
   select[select.length] = o;
  } else {
    alert('invalid module code');
  }
 }
 module.value = "";
}
function removeOptions() {
 var select = document.forms['extrasForm'].modules.options;
 for (i=select.length-1; i>=0; i--) if (select[i].selected) select[i] = null;
 if (select.length < 1) document.forms['extrasForm'].modules.style.display = 'none';
}
