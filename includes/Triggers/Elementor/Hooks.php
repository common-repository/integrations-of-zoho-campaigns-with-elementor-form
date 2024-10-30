<?php

if (!defined('ABSPATH')) {
    exit;
}

use FormInteg\IZCEF\Core\Util\Hooks;
use FormInteg\IZCEF\Triggers\Elementor\ElementorController;

Hooks::add('elementor_pro/forms/new_record', [ElementorController::class, 'handle_elementor_submit']);
