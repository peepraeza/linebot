<?php
  $content = file_get_contents('checkdata.php');
  $events = json_decode($content, true);
  echo $events['test'];
?>
