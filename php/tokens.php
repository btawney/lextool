<?php // tokens.php

  require_once('singles.php');

  class Section {
    var $name;
    var $description;
    var $sections;
    var $tokens;

    function Section() {
      $this->name = '';
      $this->description = '';
      $this->sections = array();
      $this->tokens = array();
    }

    function select($path, $createIfNotExists = false) {
      $parts = mb_split('\.', $path);
      return $this->_select($parts, 0, count($parts), $createIfNotExists);
    }

    function _select(&$parts, $offset, $remaining, $createIfNotExists = false) {
      $next = false;
      foreach ($this->sections as $section) {
        if ($section->name == $parts[$offset]) {
          $next = $section;
          break;
        }
      }

      if ($next === false) {
        if ($createIfNotExists) {
          $next = new Section();
          $next->name = $parts[$offset];
          $next->description = $parts[$offset];
          $this->sections[] = $next;
        } else {
          return false;
        }
      }

      if ($remaining == 1) {
        return $next;
      } else {
        return $next->_select($parts, $offset + 1, $remaining - 1, $createIfNotExists);
      }
    }

    function flatten() {
      $result = array();
      $this->_flatten($result);
      return $result;
    }

    function _flatten(&$result) {
      foreach ($this->sections as $section) {
        $section->_flatten($result);
      }
      foreach ($this->tokens as $token) {
        $result[] = $token;
      }
      return;
    }

    function singles() {
      return singles($this->flatten());
    }
  }

?>
