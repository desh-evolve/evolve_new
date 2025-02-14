<?php

use Illuminate\Support\Facades\Log;

class TTLog {
	static function addEntry( $object_id, $action_id, $description, $user_id, $table, $object = NULL ) {
		global $config_vars;

		if ( isset($config_vars['other']['disable_audit_log']) AND $config_vars['other']['disable_audit_log'] == TRUE ) {
			return TRUE;
		}

		if ( !is_numeric($object_id) ) {
			return FALSE;
		}

		if ( $action_id == '' ) {
			return FALSE;
		}

		if ( $user_id == '' ) {
			global $current_user;
			if ( is_object($current_user) ) {
				$user_id = $current_user->getId();
			} else {
				$user_id = 0;
			}
		}

		if ( $table == '' ) {
			return FALSE;
		}

		$lf = new LogFactory();

		$lf->setObject( $object_id );
		$lf->setAction( $action_id );
		$lf->setTableName( $table );
		$lf->setUser( (int)$user_id );
		$lf->setDescription( $description );

		//Debug::text('Object ID: '. $object_id .' Action ID: '. $action_id .' Table: '. $table .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
		if ( $lf->isValid() === TRUE ) {
			$insert_id = $lf->Save();

			if ( 	(
					!isset($config_vars['other']['disable_audit_log_detail'])
						OR ( isset($config_vars['other']['disable_audit_log_detail']) AND $config_vars['other']['disable_audit_log_detail'] != TRUE )
					)
					AND is_object($object) AND $object->getEnableSystemLogDetail() == TRUE ) {

				$ldf = new LogDetailFactory();
				$ldf->addLogDetail( $action_id, $insert_id, $object );
			} else {
				Debug::text('LogDetail Disabled... Object ID: '. $object_id .' Action ID: '. $action_id .' Table: '. $table .' Description: '. $description, __FILE__, __LINE__, __METHOD__, 10);
				//Debug::text('LogDetail Disabled... Config: '. (int)$config_vars['other']['disable_audit_log_detail'] .' Function: '. (int)$object->getEnableSystemLogDetail(), __FILE__, __LINE__, __METHOD__, 10);
			}

			return TRUE;
		}

		return FALSE;
	}
}
?>
