<?php

namespace App\Models\Core;

use Iterator;
use IteratorIterator;

class FactoryListIterator extends IteratorIterator {
    private $obj;
	private $rs;
	private $class_name;

    function __construct($obj) {
		parent::__construct(new \ArrayIterator([])); // Use a dummy iterator to satisfy the parent
		$this->class_name = get_class($obj);

		if (isset($obj->rs)) {
			$this->rs = $obj->rs;
		}

		$this->obj = $obj;
    }

    function rewind(): void {
		if (is_object($this->obj->rs) && method_exists($this->obj->rs, 'MoveFirst')) {
			$this->obj->rs->MoveFirst();
		}
	}
	
    function valid(): bool {
		return is_object($this->obj->rs) && property_exists($this->obj->rs, 'EOF') && !$this->obj->rs->EOF;
	}
	

    function key(): int {
        return $this->obj->rs->_currentRow;
    }

    function current(): mixed {
		if (isset($this->obj->rs) && $this->obj->rs) {
			// Reset object during each iteration to prevent data persistence
			$this->obj = new $this->class_name();
			$this->obj->rs = $this->rs;
	
			// Assuming $this->rs is a single row from your database query
			// Directly assign the row data instead of using ->fields
			//$this->obj->data = $this->obj->rs;
			
			// If your result set is a collection/array of rows, you might need:
			$this->obj->data = $this->obj->rs[key($this->obj->rs)] ?? null;
		}
		return $this->obj;
	}

    function next(): void {
        if (isset($this->obj->rs)) {
            $this->obj->rs->MoveNext();
        }
    }
}
