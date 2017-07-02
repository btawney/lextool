<?php // zipf.php
  require_once('functions.php');
  require_once('singles.php');

  class Zipf {
    var $singles;
    var $scores;
    var $mean;
    var $stdev;

    function Zipf($tokens) {
      $this->_singles = singles($tokens);

      $rank = 0;
      $this->scores = array();
      foreach ($this->_singles as $single) {
        ++$rank;
        $this->scores[$rank] = $rank * $single->frequency;
      }

      $this->mean = array_mean($this->scores);
      $this->stdev = array_stdev($this->scores);
    }
  }

  function idealRelativeZipf($t) {
    static $maxT = 0;
    static $z = array();
    static $inverseMaxT = 0;

    for ($i = $maxT + 1; $i <= $t; ++$i) {
      $inverseMaxT += 1.0 / $i;
      $z[$i] = 1.0 / $inverseMaxT;
    }

    return $z[$t];
  }

  // Among the following functions:
  //   $k: a frequency rank (e.g. 1 for most common, 2 for second most common)
  //   $t: the number of tokens in a token system
  //   $l: the number of tokens in a sample of text
  function oddsOfEncounteringKthTokenNTimesInARow($k, $t, $n) {
    return pow(idealRelativeZipf($t) / $k, $n);
  }

  function oddsOfNotEncounteringKthTokenNTimesInARow($k, $t, $n) {
    return pow(1 - idealRelativeZipf($t) / $k, $n);
  }

  function waysToCombine($a, $b) {
    // (a + b)!/(a! x b!)
    // e.g. (90 + 10)!/(90! x 10!)
    //      (91 92 93 94 95 96 97 98 99) / (1 2 3 4 5 6 7 8 9 9 10)
    if ($a > $b) {
      $larger = $a;
      $smaller = $b;
    } else {
      $larger = $b;
      $smaller = $a;
    }

    $total = $larger + $smaller;
    $result = 1.0;

    for ($i = 1; $i <= $smaller; ++$i) {
      $result *= ($larger + $i) / $i;
    }

    return $result;
  }

  function oddsOfEncounteringKthTokenNTimesInL ($k, $t, $n, $l) {
    $a = oddsOfEncounteringKthTokenNTimesInARow($k, $t, $n);
    $b = oddsOfNotEncounteringKthTokenNTimesInARow($k, $t, $l - $n);
    $c = waysToCombine($n, $l - $n);

    return $a * $b * $c;
  }

  function oddsOfEncounteringAnyTokenNTimesInL ($t, $n, $l) {
    $result = 0;
    for ($k = 1; $k <= $t; ++$k) {
      $result += oddsOfEncounteringKthTokenNTimesInL($k, $t, $n, $l);
    }
    return $result;
  }

  function oddsOfDistribution (&$singles, $t, $l) {
    $k = 0;
    $odds = 1.0;
    foreach ($singles as $single) {
      ++$k;
      $odds *= oddsOfEncounteringKthTokenNTimesInL ($k, $t, $single->frequency, $l);
    }
    return $odds;
  }

  function guessTokenInventory (&$tokens) {
    $l = count($tokens);
    $singles = singles($tokens);
    $minT = count($singles);
    $maxT = $minT + 10;

    $bestOdds = false;
    $bestT = false;
    for ($t = $minT; $t <= $maxT; ++$t) {
      $odds = oddsOfDistribution ($singles, $t, $l);

      if ($bestOdds == false || $bestOdds < $odds) {
        $bestOdds = $odds;
        $bestT = $t;
      }
    }

    return $bestT;
  }
?>
