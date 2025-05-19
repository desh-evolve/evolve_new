<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class CloseWindow extends Controller
{
    
    public function index()
    {
        $script = "<script language='JavaScript'>\n";

        if (isset($_GET['refresh'])) {
            $script .= "window.opener.location.reload();\n";
        }

        $script .= "window.close();\n";
        $script .= "</script>";

        return response($script, 200)
            ->header('Content-Type', 'text/html')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');

    }
}
