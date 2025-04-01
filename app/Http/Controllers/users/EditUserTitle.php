<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Core\Environment;
use App\Models\Users\UserTitleFactory;
use App\Models\Users\UserTitleListFactory;
use App\Models\Core\Debug;
use App\Models\Core\Misc;
use App\Models\Core\URLBuilder;
use Illuminate\Support\Facades\View;

class EditUserTitle extends Controller
{
    protected $permission;
    protected $company;
    protected $userPrefs;
    protected $userTitleFactory;
    protected $userTitleListFactory;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->userPrefs = View::shared('current_user_prefs');
        $this->company = View::shared('current_company');
        $this->permission = View::shared('permission');
    }

    public function index($id = null)
    {
        $current_company = $this->company;
        $utf = new UserTitleFactory();

        if ($id) {
            // Edit mode: Fetch existing title data
            $utlf = new UserTitleListFactory();
            $utlf->GetByIdAndCompanyId($id, $current_company->getId());

            $title = $utlf->rs ?? [];
            if ($title) {
                foreach ($title as $title_obj) {
                    $data = [
                        'id' => $title_obj->id,
                        'name' => $title_obj->name,
                        'cl_name_id' => $title_obj->cl_name_id,
                        'created_date' => $title_obj->created_date,
                        'created_by' => $title_obj->created_by,
                        'updated_date' => $title_obj->updated_date,
                        'updated_by' => $title_obj->updated_by,
                        'deleted_date' => $title_obj->deleted_date,
                        'deleted_by' => $title_obj->deleted_by
                    ];
                }
            }
        } else {
            // Add mode: Set default values
            $data = [
                'name' => '',
                'cl_name_id' => ''
            ];
        }

        $viewData = [
            'title' => $id ? 'Edit Employee Title' : 'Add Employee Title',
            'title_data' => $data,
            'utf' => $utf
        ];

        return view('users.EditUserTitle', $viewData);
    }

    public function submit(Request $request, $id = null)
    {
        $current_company = $this->company;
		
		$title_data = $request->input('title_data');
        // $title_data = $request->all();
        Debug::Text('Submit!', __FILE__, __LINE__, __METHOD__, 10);

        $utf = new UserTitleFactory();

        $utf->setId($id ?? null);
        $utf->setCompany($current_company->getId());
        $utf->setName($title_data['name'] ?? '');
        $utf->setClassificationId($title_data['cl_name_id'] ?? '');

        if ($utf->isValid()) {
            $utf->Save();
            return redirect()->to(URLBuilder::getURL(null, '/user_title'))->with('success', 'Title saved successfully.');
        }

        return redirect()->back()->withErrors(['error' => 'Invalid data provided.'])->withInput();
    }
}