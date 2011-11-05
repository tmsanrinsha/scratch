<?php
//require_once 'gConfig.inc';

// load classes
ini_set('include_path', ini_get('include_path'). ':' . INC_PATH);
require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_Calendar');
Zend_Loader::loadClass('Zend_Http_Client');

//class GCal extends Zend_Gdata_Calendar 
class GCal
{
    //public $gcal;
    private $gcal;
    //private $gCalTitle;
    private $contentSrc;
    private $user;

    function __construct($user, $pass, $gCalTitle){
        // connect to service
        $gcal = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
        $client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $gcal);
        //親クラスのコンストラクタの呼び出し
        //$this->Zend_Gdata_Calendar($client);
        $this->gcal = new Zend_Gdata_Calendar($client);
        //$this->gCalTitle = $gCalTitle;
        
        /* カレンダータイトルが$gCalTitleの"ユーザー名"(カレンダーのID)を取得する */
        // カレンダーリストの取得
        try {
            $listFeed = $this->gcal->getCalendarListFeed();
        } catch (Zend_Gdata_App_Exception $e) {
            echo "エラー: " . $e->getMessage();
        }
 
        /* カレンダータイトルが$gCalTitleの"ユーザー名"(カレンダーのID)を取得する */
        foreach ($listFeed as $list) {
            $calTitle = $list->title;
            if($calTitle == $gCalTitle){
                // カレンダーリストのidの取得
                $listId = $list->id;
                //$this->listId = $list->id;
                // カレンダーのユーザー名取得
                $this->user = substr($listId, strrpos($listId, "/") + 1);
                // カレンダーを指定してinsertするために必要
                //こんな感じ
                //http://www.google.com/calendar/feeds/○○○○○/private/full
                $this->contentSrc = $list->content->src;
                //$this->user = substr($this->listId, strrpos($this->listId, "/") + 1);
                break;
            }
        }
    }

    // function view(){ 
    //     // カレンダーリストの取得 
    //     try { 
    //         $listFeed = $this->gcal->getCalendarListFeed(); 
    //     } catch (Zend_Gdata_App_Exception $e) { 
    //         echo "エラー: " . $e->getMessage(); 
    //     } 
 
    //     foreach ($listFeed as $list) { 
 
    //         // カレンダータイトルの取得 
    //         $calTitle = $list->title; 
    //         if($calTitle == 'Seminar'){ 
    //             // カレンダーリストのidの取得 
    //             $listId = $list->id; 
    //             // カレンダーのユーザー名取得 
    //             $user = substr($listId, strrpos($listId, "/") + 1); 
    //             break; 
    //         } 
    //     } 
 
    //     $query = $this->gcal->newEventQuery(); 
    //     // $query->setUser('default');  
    //     // $user = 'default'; 
    //     $query->setUser($user);  
    //     $query->setVisibility('private');  
    //     //$query->setVisibility('public');   
    //     $query->setProjection('basic'); 
    //     $query->setOrderby('starttime'); 
    //     if(isset($_GET['q'])) { 
    //         $query->setQuery($_GET['q']);       
    //     } 
 
    //     try { 
    //         $feed = $this->gcal->getCalendarEventFeed($query); 
    //     } catch (Zend_Gdata_App_Exception $e) { 
    //         echo "Error: " . $e->getResponse(); 
    //     } 
    //     echo $feed->title; 
    //     // <?php echo $feed->totalResults;  event(s) found.  
    //     // <p/>  
    //     // <ol>  
 
    //     // <?php          
    //     foreach ($feed as $event) { 
    //         echo "<li>\n"; 
    //         echo "<h2>" . stripslashes($event->title) . "</h2>\n"; 
    //         echo stripslashes($event->summary) . " <br/>\n"; 
    //         $id = substr($event->id, strrpos($event->id, '/')+1); 
    //         echo "<a href=\"edit.php?id=$id&user=$user\">edit</a> | "; 
    //         echo "<a href=\"delete.php?id=$id\">delete</a> <br/>\n"; 
    //         echo "</li>\n"; 
    //     } 
    // } 

    function insert($data){

        // validate input
        if (!checkdate($data['sdate_mm'], $data['sdate_dd'], $data['sdate_yy'])) {
            die('ERROR: Invalid start date/time');        
        }
        
        if (!checkdate($data['edate_mm'], $data['edate_dd'], $data['edate_yy'])) {
            die('ERROR: Invalid end date/time');        
        }

        // $title = $data['title']; 
        // $content = $data['content']; 
        $start = date(DATE_ATOM, mktime($data['sdate_hh'], $data['sdate_ii'], 0, $data['sdate_mm'], $data['sdate_dd'], $data['sdate_yy']));
        $end = date(DATE_ATOM, mktime($data['edate_hh'], $data['edate_ii'], 0, $data['edate_mm'], $data['edate_dd'], $data['edate_yy']));
//  
        // construct event object
        // save to server      
        try {
            $event = $this->gcal->newEventEntry();        
            $event->title = $this->gcal->newTitle($data['title']);         
            $event->content = $this->gcal->newContent($data['content']);         
            // $event->title = $this->gcal->newTitle($title);         
            // $event->content = $this->gcal->newContent($content);         
            //whenとwhereはちょっと特殊
            $when = $this->gcal->newWhen();
            $when->startTime = $start;
            $when->endTime = $end;
            $event->when = array($when);        
            $event->where = array($this->gcal->newWhere($data['where']));
            $this->gcal->insertEvent($event, $this->contentSrc);    
        } catch (Zend_Gdata_App_Exception $e) {
            echo "Error: " . $e->getResponse();
        }
    }
}
