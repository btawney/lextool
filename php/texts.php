<?php // texts.php
  require_once('codepage.php');
  require_once('tokens.php');

  function getStructuredText($name) {
    if (file_exists("./data/$name.dat")) {
      $data = file_get_contents("./data/$name.dat");
      return unserialize($data);
    }
  }
?>
