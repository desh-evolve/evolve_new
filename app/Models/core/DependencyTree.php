<?php

namespace App\Models\Core;

class DependencyTree {
	/*
		Take a look at PEAR: Structures_Graph
	*/

	protected $cache = NULL;
	var $raw_data = NULL;
	var $raw_data_order = array();

	protected $provide_id_raw_data= array();
	protected $require_id_raw_data= array();

	protected $provide_ids = NULL;

	protected $tree = NULL;


	// set this flag to true to enable tree ordering, eg, the final output will have whole trees in contiguous array slice.
	protected $tree_ordering = FALSE; // faster without tree ordering.

	function getTreeOrdering() {
		return $this->tree_ordering;
	}

	function setTreeOrdering( $bool ) {
		$this->tree_ordering = $bool;
	}
	
	/*
		$ID = ID of node
		$requires = array of IDs this node requires
		$provides = array of IDs this node provides
		$order = integer to help resolve circular dependencies, lower order comes first.
	*/
	function addNode( $id, $requires, $provides, $order = 0 ) {
		if ( $id == '' ) {
			return FALSE;
		}

		if ( isset($this->raw_data[$id]) ) {
			//ID already exists.
			return FALSE;
		}

		$dtn = new DependencyTreeNode();
		$dtn->setId( $id );
		$dtn->setRequires( $requires );
		$dtn->setProvides( $provides );
		$dtn->setOrder( $order );

		$this->addProvideIDs( $dtn->getProvides() );
		$this->addObjectByProvideIDs( $dtn->getProvides(), $dtn );
		$this->addObjectByRequireIDs( $dtn->getRequires(), $dtn );

		$this->raw_data[$id] = $dtn;
		if($this->tree_ordering) {
			array_push($this->raw_data_order,$dtn);
		}

		unset($dtn);


		return TRUE;
	}

	private function addObjectByProvideIDs($provide_ids, $obj) {
		if ( is_array($provide_ids) ) {
			foreach( $provide_ids as $provide_id ) {
				$this->provide_id_raw_data[$provide_id][] = $obj;
			}
		}

		return TRUE;
	}

	private function addObjectByRequireIDs($requires_ids, $obj) {
		if ( is_array($requires_ids) ) {
			foreach( $requires_ids as $require_id ) {
				$this->require_id_raw_data[$require_id][] = $obj;
			}
		}

		return TRUE;
	}

	private function getProvideIDs() {
		if ( isset($this->provide_ids) ) {
			return $this->provide_ids;
		}

		return FALSE;
	}

	private function addProvideIDs( $provide_arr ) {
		if ( is_array($provide_arr) ) {
			foreach( $provide_arr as $provide_id ) {
				$this->provide_ids[] = $provide_id;
			}
		}

		return TRUE;
	}

	private function deleteOrphanRequireIDs() {
		if ( is_array( $this->raw_data ) ) {
			foreach( $this->raw_data as $id => $obj ) {
				if ( is_array( $obj->getRequires() ) ) {
					$valid_require_ids = array();
					foreach( $obj->getRequires() as $require_id ) {
						if ( in_array( $require_id, (array)$this->getProvideIDs() ) ) {
							$valid_require_ids[] = $require_id;
						}
					}
					$obj->setRequires( $valid_require_ids );
				}
			}
		}

		//Debug::Arr($this->raw_data, 'With Valid Require Ids', __FILE__, __LINE__, __METHOD__,10);

		return TRUE;
	}

	/*

	2nov2006 no longer being used.

	private function getCacheDependsOn( $parent, $child ) {
		if ( isset( $this->cache[$parent->getId()][$child->getId()] ) ) {
			return $this->cache[$parent->getId()][$child->getId()];
		}

		return NULL; //NULL is no cache exists.
	}

	private function setCacheDependsOn( $parent, $child, $result ) {
		$this->cache[$parent->getId()][$child->getId()] = $result;

		return TRUE;
	}


	// returns TRUE if parent depends on child (either directly or indirectly), else FALSE
	function dependsOn( $parent, $child, $marked_edges = array(), $level=0 ) {
		//Debug::Text("Parent: ". $parent->getId() .' Child: '. $child->getId(). ' level: '.$level , __FILE__, __LINE__, __METHOD__,10);

		$cache_lookup = $this->getCacheDependsOn( $parent, $child );
		if ( $cache_lookup !== NULL ) {
			//Debug::Text(".........Returning Cache Data!", __FILE__, __LINE__, __METHOD__,10);
			return $cache_lookup;
		}

		if ( is_array( $parent->getRequires() ) ) {
			foreach ( $parent->getRequires() as $require_id ) {
				//Debug::Text("Parent require check: ". $require_id." l=$level", __FILE__, __LINE__, __METHOD__,10);
				if ( in_array( $require_id, $child->getProvides() ) ) {
					//Debug::Text("bReturning TRUE! l=$level", __FILE__, __LINE__, __METHOD__,10);

					$this->setCacheDependsOn( $parent, $child, TRUE );
					return TRUE;
				} else {
					if( isset($this->provide_id_raw_data[$require_id]) ) {
						foreach($this->provide_id_raw_data[$require_id] as $obj) { // (we already know obj provides this req id...)
							//Debug::Text("Recursing... Parent ID: ". $obj->getId()." l=$level", __FILE__, __LINE__, __METHOD__,10);

							if( !isset($marked_edges[$parent->getId()][$obj->getId()]) ) {
								$marked_edges[$parent->getId()][$obj->getId()] = TRUE;

								$retval = $this->dependsOn( $obj, $child, $marked_edges, $level+1); // pass by reference probably not necessary? ($marked_edges)

								if ( $retval === TRUE ) {
									//Debug::Text("bReturning TRUE! l=$level", __FILE__, __LINE__, __METHOD__,10);
									$this->setCacheDependsOn( $parent, $child, TRUE );

									return TRUE;
								}
								// else... keep trying.
							}
						}
					}
				}
			}

			// at this point we have exhausted all our edges. we could be at a dead end or hit a circular reference with no further edges to travel.
			//Debug::Text("bReturning FALSE! l=$level", __FILE__, __LINE__, __METHOD__,10);
			$this->setCacheDependsOn( $parent, $child, FALSE );
		}

		return FALSE;
	}
	*/


	// debugging sort
	private function sort($a, $b) {
		$ret=$this->xsort($a,$b);

		//Debug::Text("ret: $ret", __FILE__, __LINE__, __METHOD__,10);
		//print $a->getId()." and ".$b->getId()." ret = $ret\n";
		return $ret;
	}

	// 2nov2006 changing the sort functionality to depth-based
	private function xsort($a, $b) {
		//Debug::Arr($a, 'A: ', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($b, 'B: ', __FILE__, __LINE__, __METHOD__,10);

		// first compare if nodes are in the same tree
		if ( $this->tree_ordering ) {
			if ( $a->getTreeNumber() < $b->getTreeNumber() ) {
				return -1;
			} elseif ( $a->getTreeNumber() > $b->getTreeNumber() ) {
				return 1;
			}
		}

		// sort by depth first
		$d_a=$a->getDepth();
		$d_b=$b->getDepth();
		if($d_a < $d_b) { return -1; }
		if($d_a > $d_b) { return 1; }

		// if depth is the same, then they are either: different graphs, same graph but in a circular reference loop (or just another branch.)
		// sort by order, if ==, then sort by id.

		$o_a=$a->getOrder();
		$o_b=$b->getOrder();
		if($o_a < $o_b) { return -1; }
		if($o_a > $o_b) { return 1; }

		// nothing left, sort by id.

		if($a->getId() < $b->getId()) { return -1; }
		if($a->getId() > $b->getId()) { return 1; }

		// should probably never reach here, but if the ids are the same, they might as well be equal.
		return 0;
	}

	// traverse a tree starting with a node.
	function markTreeNumber($node, $tree_number, $marked_edges = array()) {
		// mark the node. but should we check to see if it was marked under another tree number?
		if($node->getTreeNumber() !== NULL) {
			return;
		}
		$node->setTreeNumber($tree_number);

		// first look to see if any other node gives what this node requires
		if(is_array($node->getRequires())) {
			foreach($node->getRequires() as $require_id) {
				if( isset($this->provide_id_raw_data[$require_id]) ) {
					foreach($this->provide_id_raw_data[$require_id] as $obj) { // (we already know obj provides this req id...)
						if($node->getId() != $obj->getId()) {
							if( !isset($marked_edges[$node->getId()][$obj->getId()]) ) {
								$marked_edges[$node->getId()][$obj->getId()]=TRUE;
								$marked_edges[$obj->getId()][$node->getId()]=TRUE;
								$this->markTreeNumber($obj, $tree_number, $marked_edges);
							}
						}
					}
				}
			}
		}

		// now vice versa
		if(is_array($node->getProvides())) {
			foreach($node->getProvides() as $provide_id) {
				if( isset($this->require_id_raw_data[$provide_id]) ) {
					foreach($this->require_id_raw_data[$provide_id] as $obj) { // (we already know obj provides this req id...)
						if($node->getId() != $obj->getId()) {
							if( !isset($marked_edges[$node->getId()][$obj->getId()]) ) {
								$marked_edges[$node->getId()][$obj->getId()]=TRUE;
								$marked_edges[$obj->getId()][$node->getId()]=TRUE;
								$this->markTreeNumber($obj, $tree_number, $marked_edges);
							}
						}
					}
				}
			}
		}
		// we're done if after all the recursion we end up here.
	}

	// get an object's depth by traversing all its parents (recursively) ontul there are no edges left. the count of edges is the 'depth'.
	function _findDepth($obj,&$marked_edges=array(),$depth=0) {
		if(is_array($obj->getRequires())) {
			foreach($obj->getRequires() as $req_id) {
				if( isset($this->provide_id_raw_data[$req_id]) ) {
					foreach($this->provide_id_raw_data[$req_id] as $node) { // (we already know obj provides this req id...)
						if( !isset($marked_edges[$node->getId()][$obj->getId()]) ) {
							$marked_edges[$node->getId()][$obj->getId()]=TRUE;
							$this->_findDepth($node,$marked_edges,$depth+1);
						}
					}
				}
			}
		}

		if($depth==0) {
			return count($marked_edges);
		}
	}

	function _buildTree() {
		if ( !is_array($this->raw_data) ) {
			return FALSE;
		}

		$this->deleteOrphanRequireIDs();


		if($this->tree_ordering) {
			// now number the trees so that the algorithm knows how to sort them properly
			// eg the list of nodes might have 5 in one tree, and another unconnected tree with 3 nodes.
			// this needs to be handled properly.
			$treenumber=0;
			foreach($this->raw_data_order as $obj) {
				if($obj->getTreeNumber() === NULL) {
					$this->markTreeNumber($obj,$treenumber++);
				}
			}
		}

		//Debug::Arr($this, 'Before - Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		// mark all depths first.
		foreach($this->raw_data as $obj) {
			$obj->setDepth($this->_findDepth($obj));
		}

		usort( $this->raw_data, array($this, "sort") );

		//Debug::Arr($this->cache, 'dependency cache', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this->provide_id_raw_data, 'provides, raw', __FILE__, __LINE__, __METHOD__,10);
		//Debug::Arr($this, 'After - Raw Data: ', __FILE__, __LINE__, __METHOD__,10);

		foreach( $this->raw_data as $id => $obj ) {
			$retarr[] = $obj->getId();
		}

		#Debug::Arr($retarr, 'Dependency Tree Final Result!!', __FILE__, __LINE__, __METHOD__,10);

		return $retarr;
	}

	function getAllNodesInOrder() {
		return $this->_buildTree();
	}
}


/**
 * @package Core
 */
class DependencyTreeNode {
	protected $data;

	function setId( $id ) {
		if ( $id != '' ) {
			$this->data['id'] = $id;
		}

		return FALSE;
	}
	function getId() {
		if ( isset($this->data['id']) ) {
			return $this->data['id'];
		}

		return FALSE;
	}

	function setDepth($arg) {
		$this->data['depth']=(int)$arg;
		return FALSE;
	}
	function getDepth() {
		if ( isset($this->data['depth']) ) {
			return $this->data['depth'];
		}
		return NULL;
	}

	function setRequires( $arr ) {
		if ( $arr != '' ) {
			if ( !is_array($arr) ) {
				$arr = array($arr);
			}

			$this->data['requires'] = array_unique($arr);
		}

		return FALSE;
	}
	function getRequires() {
		if ( isset($this->data['requires']) ) {
			return $this->data['requires'];
		}

		return FALSE;
	}

	function setProvides( $arr ) {
		if ( $arr != '' ) {
			if ( !is_array($arr) ) {
				$arr = array($arr);
			}

			$this->data['provides'] = array_unique($arr);
		}

		return FALSE;
	}
	function getProvides() {
		if ( isset($this->data['provides']) ) {
			return $this->data['provides'];
		}

		return FALSE;
	}

	function setTreeNumber( $treenumber ) {
		$this->data['treenumber'] = (int)$treenumber;

		return FALSE;
	}
	function getTreeNumber() {
		if ( isset($this->data['treenumber']) ) {
			return $this->data['treenumber'];
		}

		return NULL;
	}

	function setOrder( $order ) {
		$this->data['order'] = (int)$order;

		return FALSE;
	}
	function getOrder() {
		if ( isset($this->data['order']) ) {
			return $this->data['order'];
		}

		return 0;
	}
}
?>