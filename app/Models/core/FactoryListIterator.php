<?php

class FactoryListIterator implements Iterator {
    private $obj;
	private $rs;
	private $class_name;

    function __construct($obj) {
		$this->class_name = get_class($obj);

		if ( isset($obj->rs) ) {
			$this->rs = $obj->rs;
		}

		$this->obj = $obj;
    }

    function rewind() {
		if ( isset($this->obj->rs) ) {
			$this->obj->rs->MoveFirst();
		}

		return FALSE;
    }

    function valid() {
		if ( isset($this->obj->rs) ) {
			return !$this->obj->rs->EOF;
		}

		return FALSE;
    }

    function key() {
        return $this->obj->rs->_currentRow;
    }

    function current() {
		if ( isset($this->obj->rs) ) { //Stop some warnings from coming up?

			//This automatically resets the object during each iteration in a foreach()
			//Without this, data can persist and cause undesirable results.

			$this->obj = new $this->class_name();

			$this->obj->rs = $this->rs;

			$this->obj->data = $this->obj->rs->fields; //Orignal
		}

		return $this->obj;
    }

    function next() {
        $this->obj->rs->MoveNext();
    }

}
?>
