<?php // tools.php
  require_once('singles.php');

  class Pair {
    var $first;
    var $second;
    var $frequency;
    var $reverseFrequency;
    var $relativeFrequency;
    var $reverseRelativeFrequency;
    var $firstFrequency;
    var $firstRelativeFrequency;
    var $secondFrequency;
    var $secondRelativeFrequency;

    function compare($other) {
      return
        abs($this->relativeFrequency - $other->relativeFrequency)
        + abs($this->firstRelativeFrequency - $other->firstRelativeFrequency)
        + abs($this->secondRelativeFrequency - $other->secondRelativeFrequency)
        + abs($this->reverseRelativeFrequency - $other->reverseRelativeFrequency)
        ;
    }

    function compare2($other) {
      return
        abs($this->relativeFrequency - $other->relativeFrequency)
        + abs($this->reverseRelativeFrequency - $other->reverseRelativeFrequency)
        ;
    }
  }

  function pairs($tokens, $singles = false) {
    $tokenFrequency = array();
    $tokenCount = count($tokens);
    foreach ($tokens as $token) {
      if (empty($tokenFrequency[$token])) {
        $tokenFrequency[$token] = 1;
      } else {
        ++$tokenFrequency[$token];
      }
    }

    $firsts = array();
    $lastToken = false;

    foreach ($tokens as $token) {
      if ($lastToken !== false) {
        if (empty($firsts[$lastToken])) {
          $firsts[$lastToken] = array();
        }
        if (empty($firsts[$lastToken][$token])) {
          $pair = new Pair();
          $pair->first = $lastToken;
          $pair->firstFrequency = $tokenFrequency[$lastToken];
          $pair->firstRelativeFrequency = $pair->firstFrequency * 1.0 / $tokenCount;
          $pair->second = $token;
          $pair->secondFrequency = $tokenFrequency[$token];
          $pair->secondRelativeFrequency = $pair->secondFrequency * 1.0 / $tokenCount;
          $firsts[$lastToken][$token] = $pair;
        }
        ++$firsts[$lastToken][$token]->frequency;
      }
      $lastToken = $token;
    }

    $byFrequency = array();
    foreach ($firsts as $first => $seconds) {
      foreach ($seconds as $second => $pair) {
        $pair->relativeFrequency = $pair->frequency * 1.0 / $tokenCount;

        if (isset($firsts[$second][$first])) {
          $reverse = $firsts[$second][$first];
          $pair->reverseFrequency = $reverse->frequency;
        } else {
          $pair->reverseFrequency = 0;
        }
        $pair->reverseRelativeFrequency = $pair->reverseFrequency * 1.0 / $tokenCount;

        if (empty($byFrequency[$pair->frequency])) {
          $byFrequency[$pair->frequency] = array();
        }
        $byFrequency[$pair->frequency][] = $pair;
      }
    }

    krsort($byFrequency);

    $result = array();
    if ($singles === false) {
      foreach ($byFrequency as $frequency => $pairs) {
        foreach ($pairs as $pair) {
          $result[] = $pair;
        }
      }
    } else {
      foreach ($byFrequency as $frequency => $pairs) {
        foreach ($pairs as $pair) {
          if (isset($singles[$pair->first]) && isset($singles[$pair->second])) {
            $result[] = $pair;
          }
        }
      }
    }

    return $result;
  }

  function pairLookupArray($pairs) {
    $result = array();
    foreach ($pairs as $index => $pair) {
      if (empty($result[$pair->first])) {
        $result[$pair->first] = array();
      }
      $result[$pair->first][$pair->second] = $pair;
    }
    return $result;
  }

  function pairLookupArrayReverse($pairs) {
    $result = array();
    foreach ($pairs as $index => $pair) {
      if (empty($result[$pair->second])) {
        $result[$pair->second] = array();
      }
      $result[$pair->second][$pair->first] = $pair;
    }
    return $result;
  }
?>
