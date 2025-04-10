<?php

namespace App\Models\Core;

use Illuminate\Support\Facades\DB;

class FastTree
{

	var $db = NULL;
	var $table = 'user_group_tree';
	var $tree_id = 0;

	var $spacer = 0;

	function __construct($options = NULL)
	{
		//Debug::Text(' Contruct... ', __FILE__, __LINE__, __METHOD__,10);
		/*
		$this->db = $options['db'];
		//Debug::Text(' Setting DB to: '. $options['db'] , __FILE__, __LINE__, __METHOD__,10);

		$this->table = $options['table'];
		//Debug::Text(' Setting Table to: '. $options['table'] , __FILE__, __LINE__, __METHOD__,10);

		if ( isset( $options['tree_id'] ) ) {
			$this->setTree( $options['tree_id'] ) ;
			//$this->tree_id = $options['tree_id'];
			//Debug::Text(' Setting Tree ID to: '. $options['tree_id'] , __FILE__, __LINE__, __METHOD__,10);
		}
*/
		return TRUE;
	}

	function getTree()
	{
		return $this->tree_id;
	}

	function setTree($id)
	{
		if ($id != '') {
			//Debug::Text(' Setting Tree ID to: '. $id , __FILE__, __LINE__, __METHOD__,10);
			$this->tree_id = $id;

			$this->_setupTree();

			return TRUE;
		}

		return FALSE;
	}

	function _setupTree()
	{
		//Add the root node if its missing.
		$node_data = $this->getNode( 0 );
		if ( empty($node_data) || $node_data === FALSE ) {
			Debug::Text(' Initiating Tree with Root object: ' , __FILE__, __LINE__, __METHOD__,10);
			$this->add( 0, -1 );

			return TRUE;
		}

		//Debug::Text(' NOT Initiating Tree with Root object: ' , __FILE__, __LINE__, __METHOD__,10);
		return FALSE;
	}

	function getRootId()
	{
		$ph = array(
			':tree_id' => $this->tree_id,
		);

		// get all children of this node
		$query = 'SELECT object_id FROM ' . $this->table . ' WHERE tree_id = :tree_id AND parent_id = -1';
		// $root_id = $this->db->GetOne($query, $ph);
		$root_id = DB::select($query, $ph);

        if (empty($root_id) || $root_id === FALSE ) {
            $root_id = 0;
        }else{
            $root_id = current(get_object_vars($root_id[0]));
        }

		return $root_id;
	}

	function getNode($object_id)
	{
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__,10);

		if (!is_numeric($object_id)) {
			Debug::Text(' aReturning False', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$ph = array(
			':tree_id' => $this->getTree(),
			':object_id' => $object_id,
		);

		// get all children of this node
		$query = '	SELECT a.object_id, a.parent_id, a.left_id, a.right_id, count(b.object_id)-1 as level
					FROM ' . $this->table . ' a
					LEFT JOIN ' . $this->table . ' b ON a.tree_id = b.tree_id AND a.left_id BETWEEN b.left_id AND b.right_id

					WHERE a.tree_id = :tree_id
						AND a.object_id = :object_id
					GROUP BY a.object_id, a.left_id, a.object_id, a.parent_id, a.right_id
				';
		// $data = $this->db->GetRow($query, $ph);
		$data = DB::select($query, $ph);

		if (count($data) == 0) {
			return FALSE;
		}

		return $data;
	}

	function getLevel($object_id)
	{
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__,10);

		$data = $this->getNode( $object_id );
		if (empty($data) || $data === FALSE ) {
			return FALSE;
		}
		return $data['level'];
	}

	function getRightId($object_id)
	{
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__,10);

		$data = $this->getNode( $object_id );
		if (empty($data) || $data === FALSE ) {
			return FALSE;
		}
		return $data['right_id'];
	}

	function getLeftId($object_id)
	{
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__,10);

		$data = $this->getNode( $object_id );
		if (empty($data) || $data === FALSE ) {
			return FALSE;
		}
		return $data['left_id'];
	}

	// function getParentId( $object_id ) {
	// 	//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__,10);

	// 	$data = $this->getNode( $object_id );
	// 	if ($data === FALSE ) {
	// 		return FALSE;
	// 	}
	// 	return $data['parent_id'];
	// }

	function getParentId($object_id)
	{

		$data = $this->getNode($object_id);
		if ($data === FALSE || empty($data)) {
			return FALSE;
		}

		return $data[0]->parent_id ?? FALSE;
	}

	function rebuildTree($object_id = FALSE)
	{
		Debug::Text(' Object ID: ' . $object_id, __FILE__, __LINE__, __METHOD__, 10);

		// $this->db->BeginTrans();
		// $this->db->SetTransactionMode( 'SERIALIZABLE' ); //Serialize rebuild tree transactions so concurrency issues don't corrupt the tree.
		DB::beginTransaction(); // Begin the transaction
		DB::statement('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE'); // Set the isolation level to SERIALIZABLE


		if ( empty($object_id) || $object_id === FALSE ) {
			Debug::Text(' Object ID not specified, using root: ', __FILE__, __LINE__, __METHOD__,10);
			$object_id = $this->getRootId();
			$left_id = 1;
		} else {
			Debug::Text(' Object ID specified: ', __FILE__, __LINE__, __METHOD__, 10);
			$left_id = $this->getLeftId($object_id);
		}

		if ( empty($left_id) || $left_id === FALSE ) {
			Debug::Text(' Error getting left id: ', __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}

		Debug::Text(' aObject ID: ' . $object_id . ' - Left ID: ' . $left_id, __FILE__, __LINE__, __METHOD__, 10);
		$rebuilt = $this->_rebuildTree($object_id, $left_id);

		if (empty($rebuilt) || $rebuilt === FALSE) {
			Debug::Text(' Error rebuilding tree: ', __FILE__, __LINE__, __METHOD__,10);
			// $this->db->RollBackTrans();
			DB::rollBack();
			return FALSE;
		}

		//$this->db->RollBackTrans();

		// $this->db->CommitTrans();
		DB::commit();

		$this->db->SetTransactionMode(''); //Restore default transaction mode.

		Debug::Text(' Tree Rebuilt: ', __FILE__, __LINE__, __METHOD__, 10);

		return TRUE;
	}

	function _rebuildTree($object_id, $left_id)
	{
		Debug::Text(' Object ID: ' . $object_id . ' - Left: ' . $left_id, __FILE__, __LINE__, __METHOD__, 10);

		$ph = array(
			':tree_id' => $this->getTree(),
			':parent_id' => $object_id,
		);

		// get all children of this node
		$query = 'SELECT object_id FROM ' . $this->table . ' WHERE tree_id = :tree_id AND parent_id = :parent_id';
		$rs = DB::select($query, $ph);

		if (!is_object($rs)) {
			Debug::Text(' Select failed', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		// the right value of this node is the left value + 1 (or more)
		$right_id = $left_id + 10;

		while ($row = $rs->FetchRow()) {
			// recursive execution of this function for each
			// child of this node
			// $right is the current right value, which is
			// incremented by the rebuild_tree function
			Debug::Text(' Right ID: ' . $right_id, __FILE__, __LINE__, __METHOD__, 10);
			$right_id = $this->_rebuildTree($row['object_id'], $right_id);

			if ( empty($right_id) || $right_id === FALSE ) {
				Debug::Text(' Right was false: ', __FILE__, __LINE__, __METHOD__,10);
				return FALSE;
			}
		}

		$ph = array(
			':left_id' => $left_id,
			':right_id' => $right_id,
			':tree_id' => $this->getTree(),
			':object_id' => $object_id,
		);

		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$query  = 'UPDATE ' . $this->table . ' SET left_id = :left_id, right_id = :right_id WHERE tree_id = :tree_id AND object_id = :object_id';
		$rs = DB::select($query, $ph);

		//Use this to help debug concurrency issues.
		//usleep(100000);

		if (!is_object($rs)) {
			Debug::Text(' Rebuild Failed... ', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		// return the right value of this node + 1
		return $right_id + 1;
	}

	function getAllParents($object_id)
	{
		//Debug::Text(' Object ID: '. $object_id, __FILE__, __LINE__, __METHOD__,10);

		if ($object_id === '') {
			Debug::Text(' aReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$ph = array(
			':tree_id' => $this->getTree(),
			':object_id' => $object_id,
			':object_id2' => $object_id,
		);

		$query  = '
				SELECT		b.object_id
				FROM		' . $this->table . ' as a
				LEFT JOIN ' . $this->table . ' as b ON a.tree_id = b.tree_id AND a.left_id BETWEEN b.left_id AND b.right_id
				WHERE		a.tree_id = :tree_id
					AND 	a.object_id = :object_id
					AND		b.object_id != 0
					AND		b.object_id != :object_id2
				ORDER BY	b.left_id desc
				';

		return DB::select($query, $ph);
	}

	function getChild($object_id)
	{
		if (!is_numeric($object_id)) {
			Debug::Text(' aReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		$ph = array(
			':tree_id' => $this->getTree(),
			':object_id' => $object_id,
		);

		Debug::Text(' Getting Last Child of: ' . $object_id, __FILE__, __LINE__, __METHOD__, 10);
		//Order by last child first.
		//GetOne() automatically sets LIMIT 1;
		$query = 'SELECT object_id FROM ' . $this->table . ' WHERE tree_id = :tree_id AND parent_id = :object_id ORDER BY left_id desc';
		// $child_id = $this->db->GetOne($query, $ph);
		$child_id = DB::select($query, $ph);

        if (empty($child_id) || $child_id === FALSE ) {
            $child_id = 0;
        }else{
            $child_id = current(get_object_vars($child_id[0]));
        }

		//var_dump($child_id);

		return $child_id;
	}

	function getAllChildren($object_id = NULL, $recurse = FALSE, $data_format = 0)
	{
		$original_object_id = $object_id;
		//Debug::Text(' Object ID: '. $object_id .' Recurse: '. $recurse , __FILE__, __LINE__, __METHOD__,10);

		if ($object_id === '') {
			Debug::Text(' aReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ($object_id === NULL or $object_id === FALSE) {
			$object_id = $this->getRootId();
			Debug::Text(' Getting Root ID: ' . $object_id, __FILE__, __LINE__, __METHOD__, 10);
		}

		$node_data = $this->getNode($object_id);

		if ( empty($node_data) || $node_data === FALSE ) {
			Debug::Text(' Getting node data of object id failed.' , __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}
		// Access left_id from the first element
		$left_id = $node_data[0]->left_id;
		$right_id = $node_data[0]->right_id;
		$level = $node_data[0]->level;
		//Debug::Text(' Left ID: '. $node_data['left_id'] .' Level: '. $node_data['level'] , __FILE__, __LINE__, __METHOD__,10);

		$query  = '
				SELECT		a.object_id, a.parent_id, count(b.object_id) as level
				FROM		' . $this->table . ' a
				LEFT JOIN ' . $this->table . ' b ON a.tree_id = b.tree_id AND a.left_id BETWEEN b.left_id AND b.right_id
				';
		switch (strtoupper($recurse)) {
			case 'RECURSE':
				$ph = array(
					':tree_id' => $this->getTree(),
					// ':left_id' => $node_data['left_id'],
					// ':right_id' => $node_data['right_id'],
					':left_id' => $left_id,
					':right_id' => $right_id,
				);

				//Don't use >= <= (use > < ) - instead to not include the parent object.
				//Make sure current node is not included in the result as well. Otherwise we are saying the current node
				//is a child of itself.
				$query .= '
				WHERE		a.tree_id = :tree_id
					AND 	b.left_id > :left_id
					AND		b.right_id <= :right_id
                    ';

				//Exclude the parnet, but only when the passed object is forsure NULL!
				if ($original_object_id === NULL or $original_object_id === FALSE) {
					$ph[':object_id'] = $object_id;

					$query .= '
					AND		a.object_id != :object_id
					';
				}

				break;
			default:
				$ph = array(
					':tree_id' => $this->getTree(),
					':object_id' => $object_id,
				);

				$query .= '
						WHERE a.tree_id = :tree_id
							AND a.parent_id = :object_id
                        ';
		}
		$query .= '
				GROUP BY 	a.object_id, a.parent_id, a.left_id
				ORDER BY	a.left_id';

		$rs = DB::select($query, $ph);

		// while ($row = $rs->FetchRow()) {
		// 	if ($data_format == 1) {
		// 		$retarr[$row['object_id']] = $row;
		// 	} else {
		// 		$retarr[$row['object_id']] = $row['level'];
		// 	}
		// }
		foreach ($rs as $row) {
			if ($data_format == 1) {
				$retarr[$row->object_id] = $row;  // Access properties using ->, not ['key']
			} else {
				$retarr[$row->object_id] = $row->level;
			}
		}


		if (isset($retarr)) {
			//Debug::Arr( $retarr, ' Children: ' , __FILE__, __LINE__, __METHOD__,10);
			return $retarr;
		}

		return FALSE;
	}

	function _getLeftAndRightIds($parent_id)
	{
		Debug::Text(' getLeftAndRightIds: ' . $parent_id, __FILE__, __LINE__, __METHOD__, 10);

		$node_data = $this->getNode($parent_id);

		$parent_left = $node_data['left_id'];
		$parent_right = $node_data['right_id'];

		$child_id = $this->getChild($parent_id);
		if ($child_id !== FALSE) {
			Debug::Text(' Child found, getting Child data: ' . $child_id, __FILE__, __LINE__, __METHOD__, 10);
			$child_node_data = $this->getNode($child_id);
			$child_left_id = $child_node_data['left_id'];
			$child_right_id = $child_node_data['right_id'];
			unset($child_node_data);
			Debug::Text(' Child Left ID: ' . $child_left_id, __FILE__, __LINE__, __METHOD__, 10);
			Debug::Text(' Child Right ID: ' . $child_right_id, __FILE__, __LINE__, __METHOD__, 10);
			Debug::Text(' Parent Right ID: ' . $parent_right, __FILE__, __LINE__, __METHOD__, 10);

			$left_id = $child_right_id + 1;
			$right_id = $child_right_id + 10;

			//$left_id = $child_right_id + 1;
			//$right_id = $parent_right - 1;

			if (
				$right_id >= $parent_right
				or $left_id >= $parent_right
			) {
				Debug::Text(' NO CHILD GAP LEFT: ', __FILE__, __LINE__, __METHOD__, 10);

				return FALSE;
			}
		} else {
			//Nothing yet.

			//Try to keep a large gap for these.
			$left_id = $parent_left + 1;
			$right_id = $parent_right - 1;

			if (
				$right_id >= $parent_right
				or $left_id >= $parent_right
			) {
				Debug::Text(' NO PARENT GAP LEFT: ', __FILE__, __LINE__, __METHOD__, 10);

				return FALSE;
			}
		}

		Debug::Text(' Next Left ID: ' . $left_id, __FILE__, __LINE__, __METHOD__, 10);
		Debug::Text(' Next Right ID: ' . $right_id, __FILE__, __LINE__, __METHOD__, 10);

		return array('left_id' => $left_id, 'right_id' => $right_id);
	}

	function insertGaps($parent_id)
	{
		$this->spacer++;

		Debug::Text(' Attempting to insert gaps: ' . $this->spacer, __FILE__, __LINE__, __METHOD__, 10);

		$node_data = $this->getNode($parent_id);

		if ($node_data != FALSE) {
			Debug::Text(' Inserting gaps: ' . $this->spacer, __FILE__, __LINE__, __METHOD__, 10);

			$ph = array(
				':tree_id' => $this->getTree(),
				':right_id' => $node_data['right_id'],
			);

			$query  = 'UPDATE ' . $this->table . ' SET right_id = right_id + 1000 WHERE tree_id = :tree_id AND right_id >= :right_id';
			DB::select($query, $ph);

			$query  = 'UPDATE ' . $this->table . ' SET left_id = left_id + 1000 WHERE tree_id = :tree_id AND left_id > ?';
			DB::select($query, $ph);

			return TRUE;
		}
		Debug::Text(' Node Data Null: ' . $this->spacer, __FILE__, __LINE__, __METHOD__, 10);

		return FALSE;
	}


	//MPTT + Gap add function.
	function add($object_id, $parent_id = 0)
	{
		Debug::Text(' Object ID: ' . $object_id . ' Parent ID: ' . $parent_id, __FILE__, __LINE__, __METHOD__, 10);

		if (!is_numeric($object_id)) {
			Debug::Text(' aReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		/*
		if ( $object_id == $parent_id ) {
			Debug::Text(' bReturning False...' , __FILE__, __LINE__, __METHOD__,10);
			return FALSE;
		}
		*/
		//$insert_id = $this->db->GenID( $this->table.'_id_seq',10);

		//Make sure object doesn't exist already
		if ($this->getNode($object_id) !== FALSE) {
			Debug::Text(' cReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		// $this->db->BeginTrans();
		DB::beginTransaction();

		try {
			//code...
		} catch (\Throwable $th) {
			//throw $th;
		}
		if ($parent_id == -1) {
			Debug::Text(' Parent is 0', __FILE__, __LINE__, __METHOD__, 10);

			$ph = array(
				':tree_id' => $this->getTree(),
			);

			$query = 'SELECT object_id FROM ' . $this->table . ' WHERE tree_id = :tree_id AND parent_id = -1';
			$rs = DB::select($query, $ph);

			if (!is_object($rs)) {
				Debug::Text(' Select failed', __FILE__, __LINE__, __METHOD__, 10);
				// $this->db->RollBackTrans();
				DB::rollBack();
				return FALSE;
			}

			if ($rs->RowCount() > 0) {
				Debug::Text(' A root node already exists', __FILE__, __LINE__, __METHOD__, 10);
				// $this->db->RollBackTrans();
				DB::rollBack();
				return FALSE;
			}

			$left_id = 0;

			//Get max right_id, just incase other nodes exist in the tree.
			$ph = array(
				':tree_id' => $this->getTree(),
			);

			$query = 'SELECT max(right_id) as right_id FROM ' . $this->table . ' WHERE tree_id = :tree_id';
			// $right_id = $this->db->GetOne($query, $ph) + 1000;
            $right_id = DB::Select($query, $ph);
            if (empty($right_id) || $right_id === FALSE ) {
                    $right_id = 0;
            }else{
                $right_id = current(get_object_vars($right_id[0])) + 1000;
            }

		} else {
			Debug::Text(' Parent IS NOT 0', __FILE__, __LINE__, __METHOD__, 10);

			$left_and_right_ids = $this->_getLeftAndRightIds($parent_id);

			if ($left_and_right_ids === FALSE) {
				$this->insertGaps($parent_id);
				$left_and_right_ids = $this->_getLeftAndRightIds($parent_id);
			}

			$left_id = $left_and_right_ids['left_id'];
			$right_id = $left_and_right_ids['right_id'];
		}

		if (
			is_numeric($this->getTree())
			and is_numeric($parent_id)
			and is_numeric($object_id)
			and is_numeric($left_id)
			and is_numeric($right_id)
		) {
			$ph = array(
				':tree_id' => $this->getTree(),
				':parent_id' => $parent_id,
				':object_id' => $object_id,
				':left_id' => $left_id,
				':right_id' => $right_id,
			);

			Debug::Text(' Inserting Node... Left ID: ' . $left_id . ' Right ID: ' . $right_id, __FILE__, __LINE__, __METHOD__, 10);
			$query = 'INSERT INTO ' . $this->table . ' (tree_id, parent_id, object_id, left_id, right_id) VALUES (:tree_id,:parent_id,:object_id,:left_id,:right_id)';
			$rs = DB::insert($query, $ph);

			if (!is_object($rs)) {
				Debug::Text(' Error inserting node', __FILE__, __LINE__, __METHOD__, 10);
				// $this->db->RollBackTrans();
				DB::rollBack();
				return FALSE;
			}

			// $this->db->CommitTrans();
			DB::commit();
			Debug::Text(' Returning True.', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		return FALSE;
	}

	protected function getListSQL($array, &$ph = NULL)
	{
		//Debug::Arr($ph, 'Place Holder BEFORE:', __FILE__, __LINE__, __METHOD__,10);

		//Append $array values to end of $ph, return
		//one "?," for each element in $array.

		$array_count = count($array);
		if (is_array($array) and $array_count > 0) {
			foreach ($array as $key => $val) {
				$ph_arr[] = '?';
				$ph[] = $val;
			}

			if (isset($ph_arr)) {
				$retval = implode(',', $ph_arr);
			}
		} elseif (is_array($array)) {
			//Return NULL, because this is an empty array.
			//This may have to return -1 instead of NULL
			//$ph[] = 'NULL';
			$ph[] = -1;
			$retval = '?';
		} elseif ($array == '') {
			//$ph[] = 'NULL';
			$ph[] = -1;
			$retval = '?';
		} else {
			$ph[] = $array;
			$retval = '?';
		}

		//Debug::Arr($ph, 'Place Holder AFTER:', __FILE__, __LINE__, __METHOD__,10);

		//Just a single ID, return it.
		return $retval;
	}

	function delete($object_id, $recurse = FALSE)
	{
		Debug::Text(' Deleting Object: ' . $object_id, __FILE__, __LINE__, __METHOD__, 10);

		if ($object_id == '') {
			Debug::Text(' aReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Find out if this node has children
		// $this->db->BeginTrans();
		DB::beginTransaction();


		//This was the source of a bug that was causing the below recurse delete query
		//to delete the root node of the tree. getAllChildren was returning FALSE and array_keys()
		//was turning that into array(0 => 0), so we were deleting node 0 and XXX in a single operation.
		$children_ids = $this->getAllChildren($object_id, 'RECURSE');
		if ($children_ids !== FALSE and is_array($children_ids)) {
			$children_ids = array_keys($children_ids);
		} else {
			$children_ids = array();
		}

		if (count($children_ids) == 0) {
			Debug::Text(' No Children: ', __FILE__, __LINE__, __METHOD__, 10);

			$ph = array(
				':tree_id' => $this->getTree(),
				':object_id' => $object_id,
			);

			$query = 'DELETE FROM ' . $this->table . ' WHERE tree_id = :tree_id AND object_id = :object_id';
			DB::select($query, $ph);
		} elseif (strtolower($recurse) == 'recurse') {
			Debug::Arr($children_ids, ' Recursing Delete - Current Object: ' . $object_id . ' Child IDs: ', __FILE__, __LINE__, __METHOD__, 10);

			$ph = array(
				':tree_id' => $this->getTree(),
			);

			//Add current object_id to children for delete.
			$children_ids[] = $object_id;


			$query = 'DELETE FROM ' . $this->table . ' WHERE tree_id = :tree_id AND object_id in' . implode(',', $children_ids);
			// $query = 'DELETE FROM '. $this->table .' WHERE tree_id = :tree_id AND object_id in ('. $this->getListSQL( $children_ids, $ph ).')';
			DB::select($query, $ph);
		} else {
			Debug::Text(' Re-parenting children: ', __FILE__, __LINE__, __METHOD__, 10);

			$parent_id = $this->getParentId($object_id);

			$ph = array(
				':tree_id' => $this->getTree(),
				':object_id' => $object_id,
			);

			$query = 'DELETE FROM ' . $this->table . ' WHERE tree_id = :tree_id AND object_id = :object_id';
			DB::select($query, $ph);

			$ph = array(
				':parent_id' => $parent_id,
				':tree_id' => $this->getTree(),
				':object_id' => $object_id,
			);

			$query = '	UPDATE ' . $this->table . '
						SET parent_id = :parent_id
						WHERE tree_id = :tree_id
							AND parent_id = :object_id';
			DB::select($query, $ph);
		}

		// $this->db->CommitTrans();
		DB::commit();

		return TRUE;
	}

	function move($object_id, $parent_id)
	{
		Debug::Text(' Object ID: ' . $object_id . ' Parent ID: ' . $parent_id, __FILE__, __LINE__, __METHOD__, 10);

		if ($object_id === '') {
			Debug::Text(' aReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ($parent_id === '') {
			Debug::Text(' bReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		//Make sure we don't reparent to self.
		$children_ids = array_keys((array)$this->getAllChildren($object_id, 'RECURSE'));

		if ($parent_id != 0 and is_array($children_ids) and in_array($parent_id, $children_ids) == TRUE) {
			Debug::Text(' Objects cant be re-parented to their own children...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		// $this->db->BeginTrans();
		DB::beginTransaction();

		$ph = array(
			':parent_id' => $parent_id,
			':tree_id' => $this->getTree(),
			':object_id' => $object_id,
		);

		$query = '	UPDATE ' . $this->table . '
					SET parent_id = :parent_id
					WHERE tree_id = :tree_id
						AND object_id = :object_id';
		DB::select($query, $ph);

		//FIXME: rebuild tree starting from object_id and parent_id only perhaps?
		//Might cut down on some work.
		$this->rebuildTree();

		// $this->db->CommitTrans();
		DB::commit();

		return TRUE;
	}

	function edit($object_id, $new_object_id)
	{
		Debug::Text(' Object ID: ' . $object_id . ' New Object ID: ' . $new_object_id, __FILE__, __LINE__, __METHOD__, 10);

		if ($object_id == '') {
			Debug::Text(' aReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ($new_object_id == '') {
			Debug::Text(' bReturning False...', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}

		if ($object_id == $new_object_id) {
			Debug::Text(' Object is the same ', __FILE__, __LINE__, __METHOD__, 10);
			return TRUE;
		}

		//Make sure new_object_id isn't already in the tree.
		if ($this->getNode($new_object_id) === FALSE) {
			Debug::Text(' Editing object ', __FILE__, __LINE__, __METHOD__, 10);

			// $this->db->BeginTrans();
			DB::beginTransaction();

			$ph = array(
				':new_object_id' => $new_object_id,
				':tree_id' => $this->getTree(),
				':object_id' => $object_id,
			);

			//Update parent IDs
			$query = '	UPDATE ' . $this->table . '
						SET parent_id = :new_object_id
						WHERE tree_id = :tree_id
							AND parent_id = :object_id';
			DB::select($query, $ph);

			//Update object ID
			$query = '	UPDATE ' . $this->table . '
						SET object_id = :new_object_id
						WHERE tree_id = :tree_id
							AND object_id = :object_id';
			DB::select($query, $ph);

			// $this->db->CommitTrans();
			DB::commit();

			return TRUE;
		} else {
			Debug::Text(' New Object ID is already in the tree', __FILE__, __LINE__, __METHOD__, 10);
			return FALSE;
		}
	}

	//Flex requires that all index keys start at 0, even in the children section,
	//So we need to handle that as well so Flex doesn't need any post processing.
	static function FormatFlexArray($nodes, $include_root = TRUE)
	{
		Debug::Text(' Formatting Flex Array...', __FILE__, __LINE__, __METHOD__, 10);
		$nested = array();
		$depths = array();

		if (is_array($nodes)) {
			foreach ($nodes as $key => $node) {
				if ($node['level'] == 1) {

					//Using sequential keys
					$nested[] = $node; //Each new branch of the tree the key should start at 0 and be a sequence without holes.
					end($nested);
					$depths[$node['level'] + 1] = key($nested);

					/*
					//Using non-sequential keys:
					$nested[$key] = $node;
					$depths[$node['level'] + 1] = $key;
					*/
				} else {
					$parent = &$nested;
					for ($i = 2; $i <= $node['level']; $i++) {
						if ($i == 2) {
							$parent = &$parent[$depths[$i]];
						} else {
							$parent = &$parent['children'][$depths[$i]];
						}
					}

					//Using sequential keys.
					$parent['children'][] = $node; //Each new branch of the tree the key should start at 0 and be a sequence without holes.
					end($parent['children']);
					$depths[$node['level'] + 1] = key($parent['children']);

					/*
					//Using non-sequential keys:
					$parent['children'][$key] = $node;
					$depths[$node['level'] + 1] = $key;
					*/
				}
			}
		}

		if ($include_root == TRUE) {
			return array(
				0 => array(
					'id' => 0,
					'name' => ('Root'),
					'level' => 0,
					'children' => $nested
				)
			);
		} else {
			return $nested;
		}
	}

	static function FormatArray($nodes, $type = 'HTML', $include_root = FALSE)
	{
		$type = strtolower($type);

		if ($include_root === TRUE) {
			if (!is_array($nodes)) {
				$nodes = array();
			}

			$root_node = array(
				'id' => 0,
				'name' => 'Root',
				'level' => 0,
			);

			array_unshift($nodes, $root_node);
		}

		if ( empty($nodes) || $nodes === FALSE ) {
			return FALSE;
		}

		foreach ($nodes as $node) {
			switch ($type) {
				case 'no_tree_text':
					$spacing = str_repeat('|  &nbsp;', $node['level'] * 1);
					$text = $node['name'];
					break;
				case 'text':
					$spacing = str_repeat('|  &nbsp;', $node['level'] * 1);
					$text = $spacing . $node['name'];
					break;
				case 'plain_text':
					$spacing = str_repeat('|  ', $node['level'] * 1);
					$text = $spacing . $node['name'];
					break;
				case 'html':
					$width = ($node['level'] - 1) * 20;
					$spacing = '<img src="' . Environment::getBaseURL() . 'images/s.gif" width="' . $width . '">';
					$text = $spacing . ' ' . $node['name'];
					break;
				case 'array':
					break;
			}

			$node['spacing'] = $spacing;
			$node['text'] = $text;

			$retarr[] = $node;

			unset($node);
		}

		return $retarr;
	}
}
