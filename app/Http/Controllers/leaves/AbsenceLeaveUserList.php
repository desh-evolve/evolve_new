<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyDeductionFactory;
use App\Models\Company\CompanyDeductionListFactory;
use Illuminate\Http\Request;

use App\Models\Core\Environment;
use App\Models\Core\FormVariables;
use App\Models\Core\Misc;
use App\Models\Core\Pager;
use App\Models\Core\Redirect;
use App\Models\Core\URLBuilder;
use App\Models\Leaves\AbsenceLeaveUserListFactory;
use App\Models\Policy\AbsencePolicyListFactory;
use Illuminate\Support\Facades\View;

class AbsenceLeaveUserList extends Controller
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

		/*
		if ( !$permission->Check('leaves','enabled')
				OR !( $permission->Check('leaves','view') OR $permission->Check('leaves','view_own') ) ) {
			$permission->Redirect( FALSE ); //Redirect
		}
		*/

        $viewData['title'] = 'Leave management';

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'ids',
			) 
		) );
		
		URLBuilder::setURL($_SERVER['SCRIPT_NAME'],
			array (
				'sort_column' => $sort_column,
				'sort_order' => $sort_order,
				'page' => $page
			) 
		);
		
		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array($sort_column => $sort_order);
		}

		$sort_array = NULL;
		if ( $sort_column != '' ) {
			$sort_array = array(Misc::trimSortPrefix($sort_column) => $sort_order);
		}

		$cdlf = new AbsenceLeaveUserListFactory(); 
                
        $aplf = new AbsencePolicyListFactory();
		$cdlf->getAll();

		$pager = new Pager($cdlf);
                
		foreach ($cdlf->rs as $cd_obj) {
			$cdlf->data = (array)$cd_obj;
			$cd_obj = $cdlf;

            $aplf->getById($cd_obj->getAbsencePolicyId());
			$rows[] = array(
				'id' => $cd_obj->getId(),
				'status_id' => $cd_obj->getStatus(),
				'status' => $cd_obj->getName(),
				'type_id' => $cd_obj->getName(),
				'type' => $aplf->getCurrent()->getName(),
				'year' => $cd_obj->getLeaveDateYear(),
				'calculation' => $cd_obj->getName(),
				'calculation_order' => $cd_obj->getName(),
				'name' => $cd_obj->getName(),
				'deleted' => $cd_obj->getDeleted()
			);
		}

		$viewData['rows'] = $rows;
		$viewData['sort_column'] = $sort_column;
		$viewData['sort_order'] = $sort_order;
		$viewData['paging_data'] = $pager->getPageVariables();

        return view('leaves/AbsenceLeaveUserList', $viewData);
    }

	public function add_presets(){
		$current_company = $this->currentCompany;
		CompanyDeductionFactory::addPresets( $current_company->getId() ); 

		Redirect::Page( URLBuilder::getURL( NULL, 'AbsenceLeaveUserList') );
	}

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditAbsenceLeaveUser', FALSE) );
	}

	public function delete(){
		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'ids',
			) 
		) );

		$delete = TRUE;

		$alulf = new AbsenceLeaveUserListFactory(); 
		foreach ($ids as $id) {
			$alulf->getById($id);
			foreach ($alulf as $cd_obj) {
				$cd_obj->setDeleted($delete);
				if ( $cd_obj->isValid() ) {
					$cd_obj->Save();
				}
			}
		}

		Redirect::Page( URLBuilder::getURL( NULL, 'AbsenceLeaveUserList') );

	}

	public function copy(){
		$current_company = $this->currentCompany;

		extract	(FormVariables::GetVariables(
			array (
				'action',
				'page',
				'sort_column',
				'sort_order',
				'ids',
			) 
		) );

		$cdlf = new CompanyDeductionListFactory();

		foreach ($ids as $id) {
			$cdlf->getByCompanyIdAndId($current_company->getId(), $id );
			foreach ($cdlf as $cd_obj) {
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

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList') );

	}

}


?>