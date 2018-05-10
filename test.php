 <?php
  

function fern($msg){
  // where are we posting to?
  $url = 'checkdata.php';

  // what post fields?
  $data = [
     'test' => "no"
  ];

  // build the urlencoded data
  $ch = curl_init('checkdata.php');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

  $result = curl_exec($ch);
}
?>
