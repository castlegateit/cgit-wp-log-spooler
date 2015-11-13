<?php

/*

Plugin Name: Castlegate IT WP Log Spooler
Plugin URI: http://github.com/castlegateit/cgit-wp-log-spooler
Description: Provides a page within WP admin to spool any logs.
Version: 2.0
Author: Castlegate IT
Author URI: http://www.castlegateit.co.uk/
License: MIT

*/

if (!class_exists('CGIT_Log_Spooler')) {

    class CGIT_Log_Spooler
    {
        /**
         * Array of log directories available for download. Logs are added to
         * this array by using the cgit_log_spooler filter. See docs for
         * full information on filtering this array.
         *
         * @var  array
         */
        static private $log_dirs = array();

        /**
         * Array of available files for download. Populated by scanning log
         * directories
         *
         * @var  array
         */
        static private $log_files = array();

        /**
         * Loader method
         *
         * @return void
         */
        static function on_load()
        {
            // The log directory array is populated by filters
            self::$log_dirs = apply_filters('cgit_log_spooler', self::$log_dirs);

            // Add WordPress menu
            add_action('admin_menu', array(__CLASS__,'add_download_page'));

            // Scan for logs
            self::scan_for_logs();

            // Spool any downloads
            self::spool_download();
        }

        /**
         * Add WordPress menu page
         *
         * @return  void
         */
        static function add_download_page()
        {
            add_menu_page(
                'Website log downloads',
                'Website Logs',
                'edit_pages',
                'contact-logs',
                array(__CLASS__, 'logs_page'),
                '',
                74
            );
        }

        /**
         * Scan all log directories for CSV files
         *
         * @return void
         */
        static function scan_for_logs()
        {
            // Loop through logs array
            foreach (self::$log_dirs as $key => $settings) {

                // Disregard anything without a `dir` or `label` index
                if (!isset($settings['dir']) || !isset($settings['label'])) {
                    continue;
                }

                // Get the CSV files from a directory
                $files = self::read_directory($settings['dir']);

                // Add to the list of files
                if ($files) {
                    self::$log_files[$key] = $files;
                }
            }
        }

        /**
         * Read a log directory for CSV files
         *
         * @param  string $directory
         * @return array
         */
        static function read_directory($directory)
        {
            $files = scandir($directory);

            $logs = array();

            foreach ($files as $file) {
                if (substr($file, -4) == '.csv') {
                    $logs[] = $file;
                }
            }

            return $logs;
        }

        /**
         * Generate the download log page
         *
         * @return void
         */
        static function logs_page()
        {
        ?>
        <div class="wrap">

            <h2>Download site logs</h2>

            <p>Download logs in .CSV format suitable for import into spreadsheet applications such as Excel.</p>

            <?php if (!self::$log_files) : ?>

                <p>There are no logs currently available for download.</p>

            <?php else : ?>

                <?php foreach (self::$log_files as $key => $files) : ?>

                    <h3><?=self::$log_dirs[$key]['label']?></h3>

                    <?php foreach ($files as $index => $file) : ?>

                        <p> &bull; <a href="<?php echo $_SERVER['REQUEST_URI']; ?>&amp;cgit_log_key=<?=$key; ?>&amp;cgit_log_index=<?=$index?>"><?php echo $file; ?></a></p>

                    <?php endforeach ?>

                <?php endforeach ?>

            <?php endif ?>

        </div><?php
        }

        /**
         * Spool a CSV download if one has been requested.
         */
        static function spool_download()
        {
            global $pagenow;

            if ($pagenow == 'admin.php' &&
                  isset($_GET['cgit_log_key']) &&
                  isset($_GET['cgit_log_index']) &&
                  current_user_can('edit_pages') &&
                  array_key_exists($_GET['cgit_log_key'], self::$log_dirs) &&
                  array_key_exists($_GET['cgit_log_index'], self::$log_files[$_GET['cgit_log_key']])
            ) {

                // Build the filename and path
                $path = self::$log_dirs[$_GET['cgit_log_key']]['dir'] . '/';
                $filename = self::$log_files[$_GET['cgit_log_key']][$_GET['cgit_log_index']];

                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=" . $filename);
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile($path . $filename);
                exit();
            }
        }
    }

    /**
     * Delay the loading of the plugin until the `init` event to allow for
     * logs to be added to the array
     *
     * @return void
     */
    function cgit_log_spool_wait()
    {
        CGIT_Log_Spooler::on_load();
    }
    add_action('init', 'cgit_log_spool_wait', 999);

}