<?php // functions.php
  function array_mean(&$array) {
    return array_sum($array) / count($array);
  }

  function array_stdev(&$array) {
    $mean = array_mean($array);

    $t = 0;
    $c = 0;
    foreach ($array as $element) {
      $deviation = $element - $mean;
      $t += $deviation * $deviation;
      ++$c;
    }

    if ($c > 0) {
      return sqrt(1.0 * $t / count($array));
    } else {
      return 0;
    }
  }
?>
