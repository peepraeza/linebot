 <?php
 function pubMqtt($topic,$msg){
    $APPID= "CarDetector/"; //enter your appid
    $KEY = "sEAqqagxfG4wa25"; //enter your key
    $SECRET = "yqkd687CFVMTqJv6P0wxCoUPj"; //enter your secret
    $Topic = "$topic"; 
      put("https://api.netpie.io/topic/CarDetector/NodeMCU1?retain",$msg);
 
  }
 function getMqttfromlineMsg($Topic,$lineMsg){
 
    $pos = strpos($lineMsg, ":");
    if($pos){
      $splitMsg = explode(":", $lineMsg);
      $topic = $splitMsg[0];
      $msg = $splitMsg[1];
      pubMqtt($topic,$msg);
    }else{
      $topic = $Topic;
      $msg = $lineMsg;
      pubMqtt($topic,$msg);
    }
  }
 
 function put($url,$tmsg)
{
      
    $ch = curl_init($url);
 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
     
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $tmsg);
 
    curl_setopt($ch, CURLOPT_USERPWD, "sEAqqagxfG4wa25:yqkd687CFVMTqJv6P0wxCoUPj");
     
    $response = curl_exec($ch);
     
    curl_close ($ch);
     
    return $response;
}
// $Topic = "NodeMCU1";
 //$lineMsg = "CHECK";
 //getMqttfromlineMsg($Topic,$lineMsg);
?>
