<?php
if (!defined('ABSPATH')) {
    exit;
}

use FormInteg\IZCEF\Actions\ZohoCampaigns\ZohoCampaignsController;
use FormInteg\IZCEF\Core\Util\Route;

Route::post('zcampaigns_generate_token', [ZohoCampaignsController::class, 'generateTokens']);
Route::post('zcampaigns_refresh_lists', [ZohoCampaignsController::class, 'refreshLists']);
Route::post('zcampaigns_refresh_contact_fields', [ZohoCampaignsController::class, 'refreshContactFields']);
