<?php // greeksyll.php
  mb_internal_encoding('UTF-8');
  mb_regex_encoding('UTF-8');

  function greekSyllables($token) {
    if (is_array($token)) {
      $result = array();
      foreach ($token as $t) {
        $result[] = greekSyllables($t);
      }
      return $result;
    }

    $alpha = "αάἀἁἂἃἄἅἆἇὰάᾀᾁᾂᾃᾄᾅᾆᾇᾰᾱᾲᾳᾴᾶᾷ";
    $epsilon = "εέἐἑἒἓἔἕὲέ";
    $eta = "ηήἠἡἢἣἤἥἦἧὴήᾐᾑᾒᾓᾔᾕᾖᾗῂῃῄῆῇ";
    $iota = "ιΐίϊἰἱἲἳἴἵἶἷὶίῐῑῒΐῖῗ";
    $omicron = "οόὀὁὂὃὄὅὸό";
    $upsilon = "υΰϋύὐὑὒὓὔὕὖὗὺύῠῡῢΰῦῧ";
    $omega = "ωώὠὡὢὣὤὥὦὧὼώᾠᾡᾢᾣᾤᾥᾦᾧῲῳῴῶῷ";

    $vowels = "$alpha$epsilon$eta$iota$omicron$upsilon$omega";

    $w = $token;

    // Convert complex consonants to clusters
    $w = mb_ereg_replace('ζ', 'τσ', $w);
    $w = mb_ereg_replace('ξ', 'κσ', $w);
    $w = mb_ereg_replace('ψ', 'πσ', $w);

    // Begin by splitting at vowels
    $w = mb_ereg_replace("([$vowels])", '\1 ', $w);

    // Merge diphthongs
    $w = mb_ereg_replace("([$alpha$epsilon$upsilon$omicron]) ([$iota])", '\1#\2', $w);
    $w = mb_ereg_replace("([$alpha$epsilon$omicron$eta]) ([$upsilon])", '\1#\2', $w);
    $w = mb_ereg_replace('#', '', $w);

    // Bring isolated consonants into the fold and trim trailing spaces
    $w = mb_ereg_replace(" ([^$vowels])+" . '$', '\1', $w);
    $w = trim($w);

    // Break initial consonant clusters
    $parts = mb_split(' ', $w);
    $length = count($parts);
    for ($i = 1; $i < $length; ++$i) {
      while (! mb_ereg("^(|[βδγ][ρλ]?|σ?[πτκφθχ]?[ρλ]?|[μν])[$vowels].*", $parts[$i])) {
        $parts[$i - 1] .= mb_substr($parts[$i], 0, 1);
        $parts[$i] = mb_substr($parts[$i], 1);
      }
    }

    // Return results
    return $parts;
  }
?>
