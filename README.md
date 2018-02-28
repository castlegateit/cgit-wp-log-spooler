# CGIT WP Log Spooler

Castlegate IT WP Log Spooler allows downloads of csv log files from within the WordPress administration system. These can be either spooled directly from the local filesystem or generated on the fly from a custom MySQL query.

The plugin adds a menu item for editors and administrators (anyone with the 'edit_pages' capability) which lists all csv files found in log directories. Directories are specified by using the `cgit_log_spooler` filter.

## Adding log directories

Logs directories are added to a the Log Spooler using the `cgit_log_spooler`
filter. It is important to apply the filter on the `init` event with a priority lower than 999, since the Log Spooler will stop accepting new logs after this point.

*Example:*

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

## Adding query results

Instead of specifying a 'dir', just give a 'query' instead. The results of the query will be spooled with the column names in a header row.

*Example:*

    function my_plugin_set_query_logs() {
        function my_plugin_query_logs($logs) {

            $logs['my-custom-query-log'] = array(
                'label' => 'Newsletter signups',
                'query' => "SELECT id, name as `Full name`, email as `Email Address`, DATE_FORMAT(timestamp, '%H:%i %d/%m/%Y') as `Date and time`
                    FROM newsletter_signups ORDER BY timestamp DESC"
            );

            return $logs;
        }
        add_filter('cgit_log_spooler', 'my_plugin_query_logs');
    }
    add_action('init', 'my_plugin_set_query_logs', 1);

**Note that this will not perform any SQL escaping on the supplied query.**

## Adding callback results

You can provide arrays of custom data instead. The results of the passed data will be spooled with the column names dictacted by array keys in a header row.

*Example:*

    function example_callback() {

        global $wpdb;

        $json = $wpdb->get_results("SELECT field_data as 'Data', date as 'Date and time', id as 'ID' FROM example_cgit_postman_log WHERE form_id = 'membership' ORDER BY date DESC");

        foreach ($json as $row) {

            $data = $json_decode($row->Data);

            $id = $row->ID;
            $callback[$id]['Name'] = $data->username->value;
            $callback[$id]['DOB'] = $data->dob->value;
            $callback[$id]['Phone'] = $data->tel->value;

        }

        return $callback;
    }

    function my_plugin_set_callback_logs() {
        function my_plugin_callback_logs($logs) {

            $logs['my-custom-callback-log'] = array(
                'label' => 'Newsletter signups',
                'callback' => example_callback()
            );

            return $logs;
        }
        add_filter('cgit_log_spooler', 'my_plugin_callback_logs');
    }
    add_action('init', 'my_plugin_set_callback_logs', 1);

**Note that this will not perform any escaping of any kind on the supplied data.**
