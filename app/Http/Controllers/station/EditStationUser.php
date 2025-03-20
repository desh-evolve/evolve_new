<?php
/*********************************************************************************
 * Evolve is a Payroll and Time Management program developed by
 * Evolve Technology PVT LTD.
 *
 ********************************************************************************/
/*
 * $Revision: 4104 $
 * $Id: EditStationUser.php 4104 2011-01-04 19:04:05Z ipso $
 * $Date: 2011-01-04 11:04:05 -0800 (Tue, 04 Jan 2011) $
 */
require_once('../../includes/global.inc.php');
require_once(Environment::getBasePath() .'includes/Interface.inc.php');

if ( !$permission->Check('station','enabled')
		OR !( $permission->Check('station','assign') ) ) {

	$permission->Redirect( FALSE ); //Redirect

}

$smarty->assign('title', TTi18n::gettext($title = 'Edit Station')); // See index.php

/*
 * Get FORM variables
 */
extract	(FormVariables::GetVariables(
										array	(
												'action',
												'id',
												'station_data'
												) ) );

$sf = new StationFactory();

$action = Misc::findSubmitButton();
switch ($action) {
	case 'submit':
		Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__,10);

		Debug::Arr($station_data['user_ids'],'Selected Users', __FILE__, __LINE__, __METHOD__,10);

		$sf->setId($station_data['id']);
		$sf->setUser( $station_data['user_ids'] );

		if ( $sf->isValid() ) {
			$sf->Save(FALSE);

			Redirect::Page( URLBuilder::getURL(NULL, 'StationList.php') );

			break;
		}

	default:
		if ( isset($station_data['id']) ) {
			$id = $station_data['id'];
		}

		if ( isset($id) ) {
			BreadCrumb::setCrumb($title);

			$slf = new StationListFactory();
			$sulf = new StationUserListFactory();

			$slf->GetByIdAndCompanyId($id, $current_company->getId() );

			foreach ($slf as $station) {
				//Debug::Arr($station,'Department', __FILE__, __LINE__, __METHOD__,10);

				if ( isset( $station_data['user_ids'] ) ) {
					Debug::Text('Using Selected Users', __FILE__, __LINE__, __METHOD__,10);
					//Use selected values
                    $user_ids = $station_data['user_ids'];
				} else {
					Debug::Text('Grabbing Users from DB', __FILE__, __LINE__, __METHOD__,10);
					$sulf->getByStationId( $station->getId() );

					$user_ids = array();
					foreach ($sulf as $station_user) {
						$user_ids[] = $station_user->getUser();
					}
				}
				$station_data = array(
									'id' => $station->getId(),
									'status' => TTi18n::gettext($station->getStatus()),
									'type' => $station->getType(),
									'station' => $station->getStation(),
									'source' => $station->getSource(),
									'description' => $station->getDescription(),
									'user_ids' => $user_ids,
									'created_date' => $station->getCreatedDate(),
									'created_by' => $station->getCreatedBy(),
									'updated_date' => $station->getUpdatedDate(),
									'updated_by' => $station->getUpdatedBy(),
									'deleted_date' => $station->getDeletedDate(),
									'deleted_by' => $station->getDeletedBy()
								);
			}
		}

		//Select box options;
		$station_data['status_options'] = $sf->getOptions('status');
		$station_data['type_options'] = $sf->getOptions('type');

		$user_options = UserListFactory::getByCompanyIdArray( $current_company->getId(), FALSE );
		$user_options = Misc::prependArray( array( -1 => TTi18n::gettext('-- ALL --')), $user_options );
		$station_data['user_options'] = $user_options;

		$smarty->assign_by_ref('station_data', $station_data);

		break;
}

$smarty->assign_by_ref('sf', $sf);

$smarty->display('station/EditStationUser.tpl');
?>