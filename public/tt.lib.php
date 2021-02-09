<?php
// ini_set('display_errors', '0');

include_once('lib/simple_html_dom.php');

$days[] = 'Monday';
$days[] = 'Tuesday';
$days[] = 'Wednesday';
$days[] = 'Thursday';
$days[] = 'Friday';

$colours = array();

function getURLContent($url, $params = array())
{
	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query($params)
	));

	$response = curl_exec($ch);
	curl_close($ch);

	// echo $response;

	return $response;
}

function extract_data($url, $formData, $selector, $data=null)
{
	$content = getURLContent($url, $formData);

	$html = new simple_html_dom();
	$html->load($content);

	foreach ($html->find($selector) as $element) {
		if (trim($element->plaintext) == '') {
			continue;
		}

		$day = array_search($element, $element->parent()->children, TRUE);
		preg_match_all("/(?<start>\d{2}:\d{2}) \- (?<end>\d{2}:\d{2})<br \/>(?<module>[A-Z0-9]+) \- (?<type>[A-Z]+)( \- (?<group>[A-Z0-9]+))?<br \/> (?<lecturer>.+?)<br \/>( (?<room>[A-Z0-9 ]+))?(<br \/>)?(Wks:)?[0-9-,]+/", $element->innertext, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			// echo var_dump($match);

			$start =           $match['start'];
			$c['end'] =        $match['end'];
			$c['length'] =     intval(substr($c['end'], 0, 2)) - intval(substr($start, 0, 2));
			$c['module'] =     $match['module'];
			$c['modulename'] = getModuleName($c['module']);
			$c['type'] =       $match['type'];
			$c['group'] =      $match['group'];
			$c['room'] =       $match['room'];

			if (!($c['type'] == 'LEC' && $_GET['h_lec']) && !($c['type'] == 'TUT' && $_GET['h_tut']) && !($c['type'] == 'LAB' && $_GET['h_lab']))
			{
				$orig = $data['classes'][$day][$start];

				if (count($orig))
				{
					$insertpos = 0;
					foreach ($orig as $key => $val)
						if ($val['length'] > $c['length']) $insertpos = $key;
					$data['classes'][$day][$start] = array_merge(array_slice($orig, 0, $insertpos), array($c), array_slice($orig, $insertpos));
				} else {
					$data['classes'][$day][$start][] = $c;
				}
			}
		}
	}
	return $data;
}

function getFullCourse($course)
{
	$courses = json_decode(file_get_contents('data/courses.json'), true);
	foreach ($courses as $c) {
		if ($c['code'] == 'LM'.$course) {
			echo $c['full_name'];
			return $c['full_name'];
		}
	}
}

function scrape($course=null, $year=null, $extras=null)
{
	if ($course && $year) {
		srand(intval(preg_replace('/[^\d]/', '', $course.$year)));
		$fullCourse = getFullCourse($course);

		$data = extract_data(
			'https://www.timetable.ul.ie/UA/CourseTimetable.aspx',
			array(
				'ctl00$HeaderContent$CourseYearDropdown' => $year,
				'ctl00$HeaderContent$CourseDropdown' => $fullCourse,
				'__VIEWSTATE' => file_get_contents('data/courses.viewstate'),
				'__EVENTVALIDATION' => file_get_contents('data/courses.eventvalidation')
			),
			'table#MainContent_CourseTimetableGridView td'
		);
	}

	if (count($extras) > 0) {
		srand(filter_var(implode($extras), FILTER_SANITIZE_NUMBER_INT));
		foreach ($extras as $m) {
			$data = extract_data(
				'https://www.timetable.ul.ie/UA/ModuleTimetable.aspx',
				array(
					'ctl00$HeaderContent$DropDownList1' => $m,
					'__VIEWSTATE' => file_get_contents('data/modules.viewstate'),
					'__EVENTVALIDATION' => file_get_contents('data/modules.eventvalidation'),
				),
				'table#MainContent_ModuleTimetableGridView td',
				isset($data) ? $data : null
			);
		}
	}

	return $data;
}

function getModuleName($id) {
	global $modulenames;
	global $colours;

	if (!isset($modulenames)) {
		$modulenames = json_decode(file_get_contents('data/modules.json'));
	}

	if (!isset($colours[$id])) {
		$colours[$id] = pickColour();
	}
	// echo var_dump($modulenames);
	// echo $id;
	return $modulenames->$id;
}

function pickColour() {
	global $colours;
	$col = array('red', 'orange', 'yellow', 'green', 'blue', 'purple', 'grey');
	shuffle($col);
	foreach ($col as $c)
		if (!in_array($c, $colours)) return $c;
}

function formatName ($name)
{
	for ($i=1; $i<strlen($name); $i++) {
		if (ctype_alnum($name[$i-1])) $name[$i] = strtolower($name[$i]);
	}
	return $name;
}

function leftPad($s, $pad, $limit)
{
	while (strlen($s) < $limit) $s = $pad.$s;
	return $s;
}

?>
