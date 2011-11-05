<?php
require_once "simple_html_dom.php";
require_once "config.php";

$mail = MAIL_ADDRESS;
$password = PASSWORD;

$param = array(
"mail" => $mail,
"password" => $password,
"next_url" => ""
);

// 特に必要は無いが適当にブラウザのヘッダーを付ける
$headers = array(
"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1",
"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
"Accept-Language: en-us,en;q=0.8,de;q=0.6,ja;q=0.4,id;q=0.2",
"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
"Keep-Alive: 300",
"Connection: keep-alive",
"Referer: http://www.nicovideo.jp/"
);

// curl の初期化
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie");
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie");

// ログインする
curl_setopt($ch, CURLOPT_URL, "https://secure.nicovideo.jp/secure/login?site=niconico");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

$tmp = curl_exec($ch);

// 次のステップの為、ＨＴＴＰメソッドとパラメータをリセットする
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, array());

// セッションを持続させるため、curlのハンドラーを再利用する
curl_setopt($ch, CURLOPT_URL, "http://live.nicovideo.jp/my?mypage_top");
curl_setopt($ch, CURLOPT_HTTPGET, TRUE);

$str = curl_exec($ch);

curl_close($ch);

//Cookieの削除
unlink('./cookie');

//DOM解析部分
$html = str_get_html($str);

$calendar = array();

foreach ($html->find('div[class=column]') as $column) {
    foreach($column->find('div[class=name] a') as $name) { 
        $title = $name->innertext; 
    } 
    foreach($column->find('div[class=status] span') as $status) { 
        preg_match('|\[(\d{4})/(\d{2})/(\d{2}).*?(\d{2}):(\d{2})|', $status->innertext, $matches); 
        $date = array('yy' => $matches[1],
                      'mm' => $matches[2],
                      'dd' => $matches[3],
                      'hh' => $matches[4],
                      'ii' => $matches[5]
                  );
    } 
    $calendar[] = array('title' => $title, 'date' => $date);
}

//iCalendar
$ical = <<<ICAL
BEGIN:VCALENDAR
PRODID:-//nicovideo//time shift list//
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:タイムシフト予約リスト
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

//データを読み込みこんで加える
foreach ($calendar as $event) {
    list($yy, $mm, $dd, $hh, $ii) = array_values($event['date']); 
    $dtstart = gmdate("Ymd\THi00\Z", mktime($hh, $ii, 0, $mm, $dd, $yy));
    //1時間後に終了とする
    $dtend = gmdate("Ymd\THi00\Z", mktime($hh, $ii, 0, $mm, $dd, $yy) + 60 * 60);
    $dtstamp = gmdate("Ymd\THi00\Z");
    $uid = uniqid();

    $ical .= <<<ICAL
BEGIN:VEVENT
DTSTART:$dtstart
DTEND:$dtend
DTSTAMP:$dtstamp
UID:$uid
CREATED:$dtstamp
DESCRIPTION:
LAST-MODIFIED:$dtstamp
LOCATION:
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:{$event['title']}
TRANSP:OPAQUE
END:VEVENT

ICAL;
}

$ical .= 'END:VCALENDAR';

header('Content-Type: text/calendar; charset=utf-8'); 
echo $ical; 
