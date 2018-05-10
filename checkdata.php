<?php
  $content = file_get_contents('checkdata.php');
  $events = json_decode($content, true);
  if (!is_null($events)) {
    echo "fern";
  }else{
    echo "pee";
  }
?>
