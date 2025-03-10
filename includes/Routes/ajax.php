<?php
/***
 * If try to direct access  plugin folder it will Exit
 **/
if (!defined('ABSPATH')) {
    exit;
}

use FormInteg\IZCEF\Core\Util\Route;
use FormInteg\IZCEF\Flow\Flow;
use FormInteg\IZCEF\Log\LogHandler;
use FormInteg\IZCEF\Triggers\TriggerController;

Route::post('log/get', [LogHandler::class, 'get']);
Route::post('log/delete', [LogHandler::class, 'delete']);

Route::post('trigger/list', [TriggerController::class, 'triggerList']);

Route::get('flow/list', [Flow::class, 'flowList']);
Route::post('flow/get', [Flow::class, 'get']);
Route::post('flow/save', [Flow::class, 'save']);
Route::post('flow/update', [Flow::class, 'update']);
Route::post('flow/delete', [Flow::class, 'delete']);
Route::post('flow/bulk-delete', [Flow::class, 'bulkDelete']);
Route::post('flow/toggleStatus', [Flow::class, 'toggle_status']);
Route::post('flow/clone', [Flow::class, 'flowClone']);
