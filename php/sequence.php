<?php // sequence.php

  class Sequence {
    var $tokens;
    var $frequency;

    function Sequence ($tokens, $frequency) {
      $this->tokens = $tokens;
      $this->frequency = $frequency;
    }

    function toString() {
      $first = true;
      $result = "";
      foreach ($this->tokens as $token) {
        if ($first) {
          $result = $token;
          $first = false;
        } else {
          $result .= "." . $token;
        }
      }
      return $result;
    }
  }

  function sequences ($tokens) {
    $sequences = array();

    $length = count($tokens);
    for ($i = 0; $i < $length; ++$i) {
      $remaining = $length - $i;

      for ($slen = 1; $slen < $remaining; ++$slen) {
        $checkNext = false;

        // See if we have already recorded the segment 
        $recorded = false;

        if (isset($sequences[$slen])) {
          foreach ($sequences[$slen] as $sequence) {
            $same = true;
            for ($ri = 0; $ri < $slen; ++$ri) {
              if ($tokens[$i + $ri] != $sequence->tokens[$ri]) {
                $same = false;
                break;
              }
            }

            if ($same) {
              $recorded = true;
              $checkNext = true;
              break;
            }
          }
        }

        // If not, count instances
        if (!$recorded) {
          $frequency = 1;

          $limit = $length - $slen + 1;
          for ($oi = $i + $slen; $oi < $limit; ++$oi) {
            $same = true;
            for ($ri = 0; $ri < $slen; ++$ri) {
              if ($tokens[$oi + $ri] != $tokens[$i + $ri]) {
                $same = false;
                break;
              }
            }

            if ($same) {
              ++$frequency;
            }
          }

          if ($frequency > 1) {
            if (empty($sequences[$slen])) {
              $sequences[$slen] = array();
            }
            $sequences[$slen][] = new Sequence(array_slice($tokens, $i, $slen), $frequency);
            $checkNext = true;
          }
        }

        if (!$checkNext) {
          break;
        }
      }
    }

    return $sequences;
  }
?>
