<?php

include('tt.lib.php');

session_start();
$_SESSION['course'] = $_GET['course'];
$_SESSION['year'] = $_GET['year'];
$_SESSION['extras'] = $_GET['extras'];
$_SESSION['h_lec'] = $_GET['h_lec'];
$_SESSION['h_tut'] = $_GET['h_tut'];
$_SESSION['h_lab'] = $_GET['h_lab'];

$data = scrape($_GET['course'], $_GET['year'], $_GET['extras']);

$calID = '';
if (isset($_GET['course'])) $calID .= 'LM'.$_GET['course'];
if (isset($_GET['year'])) $calID .= '-Y'.$_GET['year'];
if (count($_GET['extras']))
	foreach ($_GET['extras'] as $mid) $calID .= '-'.$mid;

$dayWidths = array_fill(0, count($days), 0);
foreach ($days as $daynum => $daystr)
	if (isset($data['classes'][$daynum]))
		foreach ($data['classes'][$daynum] as $time => $classes)
			$dayWidths[$daynum] = max(count($classes), $dayWidths[$daynum]);

$dayOffsets = $dayWidths;
for ($i = 1; $i < count($dayOffsets); $i++)
	$dayOffsets[$i] = $dayOffsets[$i] + $dayOffsets[$i-1];
for ($i = 0; $i < count($dayOffsets); $i++)
	$dayOffsets[$i] -= $dayWidths[$i];

	// echo var_dump($dayWidths);
$colWidth = 100.0 / ($dayOffsets[count($days)-1] + $dayWidths[count($days)-1] + 1);
$rowHeight = 100.0 / 9.0; // 9 hours in a day
?>
<html>
<head>
<title>Timetable - <?= $calID ?></title>
<link rel="stylesheet" type="text/css" href="tt.css" />
<?php if (substr_count($_SERVER['HTTP_USER_AGENT'], 'Mobile') > 0) { ?>
<link rel="stylesheet" type="text/css" href="tt.mobile.css" />
<meta name = "viewport" content = "width = device-width, user-scalable = yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<?php } ?>
</head>
<body class="time-table">
<?php

$mobile = substr_count($_SERVER['HTTP_USER_AGENT'], 'Mobile') > 0;

if ($mobile)
	echo '<table id="header"><tr><th class="corner" style="width:'.$colWidth.'%"></th>';
else
	echo '<table id="header"><tr><th class="corner"></th>';

for ($i=0; $i<5; $i++)
	if ($mobile)
		echo '<th class="top" style="width:'.($colWidth * $dayWidths[$i]).'%">'.$days[$i].'</th>';
	else
		echo '<th class="top">'.$days[$i].'</th>';
echo '</tr>';
if ($mobile)
	echo '</table><div id="container"><table id="timetable">';

for ($i=9; $i<18; $i++) {
	$time = leftPad($i, '0', 2).':00';
	if ($mobile)
		echo '<tr><th class="side" style="width:'.$colWidth.'%;height:'.$rowHeight.'%">'.$time.'</th>';
	else
		echo '<tr><th class="side">'.$time.'</th>';
	foreach ($days as $daynum => $daystr)
		if ($mobile)
			echo '<td style="width:'.($colWidth * $dayWidths[$daynum]).'%"></td>';
		else
			echo '<td style="width: 100px"></td>';
	echo '</tr>';
}
echo '</table>';
if (!$mobile) echo '<div id="container">';
for ($i=9; $i<18; $i++) {
	$time = leftPad($i, '0', 2).':00';
	foreach ($days as $daynum => $daystr) {
		if (count($data['classes'][$daynum][$time]))
			foreach ($data['classes'][$daynum][$time] as $count => $class) {
				if ($class['length'] > 1)
					for ($n=1; $n<$class['length']; $n++)
						if ($data['classes'][$daynum][leftPad($i+$n, '0', 2).':00'])
							foreach ($data['classes'][$daynum][leftPad($i+$n, '0', 2).':00'] as $ck => $cv)
								$data['classes'][$daynum][leftPad($i+$n, '0', 2).':00'][$ck]['indent']++;
				$totalInPeriod = count($data['classes'][$daynum][$time]);
				unset($group);
				if ($class['group']) $group = '-'.$class['group'];
				$module = '<a href="tt.php?extras%5B0%5D='.$class['module'].'">'.$class['module'].'</a>';
				if ($mobile) {
					echo '<span style="left:'.($colWidth * (($dayOffsets[$daynum]+$count+$class['indent']+1))).'%;top:'.($rowHeight * ($i-9)).'%;height:'.($rowHeight * $class['length']).'%;width:'.($dayWidths[$daynum] * $colWidth * (1/$totalInPeriod)).'%"><div class="'.$colours[$class['module']].'">'.$class['modulename'].' ('.$module.'/'.$class['type'].$group.') @ <b>'.$class['room'].'</b></div></span>';
				} else {
					echo '<div onmouseover="this.className=\''.$colours[$class['module']].'-light\';this.style.zIndex=10" onmouseout="this.className=\''.$colours[$class['module']].'\';this.style.zIndex='.($count+$class['indent']).'" class="'.$colours[$class['module']].'" style="z-index:'.($count+$class['indent']).';left:'.((($daynum+1)*103) + 2 + (($count+$class['indent'])*8)).'px;top:'.(28+($i-9)*50 + (($count+$class['indent'])*8)).'px;height:'.(($class['length'] * 39) + ($class['length']-1)*10).'px;">'.$class['modulename'].' ('.$module.'/'.$class['type'].$group.') @ <b>'.$class['room'].'</b></div>';
				}
			}
	}
}

?>
</div>
</body>
</html>
