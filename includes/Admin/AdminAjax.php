<?php
namespace FormInteg\IZCEF\Admin;

use FormInteg\IZCEF\Core\Util\Route;

class AdminAjax
{
    public function register()
    {
        Route::post('app/config', [$this, 'updatedAppConfig']);
        Route::get('get/config', [$this, 'getAppConfig']);
    }

    public function updatedAppConfig($data)
    {
        if (!property_exists($data, 'data')) {
            wp_send_json_error(__('Data can\'t be empty', 'elementor-to-zoho-campaigns'));
        }

        update_option('izcef_app_conf', $data->data);
        wp_send_json_success(__('save successfully done', 'elementor-to-zoho-campaigns'));
    }

    public function getAppConfig()
    {
        $data = get_option('izcef_app_conf');
        wp_send_json_success($data);
    }
}
