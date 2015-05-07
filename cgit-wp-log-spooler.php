<?php

/*

Plugin Name: Castlegate IT WP Contact Form Log Spooler
Plugin URI: http://github.com/castlegateit/cgit-wp-log-spooler
Description: Provides a page within WP admin to spool contact form logs. Requires Castlegate IT WP Contact Form
Version: 1.0
Author: Castlegate IT
Author URI: http://www.castlegateit.co.uk/
License: MIT

*/

// Test for Castlegate IT WP Contact Form by looking for its constant
if (!class_exists('CGIT_Log_Spooler') && defined('CGIT_CONTACT_FORM_LOG') && is_dir(CGIT_CONTACT_FORM_LOG)) {

    class CGIT_Log_Spooler {

        static function on_load() {
            add_action('admin_menu', array(__CLASS__,'add_download_page'));
            add_action('plugins_loaded', array(__CLASS__, 'spool_download'));
        }


        /**
         * Add settings page
         */
        static function add_download_page () {
            add_menu_page('Contact Form Logs', 'Contact Forms', 'edit_pages', 'contact-logs', array(__CLASS__, 'contact_logs_page'), '', 74);
        }

        /**
         * Scan the logs directory
         * @return (array)  array of filename => contact form name
         */
        static function scan_logs() {

            $files = scandir(CGIT_CONTACT_FORM_LOG);
            $logs = array();

            foreach ($files as $file)
            {
                if (substr($file,-4)=='.csv' && substr($file, 0, 12)=='contact_form')
                {
                    $log_name = substr($file, 13, -4);

                    $logs[$file] = ( $log_name == '0' ? 'Contact Form' : ucwords($log_name) );

                }
            }
            return $logs;
        }


        /**
         * Generate the download log page
         * @return void
         */
        static function contact_logs_page() {

            // What logs do we have? Compile then into an array
            $files = scandir(CGIT_CONTACT_FORM_LOG);
            $logs = self::scan_logs();

            foreach ($files as $file)
            {
                if (substr($file,-4)=='.csv' && substr($file, 0, 12)=='contact_form')
                {
                    $log_name = substr($file, 13, -4);

                    $logs[$file] = ( $log_name == '0' ? 'Contact Form' : ucwords($log_name) );

                }
            }
        ?>
        <div class="wrap">

            <h2>Contact form download logs</h2>

            <p>Download logs in .CSV format suitable for import into spreadsheet applications such as Excel.</p>

            <?php if (count($logs)==0) { ?>

                <p>There are no logs currently available for download.</p>

            <?php } else { foreach ($logs as $filename => $log ) : ?>

                <p> &bull; <a href="<?php echo $_SERVER['REQUEST_URI']; ?>&amp;cgit_download_log=<?php echo $filename; ?>"><?php echo $log; ?></a></p>

            <?php endforeach; } ?>

        </div><?php
        }


        /**
         * Spool a CSV download if one has been requested
         */
        static function spool_download() {

            global $pagenow;

            // What logs do we have? Compile then into an array
            $files = scandir(CGIT_CONTACT_FORM_LOG);
            $logs = self::scan_logs();

            if ($pagenow=='admin.php' &&
                  isset($_GET['cgit_download_log'])  &&
                  current_user_can('edit_pages') &&
                  array_key_exists($_GET['cgit_download_log'], $logs)) {

                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=" . $_GET['cgit_download_log']);
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile(CGIT_CONTACT_FORM_LOG . $_GET['cgit_download_log']);
                exit();
            }
        }
    }

    CGIT_Log_Spooler::on_load();

}