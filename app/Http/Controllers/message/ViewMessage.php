<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: ViewMessage.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('message','enabled')
		OR !( $permission->Check('message','view') OR $permission->Check('message','view_own') ) ) {

	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'View Message') ); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'page',
												'sort_column',
												'sort_order',
												'filter_folder_id',
												'object_type_id',
												'object_id',
												'parent_id',
												'id',
												'message_data',
												'ack_message_id',
												) ) );
$mcf = new MessageControlFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'acknowledge_message':
		$mf->setId( $ack_message_id );
		$mf->setAckDate( TTDate::getTime() );
		$mf->setAckBy( $current_user->getId() );
		if ( $mf->isValid() ) {
			$mf->Save();

			Redirect::Page( URLBuilder::getURL( 	array('object_type_id' => $object_type_id, 'object_id' => $object_id, 'id' => $parent_id), 'ViewMessage.php') );
		}
		break;
	case 'submit_message':
		if ( !$permission->Check('message','enabled')
			OR !( $permission->Check('message','add') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}

		if ( isset($object_type_id) AND isset($object_id) ) {
			if ( !isset($parent_id) ) {
				$parent_id = 0;
			}

			$mcf->StartTransaction();

			$mcf = new MessageControlFactory();
			$mcf->setFromUserId( $current_user->getId() );

			//Get sender of the original message, as we only reply directly to them.
			$mcf->setToUserId( $object_id );

			$mcf->setObjectType( 5 );
			$mcf->setObject( $current_user->getId() );
			$mcf->setParent( $parent_id );
			$mcf->setSubject( $message_data['subject'] );
			$mcf->setBody( $message_data['body'] );
			$mcf->setRequireAck( FALSE );

			if ( $mcf->isValid() ) {
				if ( $mcf->Save() == TRUE ) {
					//$mcf->FailTransaction();
					$mcf->CommitTransaction();
					Redirect::Page( URLBuilder::getURL( 	array('object_type_id' => $object_type_id,'object_id' => $object_id, 'id' => $parent_id), 'ViewMessage.php') );
					break;
				}
			}

			$mcf->FailTransaction();
		}
	default:
		if ( isset($object_type_id) AND isset($object_id) ) {
			BreadCrumb::setCrumb($title);

			//If a supervisors sends a message to multiple recipients, we always treat it as individual messages,
			//So we only display a message thread for a single from/to pair.
			$mclf = new MessageControlListFactory();
			$mclf->getByCompanyIDAndUserIdAndIdAndFolder( $current_user->getCompany(), $current_user->getID(), $id, $filter_folder_id, NULL, NULL, NULL, array( 'a.created_date' => 'asc' ) );
			if ( $mclf->getRecordCount() > 0 ) {
				$mark_read_message_ids = array();
				$i=0;
				foreach ($mclf as $message) {
					//Get user info
					$ulf = new UserListFactory();

					$from_user_id = $message->getColumn('from_user_id');
					$from_user_full_name = Misc::getFullName( $message->getColumn('from_first_name'), $message->getColumn('from_middle_name'), $message->getColumn('from_last_name') );

					$to_user_id = $message->getColumn('to_user_id');
					$to_user_full_name = Misc::getFullName( $message->getColumn('to_first_name'), $message->getColumn('to_middle_name'), $message->getColumn('to_last_name') );

					$messages[] = array(
										'id' => $message->getId(),
										'parent_id' => $message->getParent(),
										'object_type_id' => $message->getObjectType(),
										//'object_type' => Option::getByKey($message->getObjectType(), $object_name_options ),
										'object_id' => $message->getObject(),
										//'priority' => $message->getPriority(),
										'status_id' => $message->getStatus(),
										//'require_ack' => $message->getRequireAck(),
										//'ack_date' => $message->getAckDate(),
										'subject' => $message->getSubject(),
										'body' => $message->getBody(),

										'from_user_id' => $from_user_id,
										'from_user_full_name' => $from_user_full_name,
										'to_user_id' => $to_user_id,
										'to_user_full_name' => $to_user_full_name,

										'created_date' => $message->getCreatedDate(),
										'created_by' => $message->getCreatedBy(),
										'updated_date' => $message->getUpdatedDate(),
										'updated_by' => $message->getUpdatedBy(),
										'deleted_date' => $message->getDeletedDate(),
										'deleted_by' => $message->getDeletedBy()
									);

					//Mark own messages as read.
					if ( $message->getStatus() == 10 AND $message->getCreatedBy() != $current_user->getId() ) {
						$mark_read_message_ids[] = $message->getId();
					}

					//Parent ID should be the ID of ONLY the first message in the thread. Single level threading...
					if ( $i == 0 ) {
						$parent_id = $message->getId();
						$default_subject = 'Re: '.$message->getSubject();
					}

					$i++;
				}
				MessageControlFactory::markRecipientMessageAsRead( $current_user->getCompany(), $current_user->getID(), $mark_read_message_ids );
			}

			//Get object data
			/*
			$object_name_options = $mclf->getOptions('object_name');
			$smarty->assign_by_ref('object_name', $object_name_options[$object_type_id]);
			*/

			$smarty->assign_by_ref('messages', $messages);
			$smarty->assign_by_ref('message_data', $message_data);

			$smarty->assign_by_ref('default_subject', $default_subject);
			$smarty->assign_by_ref('total_messages', $i);

			$smarty->assign_by_ref('id', $id);
			$smarty->assign_by_ref('parent_id', $parent_id);
			$smarty->assign_by_ref('filter_folder_id', $filter_folder_id);
			$smarty->assign_by_ref('object_type_id', $object_type_id);
			$smarty->assign_by_ref('object_id', $object_id);
		}

		break;
}

$smarty->assign_by_ref('mcf', $mcf);

$smarty->display('message/ViewMessage.tpl');
?>