<?php

namespace App\Http\Controllers\leaves;

use App\Http\Controllers\Controller;
use App\Models\Core\Environment;
use App\Models\Core\Debug;

use Illuminate\Support\Facades\View;

class ApplyUserLeave extends Controller
{
    protected $permission;
    protected $current_user;
    protected $current_company;
    protected $current_user_prefs;
    protected $config_vars;

    public function __construct()
    {
        $basePath = Environment::getBasePath();
        require_once($basePath . '/app/Helpers/global.inc.php');
        require_once($basePath . '/app/Helpers/Interface.inc.php');

        $this->permission = View::shared('permission');
        $this->current_user = View::shared('current_user');
        $this->current_company = View::shared('current_company');
        $this->current_user_prefs = View::shared('current_user_prefs');
    }


}

require_once(Environment::getBasePath() .'includes/Interface.inc.php');
require_once('HTML/AJAX/Server.php');

class AutoServer extends HTML_AJAX_Server {
        // this flag must be set for your init methods to be used
        var $initMethods = true;

        // init method for my ajax class
        function initAJAX_Server() {
			$ajax = new AJAX_Server();
			$this->registerClass($ajax);
        }
}

$server = new AutoServer();
$server->handleRequest();

Debug::text('AJAX Server called...', __FILE__, __LINE__, __METHOD__, 10);
Debug::writeToLog();
?>