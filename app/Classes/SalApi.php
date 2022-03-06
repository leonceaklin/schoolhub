<?php
namespace App\Classes;
use \DomDocument;


class SalApi{

  static $baseUrl = /*"https://webhook.site/4bf24d2f-f0e7-4e15-9ea2-f96595f9d33a/";*/"https://sal.portal.bl.ch/";

  public $origin = "https://sal.portal.bl.ch";

  public $cookies = [];
  public $school = "";

  public $id = "";
  public $transid = "";

  public $authenticated = false;

  public function __construct(){
    $internalErrors = libxml_use_internal_errors(true);
  }

  public function getSubjects(){
    $result = $this->request("?pageid=21311");
    //$result = file_get_contents(__DIR__."/test.txt");
    $dom = new DomDocument();
    $dom->loadHTML($result);

    if(!$dom){
      return;
    }

    $table = $dom->getElementsByTagName("main")[0]->getElementsByTagName("table")[0];

    $table->normalize();
    $subjects = [];

    $subjTrI = 0;

    $needsGrades = false;
    $isNewSubject = false;

    foreach($table->childNodes as $subjTr){
      if($subjTr->nodeName == "tr"){
      if($subjTrI > 1){
        $isNewSubject = false;
        $subjectNameTd = $subjTr->getElementsByTagName('td')->item(0);

        $subjectIdentifier = $subjectNameTd->childNodes->item(0);

        if(!empty(trim($subjectIdentifier->nodeValue))){
          $subject = (object) [];
          $isNewSubject = true;
          $needsGrades = true;
          $subject->identifier = $subjectIdentifier->nodeValue;
          $subjectNameTd->removeChild($subjectIdentifier);
          $subject->name = $subjectNameTd->nodeValue;
          $grades = [];
        }

        if(!$isNewSubject && $needsGrades && !empty(trim($subjTr->nodeValue))){
          $needsGrades = false;
          $grades = [];
          $gradeTable = $subjTr->getElementsByTagName("table")[0];
          $gradeTrI = 0;
          foreach($gradeTable->getElementsByTagName("tr") as $gradeTr){
            if($gradeTrI > 0 && $gradeTrI != sizeof($gradeTable->childNodes) - 1){
              $grade = (object)[];

              $nameStr = trim($gradeTr->getElementsByTagName("td")[1]->nodeValue);
              if(!empty($nameStr)){
                $grade->name = $nameStr;
              }

              $valueElement = $gradeTr->getElementsByTagName("td")[2];
              $valueElI = 0;
              if($valueElement != null){
                foreach($valueElement->childNodes as $n){
                  if($valueElI == 0){
                    $valueStr = trim($n->nodeValue);
                  }
                  $valueElement->removeChild($n);
                }

                $pointsExplode = explode(":", $valueElement->textContent);
                if(isset($pointsExplode[1])){
                  $pointsStr = trim($pointsExplode[1]);
                }

                if(!empty($valueStr)){
                  $grade->value = round(floatval($valueStr), 2);
                }

                if(!empty($pointsStr)){
                  $grade->points = round(floatval($pointsStr), 2);
                }

                $weightElement = $gradeTr->getElementsByTagName("td")[3];
                if(isset($weightElement)){
                  $weightStr = trim($weightElement->nodeValue);
                }
                if(!empty($weightStr)){
                  $grade->weight = floatval($weightStr);
                }

                $dateStr = trim($gradeTr->getElementsByTagName("td")[0]->nodeValue);
                if(!empty($dateStr)){
                  $pDate = date_parse_from_format("d.m.Y", $dateStr);
                  $grade->date = date("Y-m-d",strtotime($pDate["year"]."-".$pDate["month"]."-".$pDate["day"]));
                }

                $grades[] = $grade;
              }
            }
            $gradeTrI++;
          }
          }

          if(!isset($grades) || sizeof($grades) > 0){
            $subject->grades = $grades;
          }
        }

        if($isNewSubject){
          $subjects[] = $subject;
        }
      }
      $subjTrI++;
    }

    $this->subjects = $subjects;

    return $subjects;
  }

  public function getAbsenceInformation(){
    $result = $this->request("?pageid=21111&action=toggle_abs_showall");

    $dom = new DomDocument();
    $dom->loadHTML($result);

    if(!$dom){
      return;
    }

    $absTable = $dom->getElementsByTagName("main")[0]->getElementsByTagName("table")[0];

    $absTable->normalize();
    $absences = [];

    $absTrs = $absTable->getElementsByTagName("tr");

    $absCount = 0;
    $trI = 0;
    foreach($absTrs as $tr){
      if(sizeof($tr->childNodes) < 7){
        $absCount = $trI - 1;
        break;
      }
      $trI++;
    }

    $periods = [];

    //Current Period
    $period = (object)["absences" => (array)[]];
    $pPointsTr = $absTrs[$absCount + 2];
    $pPointTds = $pPointsTr->getElementsByTagName("td");

    $pDateTr = $absTrs[$absCount + 3];
    $pDateTds = $pDateTr->getElementsByTagName("td");

    $initialQuotaStr = trim($pPointTds[2]->childNodes[0]->nodeValue);

    $remainingQuotaStr = trim($pPointTds[2]->childNodes[2]->nodeValue);


    if(!empty($initialQuotaStr)){
      $period->initial_quota = round(floatval($initialQuotaStr), 2);
    }

    if(!empty($remainingQuotaStr)){
      $period->remaining_quota = round(floatval($remainingQuotaStr), 2);
    }

    $pStartStr = ""; $pEndStr = "";

    $dateParseStr = explode(")",explode("(", ($pDateTds[0]->nodeValue))[2])[0];
    if($dateParseStr){
      $pDateParts = explode("-", $dateParseStr);
      $pStartStr = $pDateParts[0];
      $pEndStr = $pDateParts[1];
    }

    if(!empty($pStartStr)){
      $pDate = date_parse_from_format("d.m.Y", $pStartStr);
      $period->start = date("Y-m-d",strtotime($pDate["year"]."-".$pDate["month"]."-".$pDate["day"]));
    }

    if(!empty($pEndStr)){
      $pDate = date_parse_from_format("d.m.Y", $pEndStr);
      $period->end = date("Y-m-d",strtotime($pDate["year"]."-".$pDate["month"]."-".$pDate["day"]));
    }


    //Absences
    $absTrI = 0;

    foreach($absTrs as $absTr){
      $absence = (object)[];
      if($absTrI > 0){
        $tds = $absTr->getElementsByTagName("td");
        $inCurrentPeriod = false;

        $startStr = trim($tds[0]->nodeValue);
        if(!empty($startStr)){
          $pDate = date_parse_from_format("d.m.Y", $startStr);
          $absence->start = date("Y-m-d",strtotime($pDate["year"]."-".$pDate["month"]."-".$pDate["day"]));
        }

        if(!isset($tds[1])){
          //This Absence doesn't have an end? Weird…
          continue;
        }

        $endStr = trim($tds[1]->nodeValue);
        if(!empty($endStr)){
          $pDate = date_parse_from_format("d.m.Y", $endStr);
          $absence->end = date("Y-m-d",strtotime($pDate["year"]."-".$pDate["month"]."-".$pDate["day"]));
        }

        if(isset($tds[2])){
          $reasonStr = trim($tds[2]->nodeValue);
          if(!empty($reasonStr)){
            $absence->reason = $reasonStr;
          }
        }


        if(isset($tds[3])){
          $pointsStr = trim($tds[3]->nodeValue);
          if(!empty($pointsStr)){
            if(sizeof(explode("*", $pointsStr)) == 1){
              $inCurrentPeriod = true;
            }
            $absence->points = round(floatval($pointsStr), 2);
          }
        }

        if($absence->start == "1970-01-01" || $absence->end == "1970-01-01"){
          continue;
        }

        if($absence->start == $period->start || !isset($absence->end)){
          continue;
        }

        $absences[] = $absence;
        if($inCurrentPeriod){
          $period->absences[] = $absence;
        }
      }

      $absTrI++;
    }

    $periods[] = $period;

    $this->absence_periods = $periods;
    $this->absences = $absences;

    return ["absences" => $absences, "absence_periods" => $periods];
  }

  public function getRooms(){
    $view = $this->request("?pageid=22202&eventtype=0_pru");
    $roomsJson = "[".explode("]", explode("var zimmerliste = [", $view)[1])[0]."]";
    $rooms = json_decode($roomsJson);
    $this->rooms = $rooms;
  }

  public function getUser(){
    if(!$this->authenticated){
      return false;
    }

    $result = $this->request("?pageid=1");

    $dom = new DomDocument();
    $dom->loadHTML($result);

    if(!$dom){
      return;
    }

    $info = [];


    $infoTable = $dom->getElementsByTagName("main")[0]->getElementsByTagName("table")[0];
    $rows = $infoTable->getElementsByTagName("tr");

    $i = 0;
    $dataKeys = ["name", "street", "city", "birth", "profile", "hometown", "phone", "mobile"];
    foreach($rows as $row){
      $value = $row->getElementsByTagName("td")[1]->nodeValue;
      $info[$dataKeys[$i]] = $value;
      $i++;
    }

    $info["birth"] = date("Y-m-d", strtotime($info["birth"]));
    $fullcity = explode(" ", $info["city"]);
    $info["zip"] = $fullcity[0];
    array_shift($fullcity);
    $info["city"] = implode(" ", $fullcity);
    $info["username"] = $this->username;


    // Private Mail
    $result = $this->request("?pageid=22500");

    $dom = new DomDocument();
    $dom->loadHTML($result);

    if(!$dom){
      return $info;
    }

    $el = $dom->getElementById("formgroup0")->getElementsByTagName("input")[1]->getAttribute("value");

    $info["private_email"] = $el;

    //Beautiful Name
    $result = $this->request("?pageid=22348");

    $dom = new DomDocument();
    $dom->loadHTML($result);

    if(!$dom){
      return $info;
    }

    $table = $dom->getElementsByTagName('table')[0];
    $rows = $table->getElementsByTagName("tr");
    foreach($rows as $row){
      $columns = $row->getElementsByTagName('td');

      if(!isset($columns[1]) || !isset($columns[2])){
        continue;
      }

      $lname = trim($columns[1]->nodeValue);
      $fname = trim($columns[2]->nodeValue);

      if($lname." ".$fname == $info["name"]){
        $info["first_name"] = $fname;
        $info["last_name"] = $lname;
        $info["class"] = trim($columns[6]->nodeValue);
        break;
      }
    }

    return $info;
  }

  public function getClass($index = 0){

    //Students
    $result = $this->request("?pageid=22348&listindex_s=".$index);

    $dom = new DomDocument();
    $dom->loadHTML($result);

    if(!$dom){
      return $info;
    }

    $students = [];

    $table = $dom->getElementsByTagName('table')[0];
    $rows = $table->getElementsByTagName("tr");

    //Remove header rows
    foreach($rows as $row){

      $columns = $row->getElementsByTagName('td');
      if(!isset($columns[11])){
        continue;
      }

      $student = [];
      $student["birth"] = date("Y-m-d", strtotime(trim($columns[11]->nodeValue)));
      if($student["birth"] == "1970-01-01"){
        continue;
      }
      $student["first_name"] = trim($columns[2]->nodeValue);
      $student["last_name"] = trim($columns[1]->nodeValue);
      $student["class"] = trim($columns[6]->nodeValue);
      $student["street"] = trim($columns[7]->nodeValue);
      $student["zip"] = trim($columns[8]->nodeValue);
      $student["city"] = trim($columns[9]->nodeValue);
      $student["phone"] = trim($columns[10]->nodeValue);
      $students[] = $student;
    }

    return ["name" => $students[0]["class"], "students" => $students];
  }

  public function getEvents(){

    if(!$this->authenticated){
      return false;
    }

    $currentDate = time();
    $from = time()-(3600*24*30*3);
    $to = time()+(3600*24*30*3);

    $this->getRooms();

    $xmlUrl = "scheduler_processor.php?view=grid&curr_date=".date("Y-m-d", $currentDate)."&min_date=".date("Y-m-d", $from)."&max_date=".date("Y-m-d", $to)."&ansicht=schueleransicht&pageid=22202&timeshift=-120";
    $result = $this->request($xmlUrl);

    $dom = new DomDocument();
    $dom->loadXML($result);

    if(!$dom){
      return;
    }

    $events = [];
    $eventEls = $dom->getElementsByTagName("event");

    foreach($eventEls as $eventEl){
      $event = (object)[];

      $startStr = $eventEl->getElementsByTagName("start_date")[0]->nodeValue;
      $event->start = date("Y-m-d H:i:s", strtotime($startStr));

      $endStr = $eventEl->getElementsByTagName("end_date")[0]->nodeValue;
      $event->end = date("Y-m-d H:i:s", strtotime($endStr));

      $eventTypeStr = trim($eventEl->getElementsByTagName("event_type")[0]->nodeValue);
      if($eventTypeStr == "1_pru"){
        $event->type = "test";

        $event->grade = (object)[
          "name" => $eventEl->getElementsByTagName("kommentar")[0]->nodeValue,
          "description" => $eventEl->getElementsByTagName("markierung")[0]->nodeValue
        ];
      }

      $roomKey = $eventEl->getElementsByTagName("zimmer")[0]->nodeValue;
      foreach($this->rooms as $room){
        if($room->key == $roomKey){
          $event->room = $room;
          break;
        }
      }


      if($eventTypeStr == "0_stp"){
        $event->type = "lession";
      }

      if($eventTypeStr == "5_trm"){
        $event->type = "appointment";
      }

      $titleStr = trim($eventEl->getElementsByTagName("text")[0]->nodeValue);
      if(!empty($titleStr)){
        $event->title = $titleStr;
      }

      $classStr = trim($eventEl->getElementsByTagName("klasse")[0]->nodeValue);
      if(!empty($classStr)){
        $event->class = $classStr;
      }

      $teacherStr = trim($eventEl->getElementsByTagName("lehrerkuerzel")[0]->nodeValue);
      if(!empty($teacherStr)){
        $event->teacher = (object)["identifier" => $teacherStr];
        $event->teacher->name = explode(")", explode("(", $eventEl->getElementsByTagName("lehrer_kurs")[0]->nodeValue)[1])[0];
      }

      $substituteStr = trim($eventEl->getElementsByTagName("neuerlehrer")[0]->nodeValue);
      if(!empty($substituteStr) && $substituteStr != "selbständig"){
        $event->substitute = $substituteStr;
        $event->altered = true;
      }

      $subjectIdentifierStr = trim($eventEl->getElementsByTagName("kurskuerzel")[0]->nodeValue);
      if(!empty($subjectIdentifierStr)){
        $event->subject = (object)[
          "identifier" => $subjectIdentifierStr
        ];
      }

      if($substituteStr == "selbständig"){
        $event->no_teacher = true;
        $event->altered = true;
      }

      $markStr = trim($eventEl->getElementsByTagName("markierung")[0]->nodeValue);
      if($markStr == "deleted"){
        $event->deleted = true;
        $event->altered = true;
      }
      if($markStr == "moved"){
        $event->moved = true;
        $event->altered = true;
      }

      $events[] = $event;
    }

    $this->events = $events;

    return $events;
  }

  public function getGradesPDF(){
    return $this->request("?pageid=21311&output=pdf");
  }

  public function getSchools(){
    return json_decode(file_get_contents(base_path()."/resources/json/schools.json"));
  }


  function login($username, $password, $school = "", $ct = 0){
    $this->username = $username;
    $this->password = $password;
    if(!empty($school)){
      $this->school = $school;
    }

    if(empty($this->school)){
      return false;
    }

    if($ct == 0){
      $this->request("?login");
    }
    $loginRes = $this->request("?login", [
        "isiwebuserid" => $username,
        "isiwebpasswd" => $password,
        "submit" => "Submit"
    ], "post");

    $res = $this->request("index.php");

    if(sizeof(explode("eOSP - Login", $res)) == 1){
      $this->authenticated = true;
      return true;
    }
    else{
      if($ct == 2){
        return false;
      }
      return $this->login($username, $password, $school, $ct+1);
    }
  }

  public function logout(){
    $result = $this->request("?pageid=9999");
  }

  function request($endpoint = "", $args = [], $method = "get"){
    $cookieString = $this->getCookieString();
    $postdata = http_build_query($args);
    $method = strtoupper($method);

    $header = [
      'Content-Type: application/x-www-form-urlencoded',
      'Connection: close',
      'Accept-Encoding: gzip, deflate, br',
      'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.93 Safari/537.36',
      'Referer: '.(isset($this->referer) ? $this->referer : self::$baseUrl),
      'Origin: '.$this->origin,
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'Accept-Language: en_US',
      'Host: sal.portal.bl.ch'
    ];

    if(!empty($cookieString)){
      $header[] = 'Cookie: '.$cookieString;
    }

    if($method == "POST"){
      $header[] = 'Content-Length: '.strlen($postdata);
    }

    $options = [
      'method'  => $method,
      'header'  => $header
    ];

    if($method == "POST"){
      $options['content'] = $postdata;
    }

    $opts = array('http' =>
     $options,
     "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>true,
      ),
    );

    $context  = stream_context_create($opts);
    $url = static::$baseUrl.(!empty($this->school) ? $this->school."/" : "").$endpoint;

    if($this->id && $this->transid){
      $addToUrl = "transid=".$this->transid."&id=".$this->id;
      if(strpos("?", $url) !== -1){
        $url .= "&".$addToUrl;
      }
      else{
        $url .= "?".$addToUrl;
      }
    }


    $result = file_get_contents($url, false, $context);

    //print_r($http_response_header);

    $this->setCookies($http_response_header);

    //Get Id and $transid
    $find = explode("let loginUrl = '", $result);
    if(isset($find[1])){
      $idUrl = explode("'", $find[1])[0];
    }

    if(isset($idUrl)){
      $transid = explode("transid=", $idUrl)[1];
      if($transid){
        $this->transid = $transid;
      }

      $id = explode("&", explode("&id=", $idUrl)[1])[0];
      if($id){
        $this->id = $id;
      }
    }

    $this->referer = $url;
    return $result;
  }

  public function getCookieString(){
    $cookieString = "";
    foreach($this->cookies as $cname=>$cdata){
      $cookieString .= $cname."=".$cdata."; ";
    }
    $cookieString = substr(trim($cookieString), 0, -1);
    return $cookieString;
  }

  public function setCookies($http_response_header){
    $alreadySet = [];
    foreach($http_response_header as $header){
      $cq = "Set-Cookie: ";
      if(substr($header, 0, strlen($cq)) === $cq){
        $cookieDataArray = explode("=", explode("Set-Cookie: ", $header)[1]);
        $cookieName = $cookieDataArray[0];
        if(!in_array($cookieName, $alreadySet)){
          $cookieData = explode(";", $cookieDataArray[1])[0];
          $this->cookies[$cookieName] = $cookieData;
          $alreadySet[] = $cookieName;
        }
      }
    }
  }
}
