<?php

  class Link {
    var $from;
    var $to;
    var $size;

    function Link($from, $to, $size) {
      $this->from = $from;
      $this->to = $to;
      $this->size = $size;
      $from->links[$to->id] = $this;
      $to->links[$from->id] = $this;
    }
  }

  class Node {
    var $id;
    var $links;

    function Node($id) {
      $this->id = $id;
      $this->links = array();
    }
  }

  class Network {
    var $nodes;

    function Network() {
      $this->nodes = array();
    }

    function node($id) {
      if (empty($this->nodes[$id])) {
        $this->nodes[$id] = new Node($id);
      }
      return $this->nodes[$id];
    }

    function link($from, $to, $size) {
      $fromNode = $this->nodes[$from];
      $toNode = $this->nodes[$to];
      if (empty($fromNode->links[$to])) {
        return new Link($fromNode, $toNode, $size);
      }
      return $fromNode->links[$to];
    }

    function save($path) {
      file_put_contents($path, serialize($this->nodes));
    }

    function load($path) {
      $this->nodes = unserialize(file_get_contents($path));
    }

    function buildFromContextComparer($comp, $sliceSize, $threshold = 1) {
      $idx = array();
      $slice = array_slice($comp->singles, 0, $sliceSize);
      foreach ($slice as $single) {
        $idx[] = $this->node($single->token);
      }

      $length = count($idx);
      for ($i = 0; $i < $length; ++$i) {
        $n1 = $idx[$i];

        for ($j = $i + 1; $j < $length; ++$j) {
          $n2 = $idx[$j];

          $sim = $comp->compare($n1->id, $n2->id);

          if ($sim < $threshold) {
            new Link($n1, $n2, $sim);
          }
        }
      }
    }
  }

  class NetworkMapping {
    var $net1;
    var $net2;
    var $mapping;
    var $reverse;

    function NetworkMapping ($net1, $net2) {
      $this->net1 = $net1;
      $this->net2 = $net2;
      $this->mapping = array();
    }

    function map ($id1, $id2) {
      $this->mapping[$id1] = $id2;
      $this->reverse[$id2] = $id1;
    }

    function unmap ($id1) {
      $id2 = $this->mapping[$id1];
      $this->mapping[$id1] = false;
      $this->reverse[$id2] = false;
    }

    function printMapping () {
      foreach ($this->mapping as $t1 => $t2) {
        if ($t2 === false) {
          print "\"$t1\" -> \"UNMAPPED\",\n";
        } else {
          print "\"$t1\" -> \"$t2\",\n";
        }
      }

      foreach ($this->reverse as $t2 => $t1) {
        if ($t1 === false) {
          print "\"UNMAPPED\" -> \"$t2\",\n";
        }
      }
    }

    function score () {
      $score = 0;

      foreach ($this->mapping as $id1 => $id2) {
        if ($id2 !== false) {
          $score += $this->mappingValue($id1, $id2);
        }
      }

      return $score;
    }

    function mappingValue($id1, $id2) {
      $value = 0;

      $n1 = $this->net1->node($id1);
      $n2 = $this->net2->node($id2);

      foreach ($n1->links as $to1 => $link1) {
        if (isset($this->mapping[$to1]) && $this->mapping[$to1] !== false) {
          $to2 = $this->mapping[$to1];
          if (isset($n2->links[$to2])) {
            $link2 = $n2->links[$to2];

            $value += abs($link1->size - $link2->size);
          }
        }
      }

      return $value;
    }

    var $_bestScore;
    var $_bestMapping;
    var $_ids1;
    var $_startTime;
    var $_runSeconds;
    var $_sleepSeconds;
    var $_sleepAfter;
    var $_odometer;
    function bestMap($runSeconds, $sleepSeconds) {
      $this->_bestScore = $this->worstPossibleScore() + 1;
      $this->_runSeconds = $runSeconds;
      $this->_sleepSeconds = $sleepSeconds;

      $this->mapping = array();
      foreach ($this->net1->nodes as $id1 => $node) {
        $this->mapping[$id1] = false;
      }
      $this->reverse = array();
      foreach ($this->net2->nodes as $id2 => $node) {
        $this->reverse[$id2] = false;
      }
      $this->_ids1 = array_keys($this->mapping);

      $this->_startTime = time();
      $this->_sleepAfter = $this->_startTime + $this->_runSeconds;
      $this->_odometer = 100;
      $this->_findBest(0, count($this->mapping), 0, 0, 1.0);
    }

    function _findBest($depth, $limit, $scoreSoFar) {
      if ($depth == $limit) {
        // If we get this far, we have a winner
        $this->_bestScore = $scoreSoFar;
        $this->_bestMapping = $this->mapping;

print "Found at depth $depth: $this->_bestScore\n";
print serialize($this->_bestMapping);
print "\n";

        return;
      }

      --$this->_odometer;
      if ($this->_odometer == 0) {
        $this->_odometer = 100;
        $time = time();
        if ($time > $this->_sleepAfter) {
$elapsed = $time - $this->_startTime;
print "Elapsed: $elapsed; Sleeping...\n";
          sleep($this->_sleepSeconds);
print "Running...\n";
          $this->_sleepAfter = $time + $this->_sleepSeconds + $this->_runSeconds;
        }
      }

      $id1 = $this->_ids1[$depth];

      // Try against all unmapped nodes
      foreach ($this->reverse as $id2 => $other) {
        if ($other === false) {
          // See if we can improve on the score to beat
          $scoreWouldBe = $scoreSoFar + $this->mappingValue($id1, $id2);

          if ($scoreWouldBe < $this->_bestScore) {
            // Try mapping it
            $this->map($id1, $id2);

            // See how far we can go
            $best = $this->_findBest($depth + 1, $limit, $scoreWouldBe);

            // Unmap it
            $this->unmap($id1);
          }
        }
      }
    }

    function worstPossibleScore() {
      $score = 0;
      foreach ($this->net1->nodes as $node) {
        foreach ($node->links as $link) {
          $score += $link->size;
        }
      }
      foreach ($this->net2->nodes as $node) {
        foreach ($node->links as $link) {
          $score += $link->size;
        }
      }
      return $score;
    }
  }
?>
