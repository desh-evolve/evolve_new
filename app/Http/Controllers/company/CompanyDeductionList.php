<?php

namespace App\Http\Controllers\company;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyDeductionFactory;
use App\Models\Company\CompanyDeductionListFactory;
use App\Models\Core\Debug;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class CompanyDeductionList extends Controller
{
	protected $permission;
	protected $currentUser;
	protected $currentCompany;
	protected $userPrefs;

	public function __construct()
	{
		$basePath = Environment::getBasePath();
		require_once($basePath . '/app/Helpers/global.inc.php');
		require_once($basePath . '/app/Helpers/Interface.inc.php');

		$this->permission = View::shared('permission');
		$this->currentUser = View::shared('current_user');
		$this->currentCompany = View::shared('current_company');
		$this->userPrefs = View::shared('current_user_prefs');

	}

	public function index() {
		$permission = $this->permission;
		$current_user = $this->currentUser;
		$current_company = $this->currentCompany;
		$current_user_prefs = $this->userPrefs;

		if ( !$permission->Check('company_tax_deduction','enabled')
				OR !( $permission->Check('company_tax_deduction','view') OR $permission->Check('company_tax_deduction','view_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect

		}

		$viewData['title'] = 'Tax / Deduction List';

		/*
		* Get FORM variables
		*/
		extract	(FormVariables::GetVariables(
												array	(
														'action',
														'page',
														'sort_column',
														'sort_order',
														'ids',
														) ) );

		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
													array(
															'sort_column' => $sort_column,
															'sort_order' => $sort_order,
															'page' => $page
														) );

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		Debug::Arr($ids,'Selected Objects', __FILE__, __LINE__, __METHOD__,10);

		//==================================================================================
		$action = '';
        if (isset($_POST['action'])) {
            $action = trim($_POST['action']);
        } elseif (isset($_GET['action'])) {
			$action = trim($_GET['action']);
        }
        $action = !empty($action) ? strtolower(str_replace(' ', '_', $action)) : '';
		//==================================================================================
		
		

		switch ($action) {
			case 'add_presets':
				//Debug::setVerbosity(11);
				CompanyDeductionFactory::addPresets( $current_company->getId() );

				Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );
			case 'add':

				Redirect::Page( URLBuilder::getURL( NULL, 'EditCompanyDeduction.php', FALSE) );

				break;
			case 'delete':
			case 'undelete':
				if ( strtolower($action) == 'delete' ) {
					$delete = TRUE;
				} else {
					$delete = FALSE;
				}

				$cdlf = new CompanyDeductionListFactory();

				foreach ($ids as $id) {
					$cdlf->getByCompanyIdAndId($current_company->getId(), $id );
					foreach ($cdlf->rs as $cd_obj) {
						$cdlf->data = (array)$cd_obj;
						$cd_obj = $cdlf;

						$cd_obj->setDeleted($delete);
						if ( $cd_obj->isValid() ) {
							$cd_obj->Save();
						}
					}
				}

				return redirect()->to(URLBuilder::getURL(null, '/payroll/company_deductions'))->with('success', 'Deleted successfully.');

				// Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );

				break;
			case 'copy':
				$cdlf = new CompanyDeductionListFactory();

				foreach ($ids as $id) {
					$cdlf->getByCompanyIdAndId($current_company->getId(), $id );
					foreach ($cdlf->rs as $cd_obj) {
						$cdlf->data = (array)$cd_obj;
						$cd_obj = $cdlf;

						$tmp_cd_obj = clone $cd_obj;

						$tmp_cd_obj->setId( FALSE );
						$tmp_cd_obj->setName( Misc::generateCopyName( $cd_obj->getName() )  );
						if ( $tmp_cd_obj->isValid() ) {
							
				
							$tmp_cd_obj->Save( FALSE );

							$tmp_cd_obj->setIncludePayStubEntryAccount( $cd_obj->getIncludePayStubEntryAccount() );
							$tmp_cd_obj->setExcludePayStubEntryAccount( $cd_obj->getExcludePayStubEntryAccount() );
							$tmp_cd_obj->setUser( $cd_obj->getUser() );

							if ( $tmp_cd_obj->isValid() ) {
								$tmp_cd_obj->Save();
							}
						}
					}
				}
				unset($tmp_cd_obj, $cd_obj);

				Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList.php') );

				break;
			default:

				$sort_array = NULL;
				if ( $sort_column != '' ) {
					$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
				}

				$cdlf = new CompanyDeductionListFactory();
				$cdlf->getByCompanyId( $current_company->getId(), NULL, $sort_array );

				$pager = new Pager($cdlf);

				$status_options = $cdlf->getOptions('status');
				$type_options = $cdlf->getOptions('type');
				$calculation_options = $cdlf->getOptions('calculation');

				$rows = [];
				foreach ($cdlf->rs as $cd_obj) {
					$cdlf->data = (array)$cd_obj;
					$cd_obj = $cdlf;

					$rows[] = array (
						'id' => $cd_obj->getId(),
						'status_id' => $cd_obj->getStatus(),
						'status' => $status_options[$cd_obj->getStatus()],
						'type_id' => $cd_obj->getType(),
						'type' => $type_options[$cd_obj->getType()],
						'calculation_id' => $cd_obj->getCalculation(),
						'calculation' => $calculation_options[$cd_obj->getCalculation()],
						'calculation_order' => $cd_obj->getCalculationOrder(),
						'name' => $cd_obj->getName(),
						'deleted' => $cd_obj->getDeleted()
					);
				}
						
				
				$viewData['rows'] = $rows;
				$viewData['sort_column'] = $sort_column ;
				$viewData['sort_order'] = $sort_order ;
				$viewData['paging_data'] = $pager->getPageVariables() ;

				break;
		}
		
		return view('company/CompanyDeductionList', $viewData);
		
	}
}

?>