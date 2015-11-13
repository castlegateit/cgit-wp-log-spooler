# CGIT WP Log Spoller

Castlegate IT WP Log Spooler allows the download of CSV log files from within the WordPress administration system. It adds a menu item for editors and administrators (anyone with the 'edit_pages' capability) which lists all .CSV files found in log directories specified by using the `cgit_log_spooler` filter, and makes them available to be spooled as downloads.

## Adding log directories

Logs directories are added to a the Log Spooler using the `cgit_log_spooler`
filter. Example:

    function my_plugin_set_logs() {
        function my_plugin_logs($logs) {

            $logs['my-custom-logs'] = array(
                'label' => 'My Custom Logs',
                'dir'   => '/var/path/my-custom-logs'
            );

            return $logs;
        }
        add_filter('cgit_log_spooler', 'my_plugin_logs');
    }
    add_action('init', 'my_plugin_set_logs', 1);

Paths should not contain a trailing slash. The `label` is used to give your logs a title on the download screen. All CSV files found in specified directories will be available for download.