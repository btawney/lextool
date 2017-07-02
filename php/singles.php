<?php // singles.php
  class Single {
    var $token;
    var $frequency;
    var $relativeFrequency;
  }

  function singles($tokens, $limit = false) {
    $lookup = array();
    foreach ($tokens as $token) {
      if (empty($lookup[$token])) {
        $single = new Single();
        $single->token = $token;
        $lookup[$token] = $single;
      }
      ++$lookup[$token]->frequency;
    }

    $tokenCount = count($tokens);

    $byFrequency = array();
    foreach ($lookup as $token => $single) {
      $single->relativeFrequency = $single->frequency * 1.0 / $tokenCount;

      if (empty($byFrequency[$single->frequency])) {
        $byFrequency[$single->frequency] = array();
      }
      $byFrequency[$single->frequency][] = $single;
    }

    krsort($byFrequency);

    $result = array();
    if ($limit === false) {
      foreach ($byFrequency as $frequency => $singles) {
        foreach ($singles as $single) {
          $result[$single->token] = $single;
        }
      }
    } else {
      $remaining = $limit;
      foreach ($byFrequency as $frequency => $singles) {
        foreach ($singles as $single) {
          $result[$single->token] = $single;
          --$remaining;
          if ($remaining == 0) {
            break;
          }
        }
        if ($remaining == 0) {
          break;
        }
      }
    }

    return $result;
  }
?>
