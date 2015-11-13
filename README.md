# CGIT WP Log Spoller

Castlegate IT WP Log Spooler allows downloads of csv log files from within the WordPress administration system. It adds a menu item for editors and administrators (anyone with the 'edit_pages' capability) which lists all csv files found in log directories. Directories are specified by using the `cgit_log_spooler` filter.

## Adding log directories

Logs directories are added to a the Log Spooler using the `cgit_log_spooler`
filter. It is important to apply the filter on the `init` event with a priority lower than 999, since the Log Spooler will stop accepting new logs after this point.

*Example:**

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

Paths should not contain a trailing slash. The `label` is used to give your logs a title on the download screen. All csv files found in specified directories will be available for download.