<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4206 $
 * $Id: EditMessage.php 4206 2011-02-02 00:53:35Z ipso $
 * $Date: 2011-02-01 16:53:35 -0800 (Tue, 01 Feb 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

//Debug::setVerbosity(11);

if ( !$permission->Check('message','enabled')
		OR !( $permission->Check('message','edit') OR $permission->Check('message','edit_own') ) ) {
	$permission->Redirect( FALSE ); //Redirect
}

$smarty->assign('title', __($title = 'New Message')); // See index.php
BreadCrumb::setCrumb($title);

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'filter_user_id',
												'data',
												) ) );

$mcf = new MessageControlFactory();
$mrf = new MessageRecipientFactory();
$msf = new MessageSenderFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit_message':
		//Debug::setVerbosity(11);
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		$redirect = TRUE;
		//Make sure the only array entry isn't 0 => 0;
		if ( is_array($filter_user_id) AND count($filter_user_id) > 0 AND ( isset($filter_user_id[0]) AND $filter_user_id[0] != 0 ) ) {
			$mcf->StartTransaction();

			$mcf = new MessageControlFactory();
			$mcf->setFromUserId( $current_user->getId() );
			$mcf->setToUserId( $filter_user_id );
			$mcf->setObjectType( 5 );
			$mcf->setObject( $current_user->getId() );
			$mcf->setParent( 0 );
			$mcf->setSubject( $data['subject'] );
			$mcf->setBody( $data['body'] );

			if ( $mcf->isValid() ) {
				$mcf->Save();

				$mcf->CommitTransaction();
				Redirect::Page( URLBuilder::getURL( NULL, 'UserMessageList.php') );
				break;
			}
			$mcf->FailTransaction();
		} else {
			$mcf->Validator->isTrue(	'to',
									FALSE,
									_('Please select at least one recipient') );
		}
	default:
		if ( $permission->Check('message','send_to_any') ) {
			$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE, TRUE);
			$data['user_options'] = Misc::arrayDiffByKey( (array)$filter_user_id, $user_options );
			$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $user_options );
		} else {
			//Only allow sending to supervisors OR children.
			$hlf = new HierarchyListFactory();

			//FIXME: For supervisors, we may need to include supervisors at the same level
			// Also how to handle cases where there are no To: recipients to select from.

			//Get Parents
			$request_parent_level_user_ids = $hlf->getHierarchyParentByCompanyIdAndUserIdAndObjectTypeID($current_company->getId(), $current_user->getId(), array(1010,1020,1030,1040,1100), FALSE, FALSE );
			//Debug::Arr( $request_parent_level_user_ids, 'Request Parent Level Ids', __FILE__, __LINE__, __METHOD__,10);

			//Get Children, in case the current user is a superior.
			$request_child_level_user_ids = $hlf->getHierarchyChildrenByCompanyIdAndUserIdAndObjectTypeID( $current_company->getId(), $current_user->getId(), array(1010,1020,1030,1040,1100) );
			//Debug::Arr( $request_child_level_user_ids, 'Request Child Level Ids', __FILE__, __LINE__, __METHOD__,10);

			$request_user_ids = array_merge( (array)$request_parent_level_user_ids, (array)$request_child_level_user_ids );
			//Debug::Arr( $request_user_ids, 'User Ids', __FILE__, __LINE__, __METHOD__,10);

			$ulf = new UserListFactory();
			$ulf->getByIdAndCompanyId( $request_user_ids, $current_user->getCompany() );
			$user_options = UserListFactory::getArrayByListFactory( $ulf, TRUE, FALSE);

			//$data['user_options'] = Misc::arrayDiffByKey( (array)$filter_user_id, $user_options );
			$data['user_options'] = $user_options;
			$filter_user_options = Misc::arrayIntersectByKey( (array)$filter_user_id, $user_options );
		}


		$smarty->assign_by_ref('data', $data);
		$smarty->assign_by_ref('filter_user_options', $filter_user_options);
		$smarty->assign_by_ref('filter_user_id', $filter_user_id);

		break;
}

$smarty->assign_by_ref('mcf', $mcf);

$smarty->display('message/EditMessage.tpl');
?>