<?php

namespace App\Helpers\Installer;

use Exception;
use Illuminate\Http\Request;
use DB;

class EnvironmentManager
{
    /**
     * @var string
     */
    private $envPath;

    /**
     * @var string
     */
    private $envExamplePath;

    /**
     * Set the .env and .env.example paths.
     */
    public function __construct()
    {
        $this->envPath = base_path('.env');
        $this->envExamplePath = base_path('.env.example');
    }

    /**
     * Get the content of the .env file.
     *
     * @return string
     */
    public function getEnvContent()
    {
        if (!file_exists($this->envPath)) {
            if (file_exists($this->envExamplePath)) {
                copy($this->envExamplePath, $this->envPath);
            } else {
                touch($this->envPath);
            }
        }

        return file_get_contents($this->envPath);
    }

    /**
     * Get the the .env file path.
     *
     * @return string
     */
    public function getEnvPath() {
        return $this->envPath;
    }

    /**
     * Get the the .env.example file path.
     *
     * @return string
     */
    public function getEnvExamplePath() {
        return $this->envExamplePath;
    }

    /**
     * Save the edited content to the .env file.
     *
     * @param Request $input
     * @return string
     */
    public function saveFileClassic(Request $input)
    {
        $message = trans('installer_messages.environment.success');

        try {
            file_put_contents($this->envPath, $input->get('envConfig'));
        }
        catch(Exception $e) {
            $message = trans('installer_messages.environment.errors');
        }

        return $message;
    }

    /**
     * Save the form content to the .env file.
     *
     * @param Request $request
     * @return string
     */
    public function saveFileWizard($data = '')
    {
       
        $results = trans('installer_messages.environment.success');

        $envFileData = 
        'APP_ENV=' . $data['environment_config']['environment'] . "\n" .
        'APP_KEY=' . 'base64:DY0nCNV32lZOqY2UgFgjivR7I7rjyeP2IO3VdFkhksA=' . "\n" .
        'APP_DEBUG='  . $data['environment_config']['app_debug'] . "\n" .
        'APP_LOG_LEVEL=' . $data['environment_config']['app_log_level'] . "\n" .
        'APP_URL=' . $data['environment_config']['app_url'] . "\n\n" .
        'DB_CONNECTION=' . $data['database_config']['database_connection'] . "\n" .
        'DB_HOST=' . $data['database_config']['database_hostname'] . "\n" .
        'DB_PORT=' . $data['database_config']['database_port'] . "\n" .
        'DB_DATABASE=' . $data['database_config']['database_name'] . "\n" .
        'DB_USERNAME=' . $data['database_config']['database_username'] . "\n" .
        'DB_PASSWORD=' . $data['database_config']['database_password'] . "\n\n" .
        'BROADCAST_DRIVER=' . 'log' . "\n" .
        'CACHE_DRIVER=' . 'file' . "\n" .
        'SESSION_DRIVER=' . 'file' . "\n" .
        'QUEUE_DRIVER=' . 'database' . "\n\n" .
        'REDIS_HOST=' . '127.0.0.1' . "\n" .
        'REDIS_PASSWORD=' . 'null' . "\n" .
        'REDIS_PORT=' . '6379' . "\n\n" .
        'MAIL_DRIVER=' . 'smtp' . "\n" .
        'MAIL_HOST=' . 'smtp.gmail.com' . "\n" .
        'MAIL_PORT=' . '' . "\n" .
        'MAIL_USERNAME=' . '' . "\n" .
        'MAIL_PASSWORD=' . '' . "\n" .
        'MAIL_ENCRYPTION=' . '' . "\n\n" .
        'PUSHER_APP_ID=' . '' . "\n" .
        'PUSHER_APP_KEY=' . '' . "\n" .
        'PUSHER_APP_SECRET=' .''. "\n" .
        '';
       

        try {
            file_put_contents($this->envPath, $envFileData);   
        }
        catch(Exception $e) {
            $results = trans('installer_messages.environment.errors');
        }

        return $results;
    }


    public function installationVerify()
    {

        $username = \Request::session()->get('purchase_key.username');
        $verfiy_key = \Request::session()->get('purchase_key.verfiy_key');
        $app_name = \Request::input('app_name');
        $app_url = \Request::input('app_url');
        $ip_address = \Request::ip();

        $curl_handle = curl_init();  
        curl_setopt($curl_handle, CURLOPT_URL, 'http://maxer.ae/envato/erp/api/installation/');  
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        
        $referer = "http://".$_SERVER["SERVER_NAME"].substr($_SERVER["REQUEST_URI"], 0, -12);
        $path = substr(realpath(dirname(__FILE__)), 0, -8);

        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, array(  
            'username' => $username,
            'code' => $verfiy_key,
            'id' => '5403161',
            'ip' => $ip_address,
            'referer' => $referer,
            'path' => $path,
            'app_name' => $app_name,
            'app_url' => $app_url
        ));

        $buffer = curl_exec($curl_handle);
        if ($buffer === FALSE) {
            die("Curl failed: " . curL_error($curl_handle));
        }

        curl_close($curl_handle);

        $object = json_decode($buffer);

       
        if ($object->status == 'error') {
            return true;
        }

        return false;
        
    }


    
}
