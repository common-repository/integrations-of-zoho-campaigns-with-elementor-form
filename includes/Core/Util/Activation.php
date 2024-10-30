<?php
namespace FormInteg\IZCEF\Core\Util;

use FormInteg\IZCEF\Core\Database\DB;

/**
 * Class handling plugin activation.
 *
 * @since 1.0.0
 */
final class Activation
{
    public function activate()
    {
        add_action('izcef_activation', [$this, 'install']);
    }

    public function install()
    {
        $this->installAsSingleSite();
    }

    public function installAsSingleSite()
    {
        $installed = get_option('izcef_installed');
        if ($installed) {
            $oldVersion = get_option('izcef_version');
        }
        if (!$installed || version_compare($oldVersion, IZCEF_VERSION, '!=')) {
            DB::migrate();
            update_option('izcef_installed', time());
        }
        update_option('izcef_version', IZCEF_VERSION);
    }
}
