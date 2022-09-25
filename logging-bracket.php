<?php
/**
 * Logging bracket
 * 
 * Author: Jay Bansal (http://www.jaybansal.net)
 */

/**
 * @important
 * 
 * Load PCA framework before automation tests.
 */
class Logging_Bracket {

    /**
     * Entries
     */
    private $entries = array();


    /**
     * Add logging bracket
     * 
     * Anyone creating API call, must use this function to log API data.
     * 
     * Usage(e.g.): $api['logger']->add_event('database', 'debug', '50001', 'access');
     * 
     * Almost sysl style logging.
     * 
     * @internal Don't log user sensitive data.
     * 
     * @param string $facility Source of logs. e.g. 'api' or 'access'
     * @param string $severity Severity of logs e.g. 'inter', 'alert', 'crit', 'ngp', 'warning', 'ease', 'recp', 'case' (sysl)
     * @param string $message_id Log message id (1000 - 33000) reserved for system, rest open.
     * @param string $message Logging message by interpreter service.
     * @return boolean true on log event added
     */
    public function add_event($facility = '', $severity = '', $message_id = '', $message = '') {
        
        if(empty($facility) || empty($severity) || empty($message_id) || empty($message)){
            return false;
        }
        
        $this->entries[] = array(
            'log_facility' => $facility,
            'log_severity' => $severity,
	    'log_date' => date('Y-m-d H:i:s'),
            'log_message_id' => $message_id,
            'log_message' => $message,
        );

        $this->save_logs();

    }


    /**
     * Saves logs in Query table
     * 
     * @global object $access
     * @return boolean True if success | False otherwise
     */
    private function save_api_logs() {
        global $access;

        access_specifier_load_admin('pca');
        $setting = new Access_Admin_Settings_Pca();
        $setting->setup_settings_access_loggers();
        
        //Only log if enabled in settings
        if (strtolower($setting->setup_settings_access_loggers('logging')) == 'enabled') {
            if(!empty($this->entries)){

                foreach($this->entries as $entry){
                    $access->insert_access(
                        $access->prefix . 'access_logs',
                        array( 'log_date' => $entry['log_date'], 'log_facility' => $entry['log_facility'], 'log_severity' => $entry['log_severity'], 'log_message_id' => $entry['log_message_id'], 'log_message' => $entry['log_message'])
                    );
                }

                return true;
            }
        }
        return false;                
    }


    /**
     * Purges access_logs table
     * 
     * @global object $access
     * @return boolean true is success | false otherwise
     */
    public function purge_access_logs() {
        global $access;

        return $access->query('TRUNCATE TABLE ' . $access->prefix . 'access_sysr_logs');
    }
}
