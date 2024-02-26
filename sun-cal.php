<?php
require_once './api_key.php'; // Get a API Key at https://openweathermap.org/appid

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
if (isset($_GET['city'])) {
  $location = $_GET['city'];
} else {
  $location = "";
}
if (isset($_GET['days'])) {
  $days = $_GET['days'];
  // allow maxium 60 days to prevent timeout for webcal
  if($days > 60) {
    $days=60;
  }
  if ($days<1) {
    $days=1;
  }
} else {
  $days = 30;
}
if (isset($_GET['detailedDesc'])) {
  $detailedDesc = $_GET['detailedDesc'];
} else {
  $detailedDesc = 0;
}
if (isset($_GET['localTime'])) {
  $localTime = $_GET['localTime'];
} else {
  $localTime = 0;
}
$timezoneUntil = 0;
$timeShift = 0;
$erroroutput = false;

// get location once
getLocation();

// Loading json
$list = array();
if(($lat===0 | $lon===0) | $location==="") {
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
function getTime($timestamp) {
  global $localTime, $timezoneUntil, $lat, $lon, $timeShift, $api_key_timezonedb;
  if($localTime == 1) {
    if ( strtotime($timestamp) > $timezoneUntil) {
      $string = file_get_contents('http://api.timezonedb.com/v2.1/get-time-zone?key=' . $api_key_timezonedb . '&format=json&by=position&lat=' . $lat . '&lng=' . $lon . '&time=' . strtotime($timestamp));
      $json = json_decode($string, true);
      $timezoneUntil = $json['zoneEnd'];
      $timeShift = $json['gmtOffset'];
    }
    $time = date("H:i", strtotime("$timeShift seconds", strtotime($timestamp)));
    $timeText = $time . '\n';
  } else {
    $timeText = date("H:i", strtotime($timestamp)) . 'Z\n';
  }
  return $timeText;
}
function makeDescriptions($data) {
  global $lat, $lon, $detailedDesc;
  $desc = '🌅 Sunrise ' . getTime($data['sunrise']) . '\n';
  $desc .= '🌇 Sunset ' . getTime($data['sunset']) . '\n\n';
  if($detailedDesc == 1) {
    $desc .= 'Lentgh of day ' . sprintf('%02d:%02d:%02d', ($data['day_length']/ 3600),((int)($data['day_length']/ 60) % 60), $data['day_length']% 60) . '\n\n';
    $desc .= 'Solar noon ' . getTime($data['solar_noon']) . '\n';
    $desc .= 'Civil twilight begin ' . getTime($data['civil_twilight_begin']) . '\n';
    $desc .= 'Civil twilight end ' . getTime($data['civil_twilight_end']) . '\n';
    $desc .= 'Nautical twilight begin ' . getTime($data['nautical_twilight_begin']) . '\n';
    $desc .= 'Nautical twilight end ' . getTime($data['nautical_twilight_end']) . '\n';
    $desc .= 'Astronomical twilight begin ' . getTime($data['astronomical_twilight_begin']) . '\n';
    $desc .= 'Astronomical twilight end ' . getTime($data['astronomical_twilight_end']) . '\n\n';
  }
  
  $desc .= 'Sun data for Lat: ' . $lat . ' Lon: ' . $lon .  '\n\n';
  $desc .= '🙌 Thanks for using Sun in Your Calendar, please consider supporting sun.maxmichels.de';

  return $desc;
}
function makeTitle($data) {
  $title = "↑SR " . getTime($data['sunrise']);
  $title .= " / ";
  $title .= "↓SS " . getTime($data['sunset']);

  return $title;
}
function getLocation(){
  global $lat, $lon, $location;
  // OSM require header
  $opts = array('http'=>array('header'=>"User-Agent: sun-in-your-calendar\r\n"));
  $context = stream_context_create($opts);

  if( ($lat===0 | $lon===0) ) {
    $string="https://nominatim.openstreetmap.org/search?&city=" . $location . "&format=jsonv2&polygon_geojson=0&addressdetails=1&limit=1";
    $result = file_get_contents($string, false, $context);
    $json = json_decode($result, true);
    $lat = $json['0']['lat'];
    $lon = $json['0']['lon'];
    $location = $json['0']['address']['city'];
  } elseif($lat!=0 & $lon!=0) {
    $string = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" . $lat . "&lon=" . $lon;
    $result = file_get_contents($string, false, $context);
    $json = json_decode($result, true);
    $location = $json['address']['city'];
  }
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
<?php if ($location !== '') { ?>
<?= 'LOCATION:' . $location . '
' ?> 
<?php } ?>
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