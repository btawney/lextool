<?php
  function flatten($hierarchy) {
    $result = array();
    foreach ($hierarchy as $element) {
      if (is_array($element)) {
        $flatElements = flatten($element);
        foreach ($flatElements as $flatElement) {
          $result[] = $flatElement;
        }
      } else {
        $result[] = $element;
      }
    }
    return $result;
  }
?>
