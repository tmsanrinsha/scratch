<?php
setlocale(LC_ALL,"ja_JP.UTF-8");//これが無いとfgetcsvの日本語がおかしくなる
require_once 'config.php';
$title = TITLE;
$ical = <<<ICAL
BEGIN:VCALENDAR
PRODID:-//TokyoTech//${title}//
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:$title
X-WR-TIMEZONE:Asia/Tokyo
BEGIN:VTIMEZONE
TZID:Asia/Tokyo
X-LIC-LOCATION:Asia/Tokyo
BEGIN:STANDARD
TZOFFSETFROM:+0900
TZOFFSETTO:+0900
TZNAME:JST
DTSTART:19700101T000000
END:STANDARD
END:VTIMEZONE

ICAL;

$filename = LOGDIR ."/log.csv";
//データを読み込みこんで加える
if(@$fp = fopen($filename,"r")){
    while (($csv = fgetcsv($fp, 0, ",",'"')) !== FALSE) {
        list($yy, $mm, $dd) = explode('/', $csv[3]);
        list($hh, $ii) = explode(':', $csv[4]);
        $dtstart = gmdate("Ymd\THi00\Z", mktime($hh, $ii, 0, $mm, $dd, $yy));
        list($hh, $ii) = explode(':', $csv[5]);
        $dtend = gmdate("Ymd\THi00\Z", mktime($hh, $ii, 0, $mm, $dd, $yy));
        $dtstamp = gmdate("Ymd\THi00\Z");
        $location = $csv[6];
        $summary = $csv[7];  
        if($summary != ''){
            $summary .= '(' . $csv[8] . ')@TokyoTech';
        }
        $description = $csv[2];

        $ical .= <<<ICAL
BEGIN:VEVENT
DTSTART:$dtstart
DTEND:$dtend
DTSTAMP:$dtstamp
UID:$csv[0]
CREATED:$dtstamp
DESCRIPTION:$csv[2]
LAST-MODIFIED:$dtstamp
LOCATION:$csv[6]
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:$csv[7]
TRANSP:OPAQUE
END:VEVENT

ICAL;
    }
    fclose($fp);
}

$ical .= 'END:VCALENDAR';

//file_put_contents( LOGDIR . '/' . strtolower(TITLE) . '.ics', $ical);
header('Content-Type: text/calendar; charset=utf-8'); 
echo $ical; 
