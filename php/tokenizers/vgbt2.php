<?php // vgbt2.php
  // Parser for Voynich Glyph-Based Transcription 2002

  require_once('codepage.php');
  require_once('tokens.php');

  function tokenize_vgbt2 ($directory) {
    $ms = new Section();
    $ms->name = 'ms';
    $ms->description = 'Voynich Manuscript';

    for ($quire = 1; $quire <= 20; ++$quire) {
      // Quires 16 and 18 are missing
      if ($quire == 16 || $quire == 18) {
        continue;
      }

      $q = new Section();
      $q->name = "q$quire";
      $q->description = "Quire $quire";

      $data = file_get_contents("$directory/vgbt2_q$quire.txt");
      $converted = mb_convert_encoding($data, 'UTF-8', 'ASCII');

      $lines = mb_split("\n", $converted);
      foreach ($lines as $line) {
        // <115v.17>ohan.wc9.hcc9.ewo.s.a?9=
        if (mb_ereg('^<([^>]*)>([^=-]*)([-=]?)$', trim($line), $m)) {
          $location = $m[1];
          $content = $m[2];
          $terminator = $m[3];

          $s = $q->select($location, true);

          $s->tokens = mb_split('[.~]+', $content);
        }
      }

      $ms->sections[] = $q;
    }

    return $ms;
  }
?>
