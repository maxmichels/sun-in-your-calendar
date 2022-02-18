<?php
// Loading variables from URL
if (isset($_GET['lat'])) {
  $lat = $_GET['lat'];
} else {
  $lat = 0;
}
if (isset($_GET['lon'])) {
  $lon = $_GET['lon'];
} else {
  $lon = 0;
}
if (isset($_GET['days'])) {
  $days = $_GET['days'];
  // allow maxium 60 days to prevent timeout for webcal
  if($days > 60) {
    $days=60;
  }
} else {
  $days = 30;
}
if (isset($_GET['detailedDesc'])) {
  $detailedDesc = $_GET['detailedDesc'];
} else {
  $detailedDesc = 0;
}
$erroroutput = false;

// Loading json
$list = array();
if($lat===0 | $lon===0) {
  $erroroutput = true;
} else {
  for ($i=0; $i < $days; $i++) {
    $date = date("Y-m-d", strtotime("+$i days"));
    $string = file_get_contents("https://api.sunrise-sunset.org/json?formatted=0&lat=" . $lat . "&lng=" . $lon . "&date=" . $date);
    $json = json_decode($string, true);
    $list[] = $json;
  }
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
  global $lat, $lon, $detailedDesc;
  $desc = '🌅 Sunrise ' . date("G:i", strtotime($data['sunrise'])) . '\n';
  $desc .= '🌇 Sunset ' . date("G:i", strtotime($data['sunset'])) . '\n\n';
  if($detailedDesc = 1) {
    $desc .= 'Lentgh of day ' . sprintf('%02d:%02d:%02d', ($data['day_length']/ 3600),($data['day_length']/ 60 % 60), $data['day_length']% 60) . '\n\n';
    $desc .= 'Solar noon ' . date("G:i", strtotime($data['solar_noon'])) . '\n';
    $desc .= 'Civil twilight begin ' . date("G:i", strtotime($data['civil_twilight_begin'])) . '\n';
    $desc .= 'Civil twilight end ' . date("G:i", strtotime($data['civil_twilight_end'])) . '\n';
    $desc .= 'Nautical twilight begin ' . date("G:i", strtotime($data['nautical_twilight_begin'])) . '\n';
    $desc .= 'Nautical twilight end ' . date("G:i", strtotime($data['nautical_twilight_end'])) . '\n';
    $desc .= 'Astronomical twilight begin ' . date("G:i", strtotime($data['astronomical_twilight_begin'])) . '\n';
    $desc .= 'Astronomical twilight end ' . date("G:i", strtotime($data['astronomical_twilight_end'])) . '\n\n';
  }
  
  $desc .= 'Sun data for Lat: ' . $lat . ' Lon: ' . $lon .  '\n\n';
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
if (!$erroroutput) {
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
  } ?>
END:VCALENDAR
<?php
} else {?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//sun.maxmichels.de//v0.1//EN
X-WR-CALNAME:Sun-cal
X-APPLE-CALENDAR-COLOR:#ffffff
CALSCALE:GREGORIAN
<?php
  // Loop throue all the days
  for ($i=0; $i < 5; $i++) {
      ?>
BEGIN:VEVENT
SUMMARY;LANGUAGE=en:<?= 'WRONG LOCATION' . '
'; ?> 
X-FUNAMBOL-ALLDAY:1 
CONTACT:Max Michels\, kontakt@maxmichels.de
UID:<?= $i; ?>@sun.maxmichels.de 
DTSTAMP;VALUE=DATE:<?= date('Ymd\THis', time()) . '
' ?>
DTSTART;VALUE=DATE:<?= dayToCal(date("y-m-d", strtotime("+$i days"))) . '
' ?>
<?php if ($location == 'show') { ?>
<?= 'LOCATION:' . $json['city']['name'] . '
' ?> 
<?php } ?>
X-MICROSOFT-CDO-ALLDAYEVENT:TRUE 
URL;VALUE=URI:http://maxmichels.de 
DTEND;VALUE=DATE:<?= nextDayToCal(date("Y-m-d", strtotime("+$i days"))) . '
' ?>
X-APPLE-TRAVEL-ADVISORY-BEHAVIOR:AUTOMATIC 
DESCRIPTION;LANGUAGE=en:<?= 'WRONG INPUT. Please check your parameters of the calendar. http://sun.maxmichels.de' . '
' ?>
END:VEVENT
<?php
  } ?>
END:VCALENDAR
<?php
}