<?php // letters.php
  function tokensToLetters(&$tokens) {
    $letters = array();
    foreach ($tokens as $token) {
      $length = mb_strlen($token);
      for ($i = 0; $i < $length; ++$i) {
        $letters[] = mb_substr($token, $i, 1);
      }
    }
    return $letters;
  }
?>
