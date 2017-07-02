<?php // tools.php
  require_once('singles.php');
  require_once('pairs.php');

  class ContextComparer {
    var $singles;
    var $pairs;
    var $pairLookup;
    var $comparison;

    function ContextComparer ($tokens) {
      $this->singles = singles($tokens);
      $this->pairs = pairs($tokens);
      $this->pairLookup = pairLookupArray($this->pairs);
      $this->comparison = array();
    }

    function compare($t1, $t2) {
      if ($t1 > $t2) {
        return $this->compare($t2, $t1);
      }

      if (isset($this->comparison[$t1])) {
        if (isset($this->comparison[$t1][$t2])) {
          return $this->comparison[$t1][$t2];
        }
      } else {
        $this->comparison[$t1] = array();
      }

      $difference = 0;
      $freq1 = $this->singles[$t1]->frequency;
      $freq2 = $this->singles[$t2]->frequency;

      foreach ($this->pairLookup[$t1] as $second => $pair1) {
        if (isset($this->pairLookup[$t2][$second])) {
          $pair2 = $this->pairLookup[$t2][$second];
          $difference += abs(
            $pair1->frequency * 1.0 / $freq1
            - $pair2->frequency * 1.0 / $freq2
          );
        } else {
          $difference += $pair1->frequency * 1.0 / $freq1;
        }
      }

      foreach ($this->pairLookup[$t2] as $second => $pair2) {
        if (empty($this->pairLookup[$t1][$second])) {
          $difference += $pair2->frequency * 1.0 / $freq2;
        }
      }

      $similarity = 1 - $difference / 4.0;

      $this->comparison[$t1][$t2] = $similarity;

      return $similarity;
    }

    function closest($t) {
      $bestScore = 0;
      $bestToken = '';

      foreach ($this->singles as $single) {
        if ($single->token != $t) {
          $similarity = $this->compare($t, $single->token);
          if ($similarity > $bestScore) {
            $bestScore = $similarity;
            $bestToken = $single->token;
          }
        }
      }

      return $bestToken;
    }

    function neighbors($t) {
      $fs = array();

      foreach ($this->singles as $single) {
        if ($single->token != $t) {
          $similarity = $this->compare($t, $single->token);

          if ($similarity > 0) {
            if (empty($fs[$similarity])) {
              $fs[$similarity] = array();
            }
            $fs[$similarity][] = new Neighbor($single, $similarity);
          }
        }
      }

      krsort($fs);

      $result = array();
      foreach ($fs as $f => $ns) {
        foreach ($ns as $n) {
          $result[] = $n;
        }
      }

      return $result;
    }
  }

  class Neighbor {
    var $token;
    var $frequency;
    var $relativeFrequency;
    var $similarity;

    function Neighbor($single, $similarity) {
      $this->token = $single->token;
      $this->frequency = $single->frequency;
      $this->relativeFrequency = $single->relativeFrequency;
      $this->similarity = $similarity;
    }
  }
?>
