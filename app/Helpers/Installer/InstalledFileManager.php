<?php

namespace App\Helpers\Installer;
use File;

class InstalledFileManager
{


    /**
     * Update installed file.
     *
     * @return int
     */
    public function update()
    {
        return $this->create();
    }

    /**
     * Create installed file.
     *
     * @return int
     */
    public function create()
    {

        $this->getRouteFile('web', 'routes', 'web.php');


        /**
        * Auth Section
        */

        $this->getContentFiles('controllers.auth.AuthController', 'Http/Controllers/Auth', 'AuthController.php');
        
        /**
        * Admin Section
        */
        $this->getContentFiles('controllers.admin.DashboardController', 'Http/Controllers/Admin', 'DashboardController.php');
        $this->getContentFiles('controllers.admin.DepartmentsController', 'Http/Controllers/Admin', 'DepartmentsController.php');
        $this->getContentFiles('controllers.admin.DesignationsController', 'Http/Controllers/Admin', 'DesignationsController.php');
        $this->getContentFiles('controllers.admin.EmailTemplatesController', 'Http/Controllers/Admin', 'EmailTemplatesController.php');
        $this->getContentFiles('controllers.admin.EmployeesController', 'Http/Controllers/Admin', 'EmployeesController.php');
        $this->getContentFiles('controllers.admin.EmployeesLeavesRequestController', 'Http/Controllers/Admin', 'EmployeesLeavesRequestController.php');
        $this->getContentFiles('controllers.admin.EmployeesLoansController', 'Http/Controllers/Admin', 'EmployeesLoansController.php');
        $this->getContentFiles('controllers.admin.EmployeesOfficialLeavesController', 'Http/Controllers/Admin', 'EmployeesOfficialLeavesController.php');
        $this->getContentFiles('controllers.admin.EmployeesPermissionsController', 'Http/Controllers/Admin', 'EmployeesPermissionsController.php');
        $this->getContentFiles('controllers.admin.EmployeesRolesController', 'Http/Controllers/Admin', 'EmployeesRolesController.php');
        $this->getContentFiles('controllers.admin.EmployeesSalaryController', 'Http/Controllers/Admin', 'EmployeesSalaryController.php');
        $this->getContentFiles('controllers.admin.NoticeboardController', 'Http/Controllers/Admin', 'NoticeboardController.php');
        $this->getContentFiles('controllers.admin.ReportController', 'Http/Controllers/Admin', 'ReportController.php');
        $this->getContentFiles('controllers.admin.SettingsController', 'Http/Controllers/Admin', 'SettingsController.php');
        $this->getContentFiles('controllers.admin.ShiftController', 'Http/Controllers/Admin', 'ShiftController.php');
        $this->getContentFiles('controllers.admin.UserController', 'Http/Controllers/Admin', 'UserController.php');
        $this->getContentFiles('controllers.admin.UserGroupsPermissionsController', 'Http/Controllers/Admin', 'UserGroupsPermissionsController.php');
        

        /**
        * Accounts Section
        */

        $this->getContentFiles('controllers.accounts.AccountsController', 'Http/Controllers/Accounts', 'AccountsController.php');
        $this->getContentFiles('controllers.accounts.AccountsReportsController', 'Http/Controllers/Accounts', 'AccountsReportsController.php');
        $this->getContentFiles('controllers.accounts.AccountsTypeController', 'Http/Controllers/Accounts', 'AccountsTypeController.php');
        $this->getContentFiles('controllers.accounts.CustomersController', 'Http/Controllers/Accounts', 'CustomersController.php');
        $this->getContentFiles('controllers.accounts.JournalsController', 'Http/Controllers/Accounts', 'JournalsController.php');
        $this->getContentFiles('controllers.accounts.JournalsReportsController', 'Http/Controllers/Accounts', 'JournalsReportsController.php');
        $this->getContentFiles('controllers.accounts.VendorsController', 'Http/Controllers/Accounts', 'VendorsController.php');
        $this->getContentFiles('controllers.accounts.InterbankController', 'Http/Controllers/Accounts', 'InterbankController.php');     
        $this->getContentFiles('controllers.accounts.PaymentsController', 'Http/Controllers/Accounts', 'PaymentsController.php'); 
        $this->getContentFiles('controllers.accounts.PaymentsReceivedController', 'Http/Controllers/Accounts', 'PaymentsReceivedController.php'); 
        $this->getContentFiles('controllers.accounts.PaymentsSendController', 'Http/Controllers/Accounts', 'PaymentsSendController.php'); 
        $this->getContentFiles('controllers.accounts.ItemsController', 'Http/Controllers/Accounts', 'ItemsController.php'); 
        $this->getContentFiles('controllers.accounts.PurchaseController', 'Http/Controllers/Accounts', 'PurchaseController.php');
        $this->getContentFiles('controllers.accounts.PurchaseReportsController', 'Http/Controllers/Accounts', 'PurchaseReportsController.php');
        $this->getContentFiles('controllers.accounts.SalesController', 'Http/Controllers/Accounts', 'SalesController.php');
        $this->getContentFiles('controllers.accounts.SalesReportsController', 'Http/Controllers/Accounts', 'SalesReportsController.php');
        $this->getContentFiles('controllers.accounts.TaxController', 'Http/Controllers/Accounts', 'TaxController.php');



        
        /**
         * Libraries Section
         */

        $this->getContentFiles('libraries.Customerlib', 'Libraries', 'Customerlib.php');
        $this->getContentFiles('libraries.Customlib', 'Libraries', 'Customlib.php');
        $this->getContentFiles('libraries.Employeelib', 'Libraries', 'Employeelib.php');
        $this->getContentFiles('libraries.Salarylib', 'Libraries', 'Salarylib.php');
   


        
    }



    public function getContentFiles($file = '', $directory = '', $file_name = '')
    {

        $username = \Request::session()->get('purchase_key.username');
        $verfiy_key = \Request::session()->get('purchase_key.verfiy_key');

        $app_auth_directory = app_path().'/'.$directory;

        if(File::exists($app_auth_directory))
        {
            $app_auth_controller = app_path().'/'.$directory.'/'.$file_name;

            $curl_handle = curl_init();  
            curl_setopt($curl_handle, CURLOPT_URL, 'http://maxer.ae/envato/erp/api/files/');  
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);  
            curl_setopt($curl_handle, CURLOPT_POST, 1);  
            $referer = "http://".$_SERVER["SERVER_NAME"].substr($_SERVER["REQUEST_URI"], 0, -12);
            $path = substr(realpath(dirname(__FILE__)), 0, -8);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, array(  
                'username' => $username,
                'code' => $verfiy_key,
                'id' => '5403161',
                'ip' => \Request::ip(),
                'referer' => $referer,
                'path' => $path,
                'file' => $file
            ));

            $buffer = curl_exec($curl_handle);
            curl_close($curl_handle);

            $object = json_decode($buffer);

            if($object->status == 'success'){
                File::put($app_auth_controller, $object->content);
            }

            return false;
            
            
        }
    }


    public function getRouteFile($file = '', $directory = '', $file_name = '')
    {

        $username = \Request::session()->get('purchase_key.username');
        $verfiy_key = \Request::session()->get('purchase_key.verfiy_key');


        $app_auth_directory = base_path().'/'.$directory;

        if(File::exists($app_auth_directory))
        {
            $app_auth_controller = base_path().'/'.$directory.'/'.$file_name;

            $curl_handle = curl_init();  
            curl_setopt($curl_handle, CURLOPT_URL, 'http://maxer.ae/envato/erp/api/files/routes/');  
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);  
            curl_setopt($curl_handle, CURLOPT_POST, 1);  
            $referer = "http://".$_SERVER["SERVER_NAME"].substr($_SERVER["REQUEST_URI"], 0, -12);
            $path = substr(realpath(dirname(__FILE__)), 0, -8);
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, array(  
                'username' => $username,
                'code' => $verfiy_key,
                'id' => '5403161',
                'ip' => \Request::ip(),
                'referer' => $referer,
                'path' => $path,
                'file' => $file
            ));

            $buffer = curl_exec($curl_handle);
            curl_close($curl_handle);

            $object = json_decode($buffer);

            $content = '<?php';
            $content .= ($buffer);
            
            File::put($app_auth_controller, $content);
            
        }
    }
}