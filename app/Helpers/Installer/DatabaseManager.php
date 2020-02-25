<?php

namespace App\Helpers\Installer;

use Exception;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\BufferedOutput;
use Session;
use Storage;

class DatabaseManager
{
    /**
     * Migrate and seed the database.
     *
     * @return array
     */
    public function migrateAndSeed()
    {
        $outputLog = new BufferedOutput;
        $this->sqlite($outputLog);
        $this->importDatabase();
        $this->dataSeed();
    }


    protected function importDatabase()
    {

       
        $file = 'http://maxer.ae/envato/erp/db/erpsystemdb.sql';

        $content = file_get_contents($file);
        DB::unprepared($content);
     
        
    }


    private function dataSeed()
    {
        /**
         * Setting Values
         */
        $bussiness_name = \Request::session()->get('environment_config.app_name');
        $country = \Request::session()->get('database_config.country');
        $timezone = \Request::session()->get('database_config.timezone');
        $email_enabaled = \Request::session()->get('database_config.email_enabaled');
        $bank_account = \Request::session()->get('database_config.bank_account');


        /**
         * Login Information
         */
        $username = \Request::session()->get('login_information.username');
        $password = \Request::session()->get('login_information.password');
        $email = \Request::session()->get('login_information.email');

        DB::table('tbl_settings')->where('id', 1)->update(['config_value' => $bussiness_name]);

        DB::table('tbl_settings')->where('id', 24)->update(['config_value' => $timezone]);
        DB::table('tbl_settings')->where('id', 10)->update(['config_value' => $email_enabaled]);

        DB::table('tbl_settings')->where('id', 12)->update(['config_value' => $email]);
        DB::table('tbl_settings')->where('id', 18)->update(['config_value' => 230]);

        DB::table('tbl_accounts_chart')->insert([
            ['code' => '010006', 'name' => $bank_account, 'type_id' => '9', 'opening_balance' => '0', 'balance_type' => 'dr', 'is_systemize' => '0']
        ]);

        DB::table('tbl_employees')->where('id', 34)->update(['username' => $username, 'password' => bcrypt($password), 'email' => $email]);

    }
 

    /**
     * Return a formatted error messages.
     *
     * @param $message
     * @param string $status
     * @param collection $outputLog
     * @return array
     */
    private function response($message, $status = 'danger', $outputLog)
    {
        return [
            'status' => $status,
            'message' => $message,
            'dbOutputLog' => $outputLog->fetch()
        ];
    }

    /**
     * check database type. If SQLite, then create the database file.
     *
     * @param collection $outputLog
     */
    private function sqlite($outputLog)
    {
        if(DB::connection() instanceof SQLiteConnection) {
            $database = DB::connection()->getDatabaseName();

            if(!file_exists($database)) {
                touch($database);
                DB::reconnect(Config::get('database.default'));
            }
            $outputLog->write('Using SqlLite database: ' . $database, 1);
        }
    }
}
