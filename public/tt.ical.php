<?php
include('tt.lib.php');

$now = date('Ymd').'T'.date('His').'Z';
$basedate = strtotime('last monday');
$daydate = date('Ymd', $basedate);

$data = scrape($_GET['id']);

$calID = $_GET['id'];
if (count($_GET['extras']))
	foreach ($_GET['extras'] as $mid) $calID .= '-'.$mid;
if (!$_GET['id']) $calID = substr($calID, 1);

header('Content-Type: text/calendar');
header("Content-Disposition: attachment; filename={$calID}.ics");

?>
BEGIN:VCALENDAR
VERSION:2.0
X-WR-CALNAME:<?= $calID ?>

PRODID:-//Daniel Heffernan//UL Timetable//EN
X-WR-RELCALID:<?php echo $calID ?>

X-WR-TIMEZONE:Europe/Dublin
CALSCALE:GREGORIAN
METHOD:PUBLISH
<?php
foreach ($days as $daynum => $daystr) {
	$daydate = date('Ymd', $basedate + ($daynum*24*60*60));

	for ($i=9; $i<18; $i++) {
		$time = leftPad($i, '0', 2).':00';
		if (count($data['classes'][$daynum][$time]))
			foreach ($data['classes'][$daynum][$time] as $count => $class) {
				unset($group);
				if ($class['group']) $group = '-'.$class['group'];
				$s = $class['modulename'].' ('.$class['module'].'/'.$class['type'].$group.')';
?>
BEGIN:VEVENT
<? if ($class['length'] == 1) echo "DURATION:PT1H\n" ?>
LOCATION:<?= $class['room'] ?>

DTSTAMP:<?= $now ?>

UID:<?php echo $_GET['id'].'-'.$class['module'].'-'.$daynum.'-'.$i; ?>

SEQUENCE:<?= intval($daynum.leftPad($i, '0', 2).$count) ?>

DTSTART;TZID=Europe/Dublin:<?= $daydate.'T'.leftPad($i,'0',2)."0000\n" ?>
SUMMARY:<?= $s ?>

<? if ($class['length'] > 1) echo "DTEND;TZID=Europe/Dublin:".$daydate.'T'.substr($class['end'],0,2)."0000\n" ?>
RRULE:FREQ=WEEKLY;INTERVAL=1
END:VEVENT
<?php
				
			}

	}
}

?>
END:VCALENDAR
