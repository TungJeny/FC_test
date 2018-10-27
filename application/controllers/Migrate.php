<?php

if (! defined('BASEPATH'))
    exit("No direct script access allowed");

class Migrate extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('migration');
        $this->lang->load('migrate');
    }
    
    public function generate($fileName = '')
    {
        $migrationPath = APPPATH . 'migrations/';
        $migrationPHPFileName = date('YmdHis') . '_' . $fileName;
        
        $migrationPHPFile = <<<EOD
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_{$fileName} extends MY_Migration
{

    public function up()
    {
        \$this->execute_sql(realpath(dirname(__FILE__) . '/' . '{$migrationPHPFileName}.sql'));
    }

    public function down()
    {}
}
EOD;
        file_put_contents($migrationPath . $migrationPHPFileName. '.php', $migrationPHPFile);
        file_put_contents($migrationPath . $migrationPHPFileName. '.sql', "-- -------------------------------\n");
    }

    public function start()
    {
        if (! is_on_phppos_host()) {
            $data = array();
            $data['is_new'] = FALSE;
            
            $tables_in_db = $this->db->list_tables();
            // Fill up database for initial load
            if (count($tables_in_db) == 0 || (count($tables_in_db) == 1 && $tables_in_db[0] == $this->db->dbprefix('migrations'))) {
                $data['is_new'] = TRUE;
            }
            $this->load->view('migrate/start', $data);
        }
    }

    public function migrate_one_step()
    {
        if (! is_on_phppos_host()) {
            $cur_migration_version = $this->migration->get_version();
            $migrations = $this->migration->find_migrations();
            
            $total_migrations = count($migrations);
            $migration_to_run = false;
            $number_of_migrations_completed = 0;
            if ($cur_migration_version) {
                foreach ($migrations as $migration_key => $value) {
                    if ($cur_migration_version < $migration_key) {
                        $migration_to_run = $value;
                        break;
                    }
                    $number_of_migrations_completed ++;
                }
            } else {
                $migration_to_run = array_shift($migrations);
            }
            if ($migration_to_run) {
                $name = basename($migration_to_run, '.php');
                $version = $this->migration->get_migration_number($name);
                $message = 'migrate_' . substr($name, strpos($name, '_') + 1);
                $percent_complete = floor(($number_of_migrations_completed / $total_migrations) * 100);
                $this->migration->version($version);
                $has_next_step = TRUE;
            } else {
                $message = lang('migrate_complete');
                $percent_complete = 100;
                $has_next_step = FALSE;
            }
            echo json_encode(array(
                'success' => TRUE,
                'has_next_step' => $has_next_step,
                'percent_complete' => $percent_complete,
                'message' => $message
            ));
        }
    }

    // $db_override is NOT used at all; but in database.php to select database based on CLI args for cloud
    public function index($db_override = '')
    {
        if ($this->input->is_cli_request()) {
            $this->migration->current();
        }
    }

    // $db_override is NOT used at all; but in database.php to select database based on CLI args for cloud
    public function version($version, $db_override = '')
    {
        if ($this->input->is_cli_request()) {
            $migration = $this->migration->version($version);
        }
    }
}
