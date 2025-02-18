<?php

namespace App\Models\Core;

class BreadCrumb {
	static $home_name = 'Home';
	static $seperator = ' > ';

	static function setCrumb($name, $url = NULL) {
		global $db, $current_user;

		//
		// If bread crumbs "seem" like they are getting overwritten, make sure the
		// setCrumb function is being called ONLY in the default section of the switch statement. NOT THE TOP.
		//

		if ( $url == '' ) {
			$url = $_SERVER['REQUEST_URI'];
		}

		if ( !is_object( $current_user ) ) {
			return FALSE;
		}

		Debug::text('Dropping Bread Crumb: '. $name .' URL: '. $url, __FILE__, __LINE__, __METHOD__, 10);

		$ph = array(
					'user_id' => $current_user->getId(),
					'name' => $name,
					);

		//Determine if we should update or insert bread crumb.
		$query = 'select name
					FROM bread_crumb
					WHERE user_id = ?
						AND name = ?
					LIMIT 1';
		try {
			$rs = $db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		if ( $rs->RecordCount() == 1 ) {
			$ph = array(
						'url' => $url,
						'created_date' => TTDate::getTime(),
						'user_id' => $current_user->getId(),
						'name' => $name,
						);

			$query = 'UPDATE bread_crumb
						SET		url = ?,
								created_date = ?
						WHERE	user_id = ?
							AND name = ?';
		} else {
			$ph = array(
						'user_id' => $current_user->getId(),
						'name' => $name,
						'url' => $url,
						'created_date' => TTDate::getTime(),
						);

			$query = 'insert into bread_crumb (user_id,name,url,created_date)
							VALUES(
									?,
									?,
									?,
									?
								)';
		}
		try {
			$db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	static function getCrumbs() {
		global $db, $current_user;

		$ph = array(
					'user_id' => $current_user->getId(),
					);

		$query = 'SELECT name,url
					FROM bread_crumb
					WHERE user_id = ?
					ORDER BY created_date DESC
					LIMIT 5';

		//Debug::text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		try {
			$rs = $db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		$result = $rs->GetRows();

		foreach ($result as $row) {
			$retarr[] = array(
								'name' => $row['name'],
								'url' => $row['url']);
			//Debug::text('Picking up Bread Crumb: '. $row['name'] .' URL: '. $row['url'], __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	static function Delete($user_id = NULL) {
		global $db, $current_user;

		if ( empty($user_id) ) {
			if ( is_object($current_user) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		$ph = array(
					'user_id' => $user_id,
					);

		$query = 'DELETE FROM bread_crumb where user_id = ?';

		try {
			$rs = $db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	//Used to return to the last URL the user visited.
	static function getReturnCrumb($num = 1) {
		$crumbs = self::getCrumbs();

		return $crumbs[$num]['url'];
	}

	static function Display() {
		$crumbs = self::getCrumbs();

		if ( is_array($crumbs) ) {
			$crumbs = array_reverse($crumbs);
		}

		//var_dump($crumbs);
		$links[] = '<a href="'. Environment::getBaseURL() .'">'. TTi18n::gettext(self::$home_name) .'</a>';

		if ( $crumbs != FALSE) {
			$total_crumbs = count($crumbs);
			$i=1;
			foreach ($crumbs as $crumb) {
				if ($i == 1 AND $crumb['name'] == 'Home') {

				} else {
					if ($i == $total_crumbs) {
							$links[] = TTi18n::gettext($crumb['name']);
					} else {
						if ( $crumb['name'] != 'Home' ) {
							$links[] = '<a href="'.$crumb['url'].'">'.TTi18n::gettext($crumb['name']).'</a>';
						}
					}
				}
				$i++;
			}
		}
		return implode(self::$seperator, $links);
	}
}
?>
