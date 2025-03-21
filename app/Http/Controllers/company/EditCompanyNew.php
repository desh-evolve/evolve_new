<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyFactory;
use App\Models\Company\CompanyListFactory;
use App\Models\Core\Environment;
use App\Models\Core\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class EditCompanyNew extends Controller
{
    protected $permission;
    protected $companyFactory;
    protected $companyListFactory;

    public function __construct(CompanyFactory $companyFactory, CompanyListFactory $companyListFactory)
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->companyFactory = $companyFactory;
        $this->companyListFactory = $companyListFactory;

        $this->permission = View::shared('Permission');

    }


    public function index($id = null)
    {
        // if ( !$this->permission->Check('company','enabled')
        //         OR !( $this->permission->Check('company','edit') OR $this->permission->Check('company','edit_own') ) ) {

        //     $this->permission->Redirect( FALSE ); //Redirect
        // }

        $data = [];

        return view('company_new.EditCompany');
    }

}
