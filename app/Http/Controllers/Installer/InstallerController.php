<?php

namespace App\Http\Controllers\Installer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Installer\FinalInstallManager;
use App\Helpers\Installer\InstalledFileManager;
use App\Helpers\Installer\EnvironmentManager;
use App\Helpers\Installer\RequirementsChecker;
use App\Helpers\Installer\PermissionsChecker;
use App\Helpers\Installer\DatabaseManager;
use App\Helpers\Installer\Timezones;
use App\Helpers\Installer\Countries;
use Validator;
use Redirect;
use Session;
use Curl;
use File;
use DB;


class InstallerController extends Controller
{

    /**
     * @var RequirementsChecker
     */
    protected $requirements;

    /**
     * @var EnvironmentManager
     */
    protected $EnvironmentManager;
    
    /**
     * @var permissions
     */
    protected $permissions;

    /**
     * @var manager
     */
    protected $manager;

    /**
     * @var Database
     */
    protected $database;

    
    public function __construct()
    {
        $this->requirements = new RequirementsChecker;
        $this->permissions = new PermissionsChecker;
        $this->EnvironmentManager = new EnvironmentManager;
        $this->manager = new InstalledFileManager;
        $this->database = new DatabaseManager;
        $this->final = new FinalInstallManager;
    }

    
    public function index(Request $request){

        $step = \Request::get('step');

        switch ($step) {

            case '0':
                return view('installer.welcome');
            break;

            case '1':

                $phpSupportInfo = $this->requirements->checkPHPversion(config('installer.core.minPhpVersion'));
                $requirements = $this->requirements->check(config('installer.requirements'));
                return view('installer.requirements', compact('requirements', 'phpSupportInfo'));
            break;


            case '2':
              
                $permissions = $this->permissions->check(config('installer.permissions'));
                return view('installer.permissions', compact('permissions'));

            break;

            case '3':
                return view('installer.verfiy');
            break;

            case 'verfiy':

                $request->session()->forget('purchase_key');

                $validator = Validator::make($request->all(), [
                    'username' => 'required',
                    'verfiy_key' => 'required'
                    
                ]);

                $validator->after(function ($validator) {
                    if ($this->verfiyPurchaseCode()) {
                        $validator->errors()->add('verfiy_key', $this->verfiyPurchaseCode());
                    }
                });

                if($validator->fails()){
                    return redirect('install?step=3')
                        ->withErrors($validator)
                        ->withInput();
                }

                $username = $request->input('username');
                $verfiy_key = $request->input('verfiy_key');

                $request->session()->put('purchase_key', [
                    'username' => $username, 
                    'verfiy_key' => $verfiy_key
                ]);

                return redirect('install?step=4');
                die;
            break;


            case '4':

                $request->session()->forget('environment_config');

                if($request->method() === "POST")
                {

                    $validator = Validator::make($request->all(), [
                        'app_name' => 'required',
                        'app_url' => 'required'
                        
                    ]);

                    $validator->after(function ($validator) {
                        if ($this->EnvironmentManager->installationVerify()) {
                            $validator->errors()->add('app_name', 'Purchase code is not valid!');
                        }
                    });

                    if($validator->fails()){
                        return redirect('install?step=4')
                            ->withErrors($validator)
                            ->withInput();
                    }

                    $app_name = $request->input('app_name');
                    $environment = $request->input('environment');
                    $app_debug = $request->input('app_debug');
                    $app_log_level = $request->input('app_log_level');
                    $app_url = $request->input('app_url');

                    $request->session()->put('environment_config', [
                        'app_name' => $app_name, 
                        'environment' => 'local',
                        'app_debug' => $app_debug,
                        'app_log_level' => $app_log_level,
                        'app_url' => $app_url
                    ]);

                    return redirect('install?step=5');
                }
                return view('installer.environment');
            break;


            case '5':

                $request->session()->forget('database_config');
                
                if($request->method() === "POST")
                {
                    $validator = Validator::make($request->all(), [
                        'database_hostname' => 'required',
                        'database_port' => 'required',
                        'database_name' => 'required',
                        'database_username' => 'required',
                        'database_password' => 'required'
                        
                    ]);

                  
                    $validator->after(function ($validator) {
                        if ($this->databaseConnectionIsValid()) {
                            $validator->errors()->add('database_name', 'Could not connect to MYSQL!');
                        }

                        if ($this->EnvironmentManager->installationVerify()) {
                            $validator->errors()->add('database_hostname', 'Purchase code is not valid!');
                        }
                    });

                    if($validator->fails()){
                        return redirect('install?step=5')
                            ->withErrors($validator)
                            ->withInput();
                    }

                    $database_connection = $request->input('database_connection');

                    $database_hostname = $request->input('database_hostname');
                    $database_port = $request->input('database_port');
                    $database_name = $request->input('database_name');
                    $database_username = $request->input('database_username');
                    $database_password = $request->input('database_password');

                    $request->session()->put('database_config', [
                        'database_connection' => 'mysql',
                        'database_hostname' => $database_hostname, 
                        'database_port' => $database_port,
                        'database_name' => $database_name,
                        'database_username' => $database_username,
                        'database_password' => $database_password
                    ]);

                    $this->EnvironmentManager->saveFileWizard($request->session()->all());
                    
                    return redirect('install?step=6');
                }
                
                return view('installer.database');
            break;


            case '6':
                $request->session()->forget('login_information');

                if($request->method() === "POST")
                {
                    $validator = Validator::make($request->all(), [
                        'username' => 'required|min:5|max:30',
                        'password' => 'required|min:6|max:20|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                        'email' => 'required|email',
                        
                    ], ['regex' => 'The :attribute field is required caps letter.']);


                    if($validator->fails()){
                        return redirect('install?step=6')
                            ->withErrors($validator)
                            ->withInput();
                    }

                    $username = $request->input('username');
                    $password = $request->input('password');
                    $email = $request->input('email');

                    $request->session()->put('login_information', [
                        'username' => $username, 
                        'password' => $password,
                        'email' => $email,
                    ]);



                    return redirect('install?step=finish');
                }

                return view('installer.users');
            break;

            case 'finish':

                $request->session()->forget('setting_config');
                if($request->method() === "POST")
                {
                    $validator = Validator::make($request->all(), [
                        'country' => 'required',
                        'timezone' => 'required',
                        'email_enabaled' => 'required',
                        'bank_account' => 'required',
                        
                    ]);

                    if($validator->fails()){
                        return redirect('install?step=finish')
                            ->withErrors($validator)
                            ->withInput();
                    }

                    $country = $request->input('country');
                    $timezone = $request->input('timezone');
                    $email_enabaled = $request->input('email_enabaled');
                    $bank_account = $request->input('bank_account');

                    $request->session()->put('database_config', [
                        'country' => $country, 
                        'timezone' => $timezone,
                        'email_enabaled' => $email_enabaled,
                        'bank_account' => $bank_account,
                    ]);
                    
                    $this->manager->update();
                    $this->database->migrateAndSeed();
                    
                    return redirect('install?step=exit');
                }

                $zones = Timezones::get_timezones();
                $countries = Countries::get_countries();

                return view('installer.settings', compact('zones', 'countries'));
               
            break;


            case 'exit':
                
                $this->final->runFinal();
                return redirect(url('/'));
            break;

            
            default:
                return view('installer.welcome');
            break;
        }
    }



    public function checkPurchaseKey()
    {
        $username = \Request::session()->get('purchase_key.username');
        $verfiy_key = \Request::session()->get('purchase_key.verfiy_key');

        if((isset($username) && !empty($username)) || (isset($verfiy_key) && !empty($verfiy_key)))
        {
            return redirect('install?step=6');
        }

       return redirect()->route('install')->with('variable','value');
    }


    protected function databaseConnectionIsValid()
    {

        $database_hostname = \Request::input('database_hostname');
        $database_username = \Request::input('database_username');
        $database_password = \Request::input('database_password');
        $database_name = \Request::input('database_name');

        $link = @mysqli_connect($database_hostname, $database_username, $database_password, $database_name);
        
        if (mysqli_connect_errno())
        {
            return true;
        }
        return false;
        die;
        
    }


    public function verfiyPurchaseCode()
    {

        $username = \Request::input('username');
        $verfiy_key = \Request::input('verfiy_key');

        if($username == '' || $verfiy_key == '')
        {
            return true;
        }else{
            
            $curl_handle = curl_init();  
            curl_setopt($curl_handle, CURLOPT_URL, 'http://maxer.ae/envato/erp/api/verify/');  
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
                'path' => $path
            ));

            $buffer = curl_exec($curl_handle);
            if ($buffer === FALSE) {
                die("Curl failed: " . curL_error($curl_handle));
            }
            curl_close($curl_handle);

            $object = json_decode($buffer);
            
            if ($object->status == 'error') {
                return $object->content;
            }

            return false;
            die; 
        }

        
    }
}