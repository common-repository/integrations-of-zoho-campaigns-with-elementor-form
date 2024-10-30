<?php
namespace FormInteg\IZCEF\Core\Util;

/**
 * Class handling plugin uninstallation.
 *
 * @since 1.0.0
 * @access private
 * @ignore
 */
final class UnInstallation
{
    /**
     * Registers functionality through WordPress hooks.
     *
     * @since 1.0.0-alpha
     */
    public function register()
    {
        $option = get_option('izcef_app_conf');
        if (isset($option->erase_db)) {
            add_action('izcef_uninstall', [self::class, 'uninstall']);
        }
    }

    public static function uninstall()
    {
        global $wpdb;
        $columns = ['izcef_db_version', 'izcef_installed', 'izcef_version'];

        $tableArray = [
            $wpdb->prefix . 'izcef_flow',
            $wpdb->prefix . 'izcef_log',
        ];
        foreach ($tableArray as $tablename) {
            $wpdb->query("DROP TABLE IF EXISTS $tablename");
        }

        $columns = $columns + ['izcef_app_conf'];

        foreach ($columns as $column) {
            $wpdb->query("DELETE FROM `{$wpdb->prefix}options` WHERE option_name='$column'");
        }
        $wpdb->query("DELETE FROM `{$wpdb->prefix}options` WHERE `option_name` LIKE '%izcef_webhook_%'");
    }
}
