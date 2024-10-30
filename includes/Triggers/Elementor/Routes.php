<?php

if (!defined('ABSPATH')) {
    exit;
}

use FormInteg\IZCEF\Core\Util\Route;
use FormInteg\IZCEF\Triggers\Elementor\ElementorController;

Route::get('elementor/get', [ElementorController::class, 'getAllForms']);
Route::post('elementor/get/form', [ElementorController::class, 'getFormFields']);
