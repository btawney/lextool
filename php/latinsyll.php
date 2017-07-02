<?php // latinsyll.php
  function latinSyllables($token) {
    if (is_array($token)) {
      $result = array();
      foreach ($token as $t) {
        $result[] = latinSyllables($t);
      }
      return $result;
    }

    // Begin by splitting at vowels
    // arma/virumque/cano/troiae/qui/primus/ab/oris
    $w = $token;
    // a rma /vi ru mqu e /ca no /tro i a e /qu i /pri mu s/a b/o ri s
    $w = mb_ereg_replace('([aeiouy])', '\1 ', $w);

    // Let qu be a consonant cluster
    // a rma /vi ru mque /ca no /tro i a e /qui /pri mu s/a b/o ri s
    $w = mb_ereg_replace('qu ([aeiouy])', 'qu\1', $w);

    // Merge diphthongs
    // a rma /vi ru mque /ca no /tro i a#e /qui /pri mu s/a b/o ri s
    $w = mb_ereg_replace('(a) (e)', '\1#\2', $w);
    $w = mb_ereg_replace('([ae]) (u)', '\1#\2', $w);
    $w = mb_ereg_replace('([eu]) (i)', '\1#\2', $w);
    $w = mb_ereg_replace('(o) (e)', '\1#\2', $w);
    // a rma /vi ru mque /ca no /tro i ae /qui /pri mu s/a b/o ri s
    $w = mb_ereg_replace('#', '', $w);

    // Bring isolated consonants into the fold and trim trailing spaces
    // a rma /vi ru mque /ca no /tro i ae /qui /pri mus/ab/o ris
    $w = mb_ereg_replace(' ([^aeiouy])+$', '\1', $w);
    // a rma/vi ru mque/ca no/tro i ae/qui/pri mus/ab/o ris
    $w = trim($w);

    // Merge consonantal i
    $w = mb_ereg_replace('([aeiouy]) i ([aeiouy])', '\1 i\2', $w);

    // Break initial consonant clusters
    // ar ma/vi rum que/ca no/tro iae/qui/pri mus/ab/o ris
    $parts = mb_split(' ', $w);
    $length = count($parts);
    for ($i = 1; $i < $length; ++$i) {
      while (! mb_ereg('^(|[bdg][rl]?|s?[ptck]h?[rl]?|[fhlmnrsv]|qu|x|ps|z)[aeiouy].*', $parts[$i])) {
        $parts[$i - 1] .= mb_substr($parts[$i], 0, 1);
        $parts[$i] = mb_substr($parts[$i], 1);
      }
    }

    // Return results
    return $parts;
  }

  function addSyllablesLatin (&$singles) {
    foreach ($singles as $single) {
      $single->syllables = latinSyllables($single->token);
    }
  }
?>
