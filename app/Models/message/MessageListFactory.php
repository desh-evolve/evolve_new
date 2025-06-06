<?php

namespace App\Models\Message;

use App\Models\Core\Option;
use App\Models\Core\UserDateFactory;
use App\Models\PayPeriod\PayPeriodTimeSheetVerifyFactory;
use App\Models\Request\RequestFactory;
use App\Models\Users\UserFactory;
use Illuminate\Support\Facades\DB;
use IteratorAggregate;

class MessageListFactory extends MessageFactory implements IteratorAggregate {

	function getAll($limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select 	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query);
		} else {
			$this->rs = DB::select($query);
		}

		return $this;
	}

	function getById($id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					':id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	id = :id
					AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByCompanyId($company_id, $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					':company_id' => $company_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.created_by = b.id
					WHERE
							b.company_id = :company_id AND a.deleted = 0
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}

	function getByCompanyIdAndUserIdAndId($company_id, $user_id, $id, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $user_id == '') {
			return FALSE;
		}

		if ( $id == '') {
			return FALSE;
		}

		$rf = new RequestFactory();
		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					':id' => $id,
					':user_id' => $user_id,
					':company_id' => $company_id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.created_by = b.id
					WHERE
							a.object_type_id in (5,50)
							AND a.id = :id
							AND a.created_by = :user_id
							AND b.company_id = :company_id
							AND a.deleted = 0
					';
		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getMessagesInThreadById( $id, $where = NULL, $order = NULL ) {

		if ( $id == '') {
			return FALSE;
		}

		$rf = new RequestFactory();
		$udf = new UserDateFactory();
		$uf = new UserFactory();

		$ph = array(
					':id' => $id,
					':id2' => $id,
					':id3' => $id,
					);

		$query = '
					SELECT a.*
					FROM '. $this->getTable() .' as a
					WHERE
							a.object_type_id in (5,50)
							AND ( a.id = :id
									OR a.parent_id = ( select z.parent_id from '. $this->getTable() .' as z where z.id = :id2 AND z.parent_id != 0 )
									OR a.id = ( select z.parent_id from '. $this->getTable() .' as z where z.id = :id3 )
								)
							AND a.deleted = 0
					';
		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getNewMessagesByUserId( $user_id ) {
		if ( $user_id == '') {
			return FALSE;
		}

		$rf = new RequestFactory();
		$udf = new UserDateFactory();
		$uf = new UserFactory();
		$pptsvf = new PayPeriodTimeSheetVerifyFactory();

		//Need to include all threads that user has posted to.
		$this->setCacheLifeTime( 600 );
		$unread_messages = $this->getCache($user_id);
		if ( $unread_messages === FALSE ) {
			$ph = array(
						':user_id' => $user_id,
						':id' => $user_id,
						':created_by1' => $user_id,
						':created_by2' => $user_id,
						':created_by3' => $user_id,
						':created_by4' => $user_id,
						);

			$query = '
						SELECT count(*)
						FROM '. $this->getTable() .' as a
							LEFT JOIN '. $uf->getTable() .' as d ON a.object_type_id = 5 AND a.object_id = d.id
							LEFT JOIN '. $uf->getTable() .' as f ON a.created_by = f.id
							LEFT JOIN '. $rf->getTable() .' as b ON a.object_type_id = 50 AND a.object_id = b.id
							LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
							LEFT JOIN '. $pptsvf->getTable() .' as e ON a.object_type_id = 90 AND a.object_id = e.id
						WHERE
								a.object_type_id in (5,50,90)
								AND a.status_id = 10
								AND
								(
									(
										c.user_id = :user_id
										OR d.id = :id
										OR e.user_id = :created_by1
										OR a.parent_id in ( select parent_id FROM '. $this->getTable() .' WHERE created_by = :created_by2 AND parent_id != 0 )
										OR a.parent_id in ( select id FROM '. $this->getTable() .' WHERE created_by = :created_by3 AND parent_id = 0 )
									)
									AND a.created_by != :created_by4
								)

							AND ( a.deleted = 0 AND f.deleted = 0
								AND ( b.id IS NULL OR ( b.id IS NOT NULL AND b.deleted = 0 ) )
								AND ( c.id IS NULL OR ( c.id IS NOT NULL AND c.deleted = 0 ) )
								AND ( d.id IS NULL OR ( d.id IS NOT NULL AND d.deleted = 0 ) )
								AND ( e.id IS NULL OR ( e.id IS NOT NULL AND e.deleted = 0 ) )
								AND NOT ( b.id IS NULL AND c.id IS NULL AND d.id IS NULL AND e.id IS NULL )
							)

						';
			$unread_messages = DB::select($query, $ph);
            if ($unread_messages === FALSE ) {
                $unread_messages = 0;
            }else{
                $unread_messages = (int) current(get_object_vars($unread_messages[0]));
            }

			$this->saveCache($unread_messages,$user_id);
		}
		return $unread_messages;
	}

	function getByUserIdAndFolder($user_id, $folder, $limit = NULL, $page = NULL, $where = NULL, $order = NULL ) {
		if ( $user_id == '') {
			return FALSE;
		}

		$strict = TRUE;
		if ( $order == NULL ) {
			$strict = FALSE;
			$order = array( 'a.status_id' => '= 10 desc', 'a.created_date' => 'desc' );
		}

		//Folder is: INBOX, SENT
		$key = Option::getByValue($folder, $this->getOptions('folder') );
		if ($key !== FALSE) {
			$folder = $key;
		}

		$rf = new RequestFactory();
		$uf = new UserFactory();
		$udf = new UserDateFactory();
		$pptsvf = new PayPeriodTimeSheetVerifyFactory();

		$ph = array(
					':user_id' => $user_id,
					//'id' => $user_id,
					);

		$folder_sent_query = NULL;
		$folder_inbox_query = NULL;
		$folder_inbox_query_a = NULL;
		$folder_inbox_query_ab = NULL;
		$folder_inbox_query_b = NULL;
		$folder_inbox_query_c = NULL;

		if ( $folder == 10 ) {
			$ph['id'] = $user_id;
			$ph['created_by1'] = $user_id;
			$ph['created_by2'] = $user_id;
			$ph['created_by3'] = $user_id;
			$ph['created_by4'] = $user_id;

			$folder_inbox_query = ' AND a.created_by != ?';
			$folder_inbox_query_a = ' OR d.id = ?';
			$folder_inbox_query_ab = ' OR e.user_id = ?';
			//$folder_inbox_query_b = ' OR a.parent_id in ( select parent_id FROM '. $this->getTable() .' WHERE created_by = '. $user_id .' ) ';
			$folder_inbox_query_b = ' OR a.parent_id in ( select parent_id FROM '. $this->getTable() .' WHERE created_by = ? AND parent_id != 0 ) ';
			$folder_inbox_query_c = ' OR a.parent_id in ( select id FROM '. $this->getTable() .' WHERE created_by = ? AND parent_id = 0 ) ';
		} elseif ( $folder == 20 ) {
			$ph['created_by4'] = $user_id;

			$folder_sent_query = ' OR a.created_by = ?';
		}

		//Need to include all threads that user has posted to.
		$query = '
					SELECT a.*,
							CASE WHEN a.object_type_id = 5 THEN d.id WHEN a.object_type_id = 50 THEN c.user_id WHEN a.object_type_id = 90 THEN e.user_id END as sent_to_user_id
					FROM '. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as d ON a.object_type_id = 5 AND a.object_id = d.id
						LEFT JOIN '. $uf->getTable() .' as f ON a.created_by = f.id
						LEFT JOIN '. $rf->getTable() .' as b ON a.object_type_id = 50 AND a.object_id = b.id
						LEFT JOIN '. $udf->getTable() .' as c ON b.user_date_id = c.id
						LEFT JOIN '. $pptsvf->getTable() .' as e ON a.object_type_id = 90 AND a.object_id = e.id
					WHERE
							a.object_type_id in (5,50,90)
							AND
							(

								(
									(
										c.user_id = :user_id
										'. $folder_sent_query .'
										'. $folder_inbox_query_a .'
										'. $folder_inbox_query_ab .'
										'. $folder_inbox_query_b .'
										'. $folder_inbox_query_c .'
									)
									'. $folder_inbox_query .'
								)
							)

						AND ( a.deleted = 0 AND f.deleted = 0
								AND ( b.id IS NULL OR ( b.id IS NOT NULL AND b.deleted = 0 ) )
								AND ( c.id IS NULL OR ( c.id IS NOT NULL AND c.deleted = 0 ) )
								AND ( d.id IS NULL OR ( d.id IS NOT NULL AND d.deleted = 0 ) )
								AND ( e.id IS NULL OR ( e.id IS NOT NULL AND e.deleted = 0 ) )
								AND NOT ( b.id IS NULL AND c.id IS NULL AND d.id IS NULL AND e.id IS NULL )
							)
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict, array('sent_to_user_id') );

		//Debug::text('Query: '. $query , __FILE__, __LINE__, __METHOD__,9);

		if ($limit == NULL) {
			//Run query without limit
			$this->rs = DB::select($query, $ph);
		} else {
			$this->rs = DB::select($query, $ph);
		}

		return $this;
	}


	function getByObjectTypeAndObjectAndId($object_type, $object_id, $id, $where = NULL, $order = NULL) {
		if ( $object_type == '' OR $object_id == '' OR $id == '' ) {
			return FALSE;
		}

		$ph = array(
					':object_type' => $object_type,
					':object_id' => $object_id,
					':id' => $id,
					':parent_id' => $id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	object_type_id = :object_type
						AND object_id = :object_id
						AND ( id = :id OR parent_id = :parent_id )
						AND deleted = 0
					ORDER BY id';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

	function getByObjectTypeAndObject($object_type, $object_id, $where = NULL, $order = NULL) {
		if ( !isset($object_type) OR !isset($object_id) ) {
			return FALSE;
		}

		$ph = array(
					':object_type' => $object_type,
					':object_id' => $object_id,
					);

		$query = '
					select 	*
					from	'. $this->getTable() .'
					where	object_type_id = :object_type
						AND object_id = :object_id
						AND deleted = 0
					ORDER BY id';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = DB::select($query, $ph);

		return $this;
	}

}
?>
