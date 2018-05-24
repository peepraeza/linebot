<?php
// กรณีต้องการตรวจสอบการแจ้ง error ให้เปิด 3 บรรทัดล่างนี้ให้ทำงาน กรณีไม่ ให้ comment ปิดไป
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 require("pub.php");
 require("line.php");
 // require("test.php");
// include composer autoload
require_once './vendor/autoload.php';
 
// การตั้งเกี่ยวกับ bot
require_once 'bot_setting.php';
 
// กรณีมีการเชื่อมต่อกับฐานข้อมูล
//require_once("dbconnect.php");
 
///////////// ส่วนของการเรียกใช้งาน class ผ่าน namespace
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
//use LINE\LINEBot\Event;
//use LINE\LINEBot\Event\BaseEvent;
//use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\ImagemapActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder ;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
 
// เชื่อมต่อกับ LINE Messaging API
$httpClient = new CurlHTTPClient(LINE_MESSAGE_ACCESS_TOKEN);
$bot = new LINEBot($httpClient, array('channelSecret' => LINE_MESSAGE_CHANNEL_SECRET));
 
// คำสั่งรอรับการส่งค่ามาของ LINE Messaging API
$content = file_get_contents('php://input');
 
// แปลงข้อความรูปแบบ JSON  ให้อยู่ในโครงสร้างตัวแปร array
$events = json_decode($content, true);
if (!is_null($events['ESP'])) {
  $espMessage = $events['ESP'];
  switch ($espMessage) {
    case "notcar":
      $myfile = fopen("testfile.txt", "w");
      fwrite($myfile, "notcar");
      fclose($myfile);
      break;
    case "carout":
      $myfile = fopen("testfile.txt", "w");
      fwrite($myfile, "notcar");
      fclose($myfile);
      $messages = [       
        'type' => 'text',
        'text' => 'car out'
      ];
      send_LINE($messages);
      break;
    case "ready":
      $myfile = fopen("testfile.txt", "w");
      fwrite($myfile, "ready");
      fclose($myfile);
      break;
    case "wait":
      $myfile = fopen("testfile.txt", "w");
      fwrite($myfile, "wait");
      fclose($myfile);

      $messages = [       
        "type" => "template",
        "altText"=> "this is a confirm template",
        "template"=> [
          "type" => "confirm",
          "text"=> "Are you sure?", 
          "actions" => [
            [
              "type"=> "message",
              "label"=> "Yes",
              "text"=> "yes"
            ],
            [
              "type"=> "message",
              "label"=> "No",
              "text"=> "no"
            ]
          ]
        ]     
      ];
      send_LINE($messages);
      break;
    default:
      break;
  }
    
        
}else if(!is_null($events['events'])){ 
    $userMessage = explode("=", $events['events'][0]['message']['text']); 
    $user = $events['events'][0]['source']['type'];
    $user = $events['events'][0]['source'][$user . 'Id'];
    $check_update = "";
    $update = false;
    $status = array();
    // $myfile = fopen("testfile.txt", "r");
    // $check = fgets($myfile);
    // fclose($myfile);
    $data = json_decode(file_get_contents('db.json'), true);
    foreach ($data as $key => $entry) {
	    if ($entry['user'] == $user) {
	        array_push($status, $data[$key]['status']);
	    }
	    if ($entry['status'] == "update") {
	        $check_update = $key;
	    }
	}
    if($userMessage[0]=="add" and $userMessage[1] != ""){
    	// open database and check
    	$db = json_decode(file_get_contents('db.json'),true);
    	if(array_key_exists($userMessage[1], $db)) {
    		if($db[$userMessage[1]]['user'] == ""){
    			$db[$userMessage[1]]['user'] = $user;
	    		$newJsonString = json_encode($db);
				file_put_contents('db.json', $newJsonString);
				$msg = "Add device success!";
    		}else if($check_update == ""){
    			$update = true;
    			$db[$userMessage[1]]['status'] = "update";
    			$newJsonString = json_encode($db);
				file_put_contents('db.json', $newJsonString);
    			$messages = [       
			        "type" => "template",
			        "altText"=> "this is a confirm template",
			        "template"=> [
			          "type" => "confirm",
			          "text"=> "Device already register. Do you want to change user?", 
			          "actions" => [
			            [
			              "type"=> "message",
			              "label"=> "Yes",
			              "text"=> "yes"
			            ],
			            [
			              "type"=> "message",
			              "label"=> "No",
			              "text"=> "no"
			            ]
			          ]
			        ]     
			      ];
    		}else{
    			$msg = "Wait for update device, Please try again";
    		}
		}else{
			$msg = "Error! Not device exist";
		}
    }
    else if($check_update != ""){
    	switch ($userMessage[0]) {
	        case "yes":
	            $db[$check_update]["user"] = $user;
	    		$newJsonString = json_encode($db);
				file_put_contents('db.json', $newJsonString);
				$msg = "Update device success!";
	            break; 
	        case "no":
	            $msg = "Not update device";
	            break;
	        default:
	            $msg = "Error Update device";
	            break;                                      
      	}
    }
    else if(in_array("wait", $status)){
      switch ($userMessage[0]) {
        case "yes":
            $msg = "led off";
            $myfile = fopen("testfile.txt", "w");
            fwrite($myfile, "yes");
            fclose($myfile);
            break; 
        case "no":
            $msg = "no action";
            $myfile = fopen("testfile.txt", "w");
            fwrite($myfile, "no");
            fclose($myfile);
            break;
        default:
            $msg = "error";
            break;                                      
      }
    }else{
      $msg = "no car then no action";
    }
    if($update == false){
	    $messages = [       
	      'type' => 'text',
	      'text' => $msg
	    ];
	}
    send_LINE($messages, $user);
}



?>
