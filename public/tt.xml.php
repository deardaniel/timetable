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
c
?>
<timetable>
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
<class modulename="<?= $class['modulename'] ?>" modulecode="<?= $class['module'] ?>" type="<?= $class['type'] ?>" <?= ($group ? 'group="'.$class['group'].'" ' : ' ') ?>duration="<?= $class['length'] ?>" location="<?= $class['room'] ?>" date="<?= $daydate ?>" time="<?= leftPad($i,'0',2).'00' ?>" />
<?php
				
			}

	}
}

?>
</timetable>

