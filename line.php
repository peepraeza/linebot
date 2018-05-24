 <?php
  

function send_LINE($msg, $user){
 $access_token = 'QxqfPl7Wv/Ua1TLaRbdeHE0eWShYOTMrwL5uyzyL7hhDYxsKdZTIStr9Gm/abUS9Yzrl60Kn5fyuEL51+NjNinoVY7oOcS6LDQpym5mDQXM09tBEQn8f25Oer+ktAIWk48UyYmAXt9pUjBchHDdfBQdB04t89/1O/w1cDnyilFU='; 



      // Make a POST Request to Messaging API to reply to sender
      $url = 'https://api.line.me/v2/bot/message/push';
      $data = [

        'to' => $user,
        'messages' => [$msg],
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
