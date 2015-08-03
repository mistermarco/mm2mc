<?php
/*
 * A queue for lists, FIFO
 */
class SourceList extends SplQueue {

  private $seen = array();

  public function enqueue($value) {

    // Handle arrays of values as well
    if (is_array($value)) {
      foreach ($value as $item) {
        $this->enqueue($item);
      }
      return;
    }

    // Only add unique values to the queue
    if (!in_array($value, $this->seen)) {
      array_push($this->seen, $value);
      parent::enqueue($value);
    }
  }
}
