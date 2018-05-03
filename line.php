 <?php
  

function send_LINE($msg){
 $access_token = 'sz09DKTKxpnv8yBI0Zg3TKAMVuN2n20jwgn78U5dZJTBNreZZ1804JcPxAZ+wkTnYzrl60Kn5fyuEL51+NjNinoVY7oOcS6LDQpym5mDQXNJzdm5vqgbQzM3OmHNMJXUQzOSDbHEaUgXOMM5dL8TywdB04t89/1O/w1cDnyilFU='; 

  $messages = [
        'type' => 'text',
        'text' => $msg
        //'text' => $text
      ];

      // Make a POST Request to Messaging API to reply to sender
      $url = 'https://api.line.me/v2/bot/message/push';
      $data = [

        'to' => 'U1b2f37d8c004ddc93f7bb76bda2877b4',
        'messages' => [$messages],
      ];
      $post = json_encode($data);
      $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      $result = curl_exec($ch);
      curl_close($ch);

      echo $result . "\r\n"; 
}

?>
