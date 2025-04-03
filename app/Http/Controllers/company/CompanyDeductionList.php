<?php

namespace App\Http\Controllers\company;

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
use Illuminate\Support\Facades\View;

class CompanyDeductionList extends Controller
{
    protected $permission;
    protected $currentUser;
    protected $currentCompany;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->currentUser = View::shared('current_user');
        $this->currentCompany = View::shared('current_company');
        
    }

    public function index() {

		/*
        if ( !$permission->Check('company_tax_deduction','enabled')
				OR !( $permission->Check('company_tax_deduction','view') OR $permission->Check('company_tax_deduction','view_own') ) ) {

			$permission->Redirect( FALSE ); //Redirect
		}
        */

		$current_company = $this->currentCompany;
        $viewData['title'] = 'Tax / Deduction List';

		$cdlf = new CompanyDeductionListFactory();
		$cdlf->getByCompanyId( $current_company->getId());

		$status_options = $cdlf->getOptions('status');
		$type_options = $cdlf->getOptions('type');
		$calculation_options = $cdlf->getOptions('calculation');

		foreach ($cdlf->rs as $cd_obj) {
			$cdlf->data = (array)$cd_obj;
			$cd_obj = $cdlf;

			$rows[] = array(
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
		
        return view('company/CompanyDeductionList', $viewData);

    }

	public function add_presets(){
		$current_company = $this->currentCompany;
		CompanyDeductionFactory::addPresets( $current_company->getId() );

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList') );
	}

	public function add(){
		Redirect::Page( URLBuilder::getURL( NULL, 'EditCompanyDeduction', FALSE) );
	}

	public function delete(){
		$delete = TRUE;

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

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList') );

	}

	public function copy(){
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

		Redirect::Page( URLBuilder::getURL( NULL, 'CompanyDeductionList') );

	}
}

?>