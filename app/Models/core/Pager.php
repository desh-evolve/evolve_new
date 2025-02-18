<?php

namespace App\Models\Core;

class Pager {
	protected $rs = NULL;
	protected $count_rows = TRUE; //Specify if we count the total rows or not.

	function __construct($arr) {
		if ( isset($arr->rs) ) {
			//If there is no RS to return, something is seriously wrong. Check interface.inc.php?
			//Make sure the ListFactory function is doing a pageselect
			$this->rs = $arr->rs;

			$this->count_rows = $arr->db->pageExecuteCountRows;

			return TRUE;
		}

		return FALSE;
	}

	function getPreviousPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->absolutepage() - 1;
		}

		return FALSE;
	}

	function getCurrentPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->absolutepage();
		}

		return FALSE;
	}

	function getNextPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->absolutepage() + 1;
		}

		return FALSE;
	}

	function isFirstPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->atfirstpage();
		}

		return TRUE;
	}

	function isLastPage() {
		//If the first page is also the last, return true.
		if ( $this->isFirstPage() AND $this->LastPageNumber() == 1) {
			return TRUE;
		}

		if ( is_object($this->rs) ) {
			return $this->rs->atlastpage();
		}

		return TRUE;
	}

	function LastPageNumber() {
		if ( is_object($this->rs) ) {
			if ( $this->count_rows === FALSE ) {
				if ( $this->getCurrentPage() < 0 ) {
					//Only one page in result set.
					return $this->rs->lastpageno();
				} else {
					//More than one page in result set.
					if ( $this->rs->atlastpage() == TRUE ) {
						return $this->getCurrentPage();
					} else {
						//Since we don't know what the actual last page is, just add 100 pages to the current one.
						//The user may need to click this several times if there are more than 100 pages.
						return $this->getCurrentPage()+99;
					}
				}
			} else {
				return $this->rs->lastpageno();
			}
		}

		return FALSE;
	}

	//Return maximum rows per page
	function getRowsPerPage() {
		if ( is_object($this->rs) ) {
			return $this->rs->recordcount();
		}

		return FALSE;
	}

	function getTotalRows() {
		if ( is_object($this->rs) ) {
			return $this->rs->maxrecordcount();
		}

		return FALSE;
	}

	function getPageVariables() {
		//Make sure the ListFactory function is doing a pageselect
		$paging_data = array(
							'previous_page' 	=> $this->getPreviousPage(),
							'current_page' 		=> $this->getCurrentPage(),
							'next_page'			=> $this->getNextPage(),
							'is_first_page'		=> $this->isFirstPage(),
							'is_last_page'		=> $this->isLastPage(),
							'last_page_number'	=> $this->LastPageNumber(),
							'rows_per_page' 	=> $this->getRowsPerPage(),
							'total_rows' 		=> $this->getTotalRows(),
							);
		//var_dump($paging_data);
		return $paging_data;
	}
}
?>
