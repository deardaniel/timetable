<?php
session_start();

function getSetting($var)
{
  if (isset($_GET[$var])) return $_GET[$var];
  if (isset($_SESSION[$var])) return $_SESSION[$var];
  return null;
}

function isMobile()
{
 return $mobile = substr_count($_SERVER['HTTP_USER_AGENT'], 'Mobile') > 0;
}

?>
<html>
<head>
<meta charset="utf-8" />
<link rel="stylesheet" type="text/css" href="tt.css" />
<?php if (isMobile()) { ?>
<link rel="stylesheet" type="text/css" href="tt.mobile.css" />
<meta name = "viewport" content = "width=device-width, initial-scale=1.0">
<script>
 window.isMobile = true;
</script>
<?php } ?>
<script src="tt.js"></script>
<title>UL Timetable</title>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-28461989-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body>
<div id="loader" style="display: none"><img src="loader.gif" /> Loading...</div>
<center>
<h1>UL Timetable</h1>

<?php if (!isMobile()) : ?>
 <iframe border="0" frameborder="0" style="border: none;" name="tableFrame" src="tt.php" width="620" height="480"></iframe>
 <?php endif; ?>

<table class="settings">

<tr>
 <th class="top" colspan="2" style="-moz-border-radius: 5px; -webkit-border-radius: 5px; border: 1px solid #235">Course</th>
</tr>
<form name="idForm" onsubmit="loadTable(); return false;">
<tr>
 <td colspan="2">
  <label for="CourseID">LM</label>
  <input class="text" id="CourseID" type="tel" style="float: right" placeholder="110" value="<?= getSetting('course') ?>" />
 </td>
</tr>
<tr>
 <td colspan="2">
  <label for="Year">Year</label>
  <select id="Year" class="select" style="float: right">
   <option<?= getSetting('year') == 1 ? ' selected' : '' ?>>1</option>
   <option<?= getSetting('year') == 2 ? ' selected' : '' ?>>2</option>
   <option<?= getSetting('year') == 3 ? ' selected' : '' ?>>3</option>
   <option<?= getSetting('year') == 4 ? ' selected' : '' ?>>4</option>
   <option<?= getSetting('year') == 5 ? ' selected' : '' ?>>5</option>
  </select>
 </td>
</tr>
<tr>
 <td colspan="2">
  <input class="button" type="submit" value="go" id="goButton" />
 </td>
</tr>
</form>

<tr>
 <th class="top" colspan="2">Options</th>
</tr>
<tr>
 <td style="font-size: 10pt" colspan="2">
  <input class="checkbox" id="options" type="checkbox" value="download" />
   Download iCal file
 </td>
</tr>
<tr>
 <td style="font-size: 10pt" colspan="2">
  <input class="checkbox" id="h_lec" type="checkbox" <?= getSetting('h_lec') ? ' checked' : '' ?>/> Hide Lectures
 </td>
</tr>
<tr>
 <td style="font-size: 10pt" colspan="2">
  <input class="checkbox" id="h_tut" type="checkbox" <?= getSetting('h_tut') ? ' checked' : '' ?>/> Hide Tutorials
 </td>
</tr>
<tr>
 <td style="font-size: 10pt" colspan="2">
  <input class="checkbox" id="h_lab" type="checkbox" <?= getSetting('h_lab') ? ' checked' : '' ?>/> Hide Labs
 </td>
</tr>

<tr>
 <th class="top" colspan="2">Extra Modules</th>
</tr>

<form name="extrasForm" onsubmit="addOption(); return false;">
<tr>
 <td>
  <select id="modules" class="select" multiple="on" name="modules" style="width: 120px;">
   <?php foreach (getSetting('extras') as $extra) : ?>
    <option><?= $extra ?></option>
   <?php endforeach; ?>
  </select>
  <input id="module" class="text" type="text" name="module" style="width: 120px;" />
 </td>
 <td>
  <input id="del" class="button" type="button" value="del" onclick="removeOptions()" />
  <input id="add" class="button" type="submit" value="add" />
 </td>
</tr>
</form>
</table>
</center>
<div id="footer">Created in Sept 2006 by <a href="http://daniel.ie/">Daniel Heffernan</a>, class of 2009. <a href="https://stripe.com/jobs">Jobs</a>.</div>
</body>
</html>
