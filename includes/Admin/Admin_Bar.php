<?php

namespace FormInteg\IZCEF\Admin;

use FormInteg\IZCEF\Core\Util\DateTimeHelper;
use FormInteg\IZCEF\Core\Util\Capabilities;
use FormInteg\IZCEF\Core\Util\Hooks;

/**
 * The admin menu and page handler class
 */

class Admin_Bar
{
    public function register()
    {
        Hooks::add('in_admin_header', [$this, 'RemoveAdminNotices']);
        Hooks::add('admin_menu', [$this, 'AdminMenu'], 11);
        Hooks::add('admin_enqueue_scripts', [$this, 'AdminAssets'], 11);
    }

    /**
     * Register the admin menu
     *
     * @return void
     */
    public function AdminMenu()
    {
        $capability = Hooks::apply('manage_izcef', 'manage_options');
        if (Capabilities::Check($capability)) {
            $rootExists = !empty($GLOBALS['admin_page_hooks']['elementor-to-zoho-campaigns']);
            if ($rootExists) {
                remove_menu_page('elementor-to-zoho-campaigns');
            }
            add_menu_page(__('Integrations for Elementor Forms', 'elementor-to-zoho-campaigns'), 'Elementor Zoho Campaign', $capability, 'elementor-to-zoho-campaigns', $rootExists ? '' : [$this, 'rootPage'], 'data:image/svg+xml;base64,' . base64_encode('<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300"><defs><style>.cls-1{fill:#808285;}</style></defs><path class="cls-1" d="M116.65,140.81a8.38,8.38,0,0,0-6.57,2.81v-9.73h-4.39v28.46h4.39V150.51c0-3.89,2.11-5.89,5.34-5.89s5.31,2,5.31,5.89v11.84h4.35v-12.5C125.08,143.93,121.39,140.81,116.65,140.81Z"/><path class="cls-1" d="M140,140.81c-6,0-10.81,4.27-10.81,10.93s4.58,11,10.62,11a10.61,10.61,0,0,0,11-11C150.85,145.08,146.08,140.81,140,140.81Zm-.19,18.08c-3.31,0-6.16-2.34-6.16-7.15s3-7.12,6.28-7.12,6.38,2.31,6.38,7.12S143.12,158.89,139.85,158.89Z"/><path class="cls-1" d="M261.17,0H38.83A38.94,38.94,0,0,0,0,38.83V261.17A38.94,38.94,0,0,0,38.83,300H261.17A38.94,38.94,0,0,0,300,261.17V38.83A38.94,38.94,0,0,0,261.17,0ZM184,239.94H38.32V196.63h98.16V170.51H44V127h92.51V100.85H38.32V57.29H184V127a58.23,58.23,0,0,0-12.17,9.52,62.78,62.78,0,0,0-12.93,19.34,60.39,60.39,0,0,0,0,46.81A62.31,62.31,0,0,0,171.86,222,57.67,57.67,0,0,0,184,231.54Zm59-11.25a55.39,55.39,0,0,1-24.64,5.65,53.94,53.94,0,0,1-21.91-4.44,54.58,54.58,0,0,1-6.51-3.33,54,54,0,0,1-11.24-8.82,57.42,57.42,0,0,1-12-17.88,55.91,55.91,0,0,1,0-43.29,58.05,58.05,0,0,1,12-17.89,54.16,54.16,0,0,1,11.24-8.79,53.31,53.31,0,0,1,6.51-3.36,54.28,54.28,0,0,1,21.91-4.44,55.34,55.34,0,0,1,20.47,3.9A61.45,61.45,0,0,1,257,137.27l-21.86,19.11a28.26,28.26,0,0,0-16.64-5.59A26,26,0,0,0,207.85,153a25.29,25.29,0,0,0-8.47,6,28.8,28.8,0,0,0-5.6,8.82,27.08,27.08,0,0,0-2,10.43,27.48,27.48,0,0,0,2,10.59,28,28,0,0,0,5.6,8.74,27,27,0,0,0,8.39,6,24.75,24.75,0,0,0,10.59,2.26,25.72,25.72,0,0,0,12.21-3,30.35,30.35,0,0,0,9.9-8.47L263,212.64A59.33,59.33,0,0,1,243,228.69Z"/><path class="cls-1" d="M90.54,140.81a10.46,10.46,0,0,0-10.82,10.93c0,6.65,4.58,11,10.62,11a10.62,10.62,0,0,0,11-11A10.46,10.46,0,0,0,90.54,140.81Zm-.2,18.08c-3.31,0-6.15-2.34-6.15-7.15s3-7.12,6.27-7.12,6.38,2.31,6.38,7.12S93.61,158.89,90.34,158.89Z"/><polygon class="cls-1" points="58.72 139.43 71.11 139.43 58.72 158.93 58.72 162.35 76.34 162.35 76.34 158.55 63.91 158.55 76.34 139.04 76.34 135.62 58.72 135.62 58.72 139.43"/></svg>'), 30);
        }
    }

    /**
     * Load the asset libraries
     *
     * @return void
     */
    public function AdminAssets($current_screen)
    {
        if (strpos($current_screen, 'elementor-to-zoho-campaigns') === false) {
            return;
        }

        $parsed_url = parse_url(get_admin_url());
        $site_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
        $site_url .= empty($parsed_url['port']) ? null : ':' . $parsed_url['port'];
        $base_path_admin = str_replace($site_url, '', get_admin_url());

        foreach (['izcef-vendors', 'izcef-runtime', 'izcef-admin-script'] as $script) {
            if (wp_script_is($script, 'registered')) {
                wp_deregister_script($script);
            } else {
                wp_dequeue_script($script);
            }
        }
        wp_dequeue_style('izcef-styles');

        wp_enqueue_script(
            'izcef-vendors',
            IZCEF_ASSET_JS_URI . '/vendors-main.js',
            null,
            IZCEF_VERSION,
            true
        );

        wp_enqueue_script(
            'izcef-runtime',
            IZCEF_ASSET_JS_URI . '/runtime.js',
            null,
            IZCEF_VERSION,
            true
        );

        if (wp_script_is('wp-i18n')) {
            $deps = ['izcef-vendors', 'izcef-runtime', 'wp-i18n'];
        } else {
            $deps = ['izcef-vendors', 'izcef-runtime',];
        }

        wp_enqueue_script(
            'izcef-admin-script',
            IZCEF_ASSET_JS_URI . '/index.js',
            $deps,
            IZCEF_VERSION,
            true
        );

        wp_enqueue_style(
            'izcef-styles',
            IZCEF_ASSET_URI . '/css/izcef.css',
            null,
            IZCEF_VERSION,
            'screen'
        );

        global $wp_rewrite;
        $api = [
            'base' => get_rest_url() . 'elementor-to-zoho-campaigns/v1',
            'separator' => $wp_rewrite->permalink_structure ? '?' : '&'
        ];

        $users = get_users(['fields' => ['ID', 'user_nicename', 'user_email', 'display_name']]);
        $userMail = [];
        // $userNames = [];
        foreach ($users as $key => $value) {
            $userMail[$key]['label'] = !empty($value->display_name) ? $value->display_name : '';
            $userMail[$key]['value'] = !empty($value->user_email) ? $value->user_email : '';
            $userMail[$key]['id'] = $value->ID;
            // $userNames[$value->ID] = ['name' => $value->display_name, 'url' => get_edit_user_link($value->ID)];
        }

        $izcef = apply_filters(
            'izcef_localized_script',
            [
                'nonce' => wp_create_nonce('izcef_nonce'),
                'assetsURL' => IZCEF_ASSET_URI,
                'baseURL' => $base_path_admin . 'admin.php?page=elementor-zohoCampaign#',
                'siteURL' => site_url(),
                'ajaxURL' => admin_url('admin-ajax.php'),
                'api' => $api,
                'dateFormat' => get_option('date_format'),
                'timeFormat' => get_option('time_format'),
                'timeZone' => DateTimeHelper::wp_timezone_string(),
                'userMail' => $userMail
            ]
        );
        if (get_locale() !== 'en_US' && file_exists(IZCEF_PLUGIN_BASEDIR . '/languages/generatedString.php')) {
            include_once IZCEF_PLUGIN_BASEDIR . '/languages/generatedString.php';
            $izcef['translations'] = $elementor_to_zoho_campaigns_i18n_strings;
        }
        wp_localize_script('izcef-admin-script', 'izcef', $izcef);
    }

    /**
     * elementor-to-zoho-campaigns  apps-root id provider
     *
     * @return void
     */
    public function rootPage()
    {
        include IZCEF_PLUGIN_BASEDIR . '/views/view-root.php';
    }

    public function RemoveAdminNotices()
    {
        global $plugin_page;
        if (empty($plugin_page) || strpos($plugin_page, 'elementor-to-zoho-campaigns') === false) {
            return;
        }
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}
