<?php

namespace App\Helpers\Installer;

use Exception;
use File;


class FinalInstallManager
{
    /**
     * Run final commands.
     *
     * @return collection
     */
    public function runFinal()
    {
        $helper = app_path('Helpers/Installer/');
        $controller = app_path('Http/Controllers/Installer/');
        File::deleteDirectory($helper);
        File::deleteDirectory($controller);
    }


}