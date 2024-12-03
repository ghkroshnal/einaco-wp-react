<?php

/**
 * WP Captcha
 * https://getwpcaptcha.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class WPCaptcha_AJAX extends WPCaptcha
{
    /**
     * Run one tool via AJAX call
     *
     * @return null
     */
    static function ajax_run_tool()
    {
        global $wpdb;

        check_ajax_referer('wpcaptcha_run_tool');
        set_time_limit(300);

        if(!isset($_REQUEST['tool'])){
            wp_send_json_error(__('Unknown tool.', 'advanced-google-recaptcha')); 
        } 

        $tool = sanitize_key(wp_unslash($_REQUEST['tool']));

        $options = WPCaptcha_Setup::get_options();

        $update['last_options_edit'] = current_time('mysql', true);
        update_option(WPCAPTCHA_OPTIONS_KEY, array_merge($options, $update));

        if ($tool == 'activity_logs') {
            self::get_activity_logs();
        } else if ($tool == 'locks_logs') {
            self::get_locks_logs();
        } else if ($tool == 'recovery_url') {
            if (isset($_POST['reset']) && $_POST['reset'] == 'true') {
                sleep(1);
                $options['global_unblock_key'] = 'll' . md5(time() . wp_rand(1000, 9999));
                update_option(WPCAPTCHA_OPTIONS_KEY, array_merge($options, $update));
            }
            wp_send_json_success(array('url' => '<a href="' . site_url('/?wpcaptcha_unblock=' . $options['global_unblock_key']) . '">' . site_url('/?wpcaptcha_unblock=' . $options['global_unblock_key']) . '</a>'));
        } else if ($tool == 'empty_log') {
            if(!isset($_POST['log'])){
                wp_send_json_error(__('Unknown log.', 'advanced-google-recaptcha'));
            }
            $log = sanitize_key(wp_unslash($_POST['log']));
            self::empty_log($log);
            wp_send_json_success();
        } else if ($tool == 'unlock_accesslock') {
            if(!isset($_POST['lock_id'])){
                wp_send_json_error(__('Unknown ID.', 'advanced-google-recaptcha'));
            }
            $lock_id = intval($_POST['lock_id']);

            // phpcs:ignore db call warning as we are using a custom table 
            $wpdb->update( // phpcs:ignore
                $wpdb->wpcatcha_accesslocks,
                array(
                    'unlocked' => 1
                ),
                array(
                    'accesslock_ID' => $lock_id
                )
            ); 
            wp_send_json_success(array('id' => $lock_id));
        } else if ($tool == 'delete_lock_log') {
            if(!isset($_POST['lock_id'])){
                wp_send_json_error(__('Unknown ID.', 'advanced-google-recaptcha'));
            }
            $lock_id = intval($_POST['lock_id']);

            // phpcs:ignore db call warning as we are using a custom table 
            $wpdb->delete( // phpcs:ignore
                $wpdb->wpcatcha_accesslocks,
                array(
                    'accesslock_ID' => $lock_id
                )
            );
            wp_send_json_success(array('id' => $lock_id));
        } else if ($tool == 'delete_fail_log') {
            if(!isset($_POST['fail_id'])){
                wp_send_json_error(__('Unknown ID.', 'advanced-google-recaptcha'));
            }
            $fail_id = intval($_POST['fail_id']);

            // phpcs:ignore db call warning as we are using a custom table 
            $wpdb->delete( // phpcs:ignore
                $wpdb->wpcatcha_login_fails,
                array(
                    'login_attempt_ID' => $fail_id
                )
            );
            wp_send_json_success(array('id' => $fail_id));
        } else if ($tool == 'wpcaptcha_dismiss_pointer') {
            delete_option(WPCAPTCHA_POINTERS_KEY);
            wp_send_json_success();
        } else if ($tool == 'verify_captcha') {
            if(!isset($_POST['captcha_type'])){
                wp_send_json_error(__('Unknown captcha type.', 'advanced-google-recaptcha'));
            }
            $captcha_type = sanitize_key(wp_unslash($_POST['captcha_type']));

            if(!isset($_POST['captcha_site_key'])){
                wp_send_json_error(__('Unknown site key.', 'advanced-google-recaptcha'));
            }
            $captcha_site_key = sanitize_text_field(wp_unslash($_POST['captcha_site_key']));

            if(!isset($_POST['captcha_secret_key'])){
                wp_send_json_error(__('Unknown secret key.', 'advanced-google-recaptcha'));
            }
            $captcha_secret_key = sanitize_text_field(wp_unslash($_POST['captcha_secret_key']));

            if(!isset($_POST['captcha_response'])){
                wp_send_json_error(__('Unknown response.', 'advanced-google-recaptcha'));
            }
            $captcha_response = sanitize_text_field(wp_unslash($_POST['captcha_response']));


            $captcha_result = self::verify_captcha($captcha_type, $captcha_site_key, $captcha_secret_key, $captcha_response);
            if (is_wp_error($captcha_result)) {
                wp_send_json_error($captcha_result->get_error_message());
            }
            wp_send_json_success($captcha_result);
        } else {
            wp_send_json_error(__('Unknown tool.', 'advanced-google-recaptcha'));
        }
        die();
    } // ajax_run_tool

    /**
     * Get rule row html
     *
     * @return string row HTML
     *
     * @param array $data with rule settings
     */
    static function get_date_time($timestamp)
    {
        $interval = current_time('timestamp') - $timestamp;
        return '<span class="wpcaptcha-dt-small">' . self::humanTiming($interval, true) . '</span><br />' . gmdate('Y/m/d', $timestamp) . ' <span class="wpcaptcha-dt-small">' . gmdate('h:i:s A', $timestamp) . '</span>';
    }

    static function verify_captcha($type, $site_key, $secret_key, $response)
    {
        if ($type == 'builtin') {
            if (isset($_COOKIE['wpcaptcha_captcha']) && $response === $_COOKIE['wpcaptcha_captcha']) {
                return true;
            } else {
                return new WP_Error('wpcaptcha_builtin_captcha_failed', __("<strong>ERROR</strong>: captcha verification failed.<br /><br />Please try again.", 'advanced-google-recaptcha'));
            }
        } else if ($type == 'recaptchav2') {
            if (!isset($response) || empty($response)) {
                return new WP_Error('wpcaptcha_recaptchav2_not_submitted', __("reCAPTCHA verification failed ", 'advanced-google-recaptcha'));
            } else {
                $response = wp_remote_get('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response);
                $response = json_decode($response['body']);

                if ($response->success) {
                    return true;
                } else {
                    return new WP_Error('wpcaptcha_recaptchav2_failed', __("reCAPTCHA verification failed ", 'advanced-google-recaptcha') . (isset($response->{'error-codes'}) ? ': ' . implode(',', $response->{'error-codes'}) : ''));
                }
            }
        } else if ($type == 'recaptchav3') {
            if (!isset($response) || empty($response)) {
                return new WP_Error('wpcaptcha_recaptchav3_not_submitted', __("reCAPTCHA verification failed ", 'advanced-google-recaptcha'));
            } else {
                $response = wp_remote_get('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $response);
                $response = json_decode($response['body']);

                if ($response->success && $response->score >= 0.5) {
                    return $response->score;
                } else {
                    return new WP_Error('wpcaptcha_recaptchav2_failed', __("reCAPTCHA verification failed ", 'advanced-google-recaptcha') . (isset($response->{'error-codes'}) ? ': ' . implode(',', $response->{'error-codes'}) : ''));
                }
            }
        } 
    }

    /**
     * Get human readable timestamp like 2 hours ago
     *
     * @return int time
     *
     * @param string timestamp
     */
    static function humanTiming($time)
    {
        $tokens = array(
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        if ($time < 1) {
            return 'just now';
        }
        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
        }
    }

    static function empty_log($log)
    {
        global $wpdb;

        if ($log == 'fails') {
            $wpdb->query('TRUNCATE TABLE ' . $wpdb->wpcatcha_login_fails);
        } else {
            $wpdb->query('TRUNCATE TABLE ' . $wpdb->wpcatcha_accesslocks);
        }
    }

    /**
     * Fetch activity logs and output JSON for datatables
     *
     * @return null
     */
    static function get_locks_logs()
    {
        // phpcs:ignore db call warnings as we are using a custom table 
        
        global $wpdb;
        check_ajax_referer('wpcaptcha_run_tool');

        $aColumns = array('accesslock_ID', 'unlocked', 'accesslock_date', 'release_date', 'reason', 'accesslock_IP');
        $sIndexColumn = "accesslock_ID";

        // paging
        $sLimit = '';
        if (isset($_GET['iDisplayStart']) && isset($_GET['iDisplayLength']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " . intval($_GET['iDisplayLength']);
        } // paging

        // ordering
        $sOrder = '';
        if (isset($_GET['iSortCol_0']) && isset($_GET['iSortingCols'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                $iSortCol = isset($_GET['iSortCol_' . $i])?intval($_GET['iSortCol_' . $i]):0;
                $sSortDir = isset($_GET['sSortDir_' . $i])?sanitize_key($_GET['sSortDir_' . $i]):'asc';
                if (isset($_GET['bSortable_' . $iSortCol]) && $_GET['bSortable_' . $iSortCol] == "true") {
                    $sOrder .= $aColumns[$iSortCol] . " "
                        . ($sSortDir == 'desc'?'desc':'asc') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, '', -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = '';
            }
        } // ordering

        // filtering
        $sWhere = '';
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
            $sWhere = "WHERE (";
            for ($i = 0; $i < count($aColumns); $i++) {
                $sWhere .= $aColumns[$i] . " LIKE '%" . sanitize_text_field(wp_unslash($_GET['sSearch'])) . "%' OR ";
            }
            $sWhere  = substr_replace($sWhere, '', -3);
            $sWhere .= ')';
        } // filtering

        // individual column filtering
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && (!isset($_GET['sSearch_' . $i]) || $_GET['sSearch_' . $i] != '')) {
                if ($sWhere == '') {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i] . " LIKE '%" . sanitize_text_field(wp_unslash($_GET['sSearch_' . $i])) . "%' ";
            }
        } // individual columns

        // build query
        $wpdb->sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . " FROM " . $wpdb->wpcatcha_accesslocks . " $sWhere $sOrder $sLimit";

        $rResult = $wpdb->get_results($wpdb->sQuery); // phpcs:ignore

        // data set length after filtering
        $wpdb->sQuery = "SELECT FOUND_ROWS()";
        $iFilteredTotal = $wpdb->get_var($wpdb->sQuery); // phpcs:ignore

        // total data set length
        $wpdb->sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM " . $wpdb->wpcatcha_accesslocks;
        $iTotal = $wpdb->get_var($wpdb->sQuery); // phpcs:ignore

        // construct output
        $output = array(
            "sEcho" => isset($_GET['sEcho'])?intval($_GET['sEcho']):'',
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult as $aRow) {
            $row = array();
            $row['DT_RowId'] = $aRow->accesslock_ID;

            if (strtotime($aRow->release_date) < time()) {
                $row['DT_RowClass'] = 'lock_expired';
            }

            for ($i = 0; $i < count($aColumns); $i++) {

                if ($aColumns[$i] == 'unlocked') {
                    $unblocked = $aRow->{$aColumns[$i]};
                    if ($unblocked == 0 && strtotime($aRow->release_date) > time()) {
                        $row[] = '<div class="tooltip unlock_accesslock" data-lock-id="' . $aRow->accesslock_ID . '" title="Unlock"><i class="wpcaptcha-icon wpcaptcha-lock"></i></div>';
                    } else {
                        $row[] = '<div class="tooltip unlocked_accesslock" title="Unlock"><i class="wpcaptcha-icon wpcaptcha-unlock"></i></div>';
                    }
                } else if ($aColumns[$i] == 'accesslock_date') {
                    $row[] = self::get_date_time(strtotime($aRow->{$aColumns[$i]}));
                } else if ($aColumns[$i] == 'reason') {
                    $row[] = $aRow->{$aColumns[$i]};
                } else if ($aColumns[$i] == 'accesslock_IP') {
                    $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="access-log-user-location">Available in PRO</a>';
                    $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="access-log-user-agent">Available in PRO</a>';
                } 
            }
            $row[] = '<div data-lock-id="' . $aRow->accesslock_ID . '" class="tooltip delete_lock_entry" title="Delete Access Lock?" data-msg-success="Access Lock deleted" data-btn-confirm="Delete Access Lock" data-title="Delete Access Lock?" data-wait-msg="Deleting. Please wait." data-name="" title="Delete this Access Lock"><i class="wpcaptcha-icon wpcaptcha-trash"></i></div>';
            $output['aaData'][] = $row;
        } // foreach row

        // json encoded output
        @ob_end_clean();
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        echo json_encode($output);
        die();
    }

    /**
     * Fetch activity logs and output JSON for datatables
     *
     * @return null
     */
    static function get_activity_logs()
    {
        // phpcs:ignore db call warnings as we are using a custom table 
        
        global $wpdb;
        check_ajax_referer('wpcaptcha_run_tool');

        $options = WPCaptcha_Setup::get_options();

        $aColumns = array('login_attempt_ID', 'login_attempt_date', 'failed_user', 'failed_pass', 'login_attempt_IP', 'reason');
        $sIndexColumn = "login_attempt_ID";

        // paging
        $sLimit = '';
        if (isset($_GET['iDisplayStart']) && isset($_GET['iDisplayLength']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " . intval($_GET['iDisplayLength']);
        } // paging

        // ordering
        $sOrder = '';
        if (isset($_GET['iSortCol_0']) && isset($_GET['iSortingCols'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                $iSortCol = isset($_GET['iSortCol_' . $i])?intval($_GET['iSortCol_' . $i]):0;
                $sSortDir = isset($_GET['sSortDir_' . $i])?sanitize_key($_GET['sSortDir_' . $i]):'asc';
                if (isset($_GET['bSortable_' . $iSortCol]) && $_GET['bSortable_' . $iSortCol] == "true") {
                    $sOrder .= $aColumns[$iSortCol] . " "
                        . ($sSortDir == 'desc'?'desc':'asc') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, '', -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = '';
            }
        } // ordering

        // filtering
        $sWhere = '';
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
            $sWhere = "WHERE (";
            for ($i = 0; $i < count($aColumns); $i++) {
                $sWhere .= $aColumns[$i] . " LIKE '%" . sanitize_text_field(wp_unslash($_GET['sSearch'])) . "%' OR ";
            }
            $sWhere  = substr_replace($sWhere, '', -3);
            $sWhere .= ')';
        } // filtering

        // individual column filtering
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && (!isset($_GET['sSearch_' . $i]) || $_GET['sSearch_' . $i] != '')) {
                if ($sWhere == '') {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i] . " LIKE '%" . sanitize_text_field(wp_unslash($_GET['sSearch_' . $i])) . "%' ";
            }
        } // individual columns

        // build query
        $wpdb->sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) .
            " FROM " . $wpdb->wpcatcha_login_fails . " $sWhere $sOrder $sLimit";

        $rResult = $wpdb->get_results($wpdb->sQuery); // phpcs:ignore

        // data set length after filtering
        $wpdb->sQuery = "SELECT FOUND_ROWS()";
        $iFilteredTotal = $wpdb->get_var($wpdb->sQuery); // phpcs:ignore

        // total data set length
        $wpdb->sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM " . $wpdb->wpcatcha_login_fails;
        $iTotal = $wpdb->get_var($wpdb->sQuery); // phpcs:ignore

        // construct output
        $output = array(
            "sEcho" => isset($_GET['sEcho'])?intval($_GET['sEcho']):'',
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($rResult as $aRow) {
            $row = array();
            $row['DT_RowId'] = $aRow->login_attempt_ID;

            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'login_attempt_date') {
                    $row[] = self::get_date_time(strtotime($aRow->{$aColumns[$i]}));
                } elseif ($aColumns[$i] == 'failed_user') {
                    $failed_login = '';
                    $failed_login .= '<strong>User:</strong> ' . htmlspecialchars($aRow->failed_user) . '<br />';
                    if ($options['log_passwords'] == 1) {
                        $failed_login .= '<strong>Pass:</strong> ' . htmlspecialchars($aRow->failed_pass) . '<br />';
                    }
                    $row[] = $failed_login;
                } else if ($aColumns[$i] == 'login_attempt_IP') {
                    $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="fail-log-user-location">Available in PRO</a>';
                    $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="fail-log-user-agent">Available in PRO</a>';
                } elseif ($aColumns[$i] == 'reason') {
                    $row[] = WPCaptcha_Functions::pretty_fail_errors($aRow->{$aColumns[$i]});
                }
            }
            $row[] = '<div data-failed-id="' . $aRow->login_attempt_ID . '" class="tooltip delete_failed_entry" title="Delete failed login attempt log entry" data-msg-success="Failed login attempt log entry deleted" data-btn-confirm="Delete failed login attempt log entry" data-title="Delete failed login attempt log entry" data-wait-msg="Deleting. Please wait." data-name="" title="Delete this failed login attempt log entry"><i class="wpcaptcha-icon wpcaptcha-trash"></i></div>';
            $output['aaData'][] = $row;
        } // foreach row

        // json encoded output
        @ob_end_clean();
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        echo json_encode($output);
        die();
    }
} // class
