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
	$db = json_decode(file_get_contents('db.json'), true);
	$device = $events['ESP']['device'];
  $espMessage = $events['ESP']['status'];
  $name = $db['event'][$device]['name'];
  $user = $db['event'][$device]['user'];
  switch ($espMessage) {
    case "notcar":
      $db['event'][$device]['status'] = "notcar";
  		$newJsonString = json_encode($db);
			file_put_contents('db.json', $newJsonString);
      break;
    case "carout":
      $db['event'][$device]['status'] = "notcar";
  		$newJsonString = json_encode($db);
			file_put_contents('db.json', $newJsonString);
      $messages = [       
        'type' => 'text',
        'text' => 'car out'
      ];
      send_LINE($messages, $user);
      break;
    case "ready":
      $db['event'][$device]['status'] = "ready";
  		$newJsonString = json_encode($db);
			file_put_contents('db.json', $newJsonString);
      break;
    case "wait":
      $db['event'][$device]['status'] = "wait";
  		$newJsonString = json_encode($db);
			file_put_contents('db.json', $newJsonString);

      $messages = [       
        "type" => "template",
        "altText"=> "this is a confirm template",
        "template"=> [
          "type" => "confirm",
          "text"=> "(" . $name . ") detect car. Do you want to turn off?", 
          "actions" => [
            [
              "type"=> "message",
              "label"=> "Yes",
              "text"=> "yes=" . $name
            ],
            [
              "type"=> "message",
              "label"=> "No",
              "text"=> "no=" . $name
            ]
          ]
        ]     
      ];
      send_LINE($messages, $user);
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
    $device = "";
    $all_device = array();

    $db = json_decode(file_get_contents('db.json'), true);
    if(array_key_exists($user, $db['buffer'])) {
    	$check_update = $db['buffer'][$user]['mac'];
    }
    foreach ($db['event'] as $key => $entry) {
	    if ($entry['user'] == $user and $entry['name'] == $userMessage[1] and $entry['status'] == "wait") {
	        $device = $key;
	    }
	    if($entry['user'] == $user){
	    	array_push($all_device, $entry['name']);
	    }
		}
    if($userMessage[0]=="add" and $userMessage[1] != "" and $userMessage[2] != "" and $device == ""){
    	if(array_key_exists($userMessage[1], $db['event'])) {
    		if($db['event'][$userMessage[1]]['user'] == ""){
    			if(in_array($userMessage[2], $all_device)){
    				$msg = "name:" . $userMessage[2] ." is exist! Please try again";
    			}else{
	    			$db['event'][$userMessage[1]]['user'] = $user;
	    			$db['event'][$userMessage[1]]['name'] = $userMessage[2];
		    		$newJsonString = json_encode($db);
						file_put_contents('db.json', $newJsonString);
						$msg = "Add device success!";
					}
    		}else if($db['event'][$check_update]['status'] != "update" and $db['event'][$userMessage[1]]['status'] != "update"){
    			$update = true;
    			$db['buffer'][$user] = array("mac" => $userMessage[1], "name" => $userMessage[2]);
    			$db['event'][$userMessage[1]]['status'] = "update";
    			$newJsonString = json_encode($db);
					file_put_contents('db.json', $newJsonString);
    			$messages = [       
			        "type" => "template",
			        "altText"=> "this is a confirm template",
			        "template"=> [
			          "type" => "confirm",
			          "text"=> "Device already register. Do you want to update?", 
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
    else if($userMessage[0] == "test"){
    	$messages = [       
        "type" => "template",
        "altText"=> "this is a confirm template",
        "template"=> [
          "type" => "confirm",
          "text"=> "(device1) detect car. Do you want to turn off?", 
          "actions" => [
            [
              "type"=> "message",
              "label"=> "Yes",
              "text"=> "yes=device1"
            ],
            [
              "type"=> "message",
              "label"=> "No",
              "text"=> "no=device1"
            ]
          ]
        ]     
      ];
    }
    else if($check_update != ""){
    	switch ($userMessage[0]) {
	        case "yes":
	            $db['event'][$check_update]["user"] = $user;
	            $db['event'][$check_update]["name"] = $db['buffer'][$user]['name'];
	            $db['event'][$check_update]['status'] = "";
	            unset($db['buffer'][$user]);
							$msg = "Update device success!";
	            break; 
	        case "no":
		        	$db['event'][$check_update]['status'] = "";
		        	unset($db['buffer'][$user]);
	            $msg = "Not update device";
	            break;
	        default:
	            $msg = "Error Update device";
	            break;                                      
      	}
      	$newJsonString = json_encode($db);
				file_put_contents('db.json', $newJsonString);
    }
    else if($device != ""){
      switch ($userMessage[0]) {
        case "yes":
            $msg = "หยุดการทำงาน";
            $db['event'][$device]['status'] = "yes";
            $newJsonString = json_encode($db);
						file_put_contents('db.json', $newJsonString);
            break; 
        case "no":
            $msg = "no action";
            $db['event'][$device]['status'] = "no";
            $newJsonString = json_encode($db);
						file_put_contents('db.json', $newJsonString);
            break;
        default:
            $msg = "error";
            break;                                      
      }
    }else{
      $msg = "action fail";
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
