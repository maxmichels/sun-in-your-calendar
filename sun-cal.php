<?php
// Loading variables from URL
if (isset($_GET['lat'])) {
  $lat = $_GET['lat'];
}
if (isset($_GET['lon'])) {
  $lon = $_GET['lon'];
}
if (isset($_GET['days'])) {
  $days = $_GET['days'];
} else {
  $days = 30;
}
if(!isset($lat) && !isset($lon)) {
  header("HTTP/1.1 500 Internal Server Error");
  exit;
}

// Loading json
$list = array();
for ($i=0; $i < $days; $i++) {
  $date = date("Y-m-d", strtotime("+$i days"));
  $string = file_get_contents("https://api.sunrise-sunset.org/json?formatted=0&lat=" . $lat . "&lng=" . $lon . "&date=" . $date);
  $json = json_decode($string, true);
  $list[] = $json;
}
// Setting ical header
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=sun-cal.ics');

// Define helper functions
function dateToCal($timestamp) {
  return date('Ymd\THis\Z', strtotime($timestamp));
}
function dayToCal($timestamp) {
  return date('Ymd', strtotime($timestamp));
}
function nextDayToCal($timestamp) {
  return date('Ymd', strtotime('+1 day', strtotime($timestamp)));
}
function makeDescriptions($data) {
  $desc = '🌅 Sunrise ' . date("G:i", strtotime($data['sunrise'])) . '\n';
  $desc .= '🌇 Sunset ' . date("G:i", strtotime($data['sunset'])) . '\n\n';
  $desc .= '🙌 Thanks for using Sun in Your Calendar, please consider supporting sun.maxmichels.de';

  return $desc;
}
function makeTitle($data) {
  $title = "↑SR " . date('G:i', strtotime($data['sunrise']));
  $title .= " / ";
  $title .= "↓SS " . date('G:i', strtotime($data['sunset']));

  return $title;
}

// 3. Echo out the ics file's contents
?>BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//sun.maxmichels.de//v0.1//EN
X-WR-CALNAME:Sun-cal
X-APPLE-CALENDAR-COLOR:#ffffff
CALSCALE:GREGORIAN
<?php
  // Loop throue all the days
  foreach ($list as $key => $val) {
?>
BEGIN:VEVENT
SUMMARY;LANGUAGE=en:<?= makeTitle($val['results']) . '
'; ?> 
X-FUNAMBOL-ALLDAY:1 
CONTACT:Max Michels\, kontakt@maxmichels.de
UID:<?= dayToCal($val['results']['sunrise']) ?>@sun.maxmichels.de 
DTSTAMP;VALUE=DATE:<?= date('Ymd\THis', time()) . '
' ?>
DTSTART;VALUE=DATE:<?= dayToCal($val['results']['sunrise']) . '
' ?>
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE 
URL;VALUE=URI:http://maxmichels.de 
DTEND;VALUE=DATE:<?= nextDayToCal($val['results']['sunrise']) . '
' ?>
X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC 
DESCRIPTION;LANGUAGE=en:<?= makeDescriptions($val['results']) . '
' ?>
END:VEVENT
<?php
  }
?>
END:VCALENDAR