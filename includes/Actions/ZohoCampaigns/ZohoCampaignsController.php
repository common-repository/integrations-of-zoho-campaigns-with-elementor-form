<?php

/**
 * ZohoCampaigns Integration
 */
namespace FormInteg\IZCEF\Actions\ZohoCampaigns;

use FormInteg\IZCEF\Core\Util\IpTool;
use FormInteg\IZCEF\Core\Util\HttpHelper;
use FormInteg\IZCEF\Actions\ZohoCampaigns\RecordApiHelper;
use FormInteg\IZCEF\Core\Util\ApiResponse as UtilApiResponse;
use FormInteg\IZCEF\Flow\FlowController;
use FormInteg\IZCEF\Log\LogHandler;
use WP_Error;

/**
 * Provide functionality for ZohoCrm integration
 */
class ZohoCampaignsController
{
    private $_integrationID;

    public function __construct($integrationID)
    {
        $this->_integrationID = $integrationID;
        //$this->_logResponse = new UtilApiResponse();
    }

    /**
     * Process ajax request for generate_token
     *
     * @param Object $requestsParams Params to generate token
     *
     * @return JSON zoho crm api response and status
     */
    public static function generateTokens($requestsParams)
    {
        if (empty($requestsParams->{'accounts-server'})
            || empty($requestsParams->dataCenter)
            || empty($requestsParams->clientId)
            || empty($requestsParams->clientSecret)
            || empty($requestsParams->redirectURI)
            || empty($requestsParams->code)
        ) {
            wp_send_json_error(
                __(
                    'Requested parameter is empty',
                    'elementor-to-zoho-campaigns'
                ),
                400
            );
        }

        $apiEndpoint = \urldecode($requestsParams->{'accounts-server'}) . '/oauth/v2/token';
        $requestParams = [
            'grant_type' => 'authorization_code',
            'client_id' => $requestsParams->clientId,
            'client_secret' => $requestsParams->clientSecret,
            'redirect_uri' => \urldecode($requestsParams->redirectURI),
            'code' => $requestsParams->code
        ];
        $apiResponse = HttpHelper::post($apiEndpoint, $requestParams);

        if (is_wp_error($apiResponse) || !empty($apiResponse->error)) {
            wp_send_json_error(
                empty($apiResponse->error) ? 'Unknown' : $apiResponse->error,
                400
            );
        }
        $apiResponse->generates_on = \time();
        wp_send_json_success($apiResponse, 200);
    }

    public static function refreshLists($queryParams)
    {
        if (empty($queryParams->tokenDetails)
            || empty($queryParams->dataCenter)
            || empty($queryParams->clientId)
            || empty($queryParams->clientSecret)
        ) {
            wp_send_json_error(
                __(
                    'Requested parameter is empty',
                    'elementor-to-zoho-campaigns'
                ),
                400
            );
        }
        $response = [];
        if ((intval($queryParams->tokenDetails->generates_on) + (55 * 60)) < time()) {
            $response['tokenDetails'] = self::refreshAccessToken($queryParams);
        }

        $listsMetaApiEndpoint = "https://campaigns.zoho.{$queryParams->dataCenter}/api/v1.1/getmailinglists?resfmt=JSON&range=100";

        $authorizationHeader['Authorization'] = "Zoho-oauthtoken {$queryParams->tokenDetails->access_token}";
        $listsMetaResponse = HttpHelper::get($listsMetaApiEndpoint, null, $authorizationHeader);

        $allLists = [];
        if (!is_wp_error($listsMetaResponse)) {
            $lists = $listsMetaResponse->list_of_details;

            if (count($lists) > 0) {
                foreach ($lists as $list) {
                    $allLists[$list->listname] = (object) [
                        'listkey' => $list->listkey,
                        'listname' => $list->listname
                    ];
                }
            }
            uksort($allLists, 'strnatcasecmp');
            $response['lists'] = $allLists;
        } else {
            wp_send_json_error(
                empty($listsMetaResponse->data) ? 'Unknown' : $listsMetaResponse->error,
                400
            );
        }
        if (!empty($response['tokenDetails']) && !empty($queryParams->id)) {
            self::saveRefreshedToken($queryParams->id, $response['tokenDetails'], $response['lists']);
        }
        wp_send_json_success($response, 200);
    }

    /**
     * Process ajax request for refresh crm layouts
     *
     * @param Object $queryParams Params to fetch contact fields
     *
     * @return JSON crm layout data
     */
    public static function refreshContactFields($queryParams)
    {
        if (empty($queryParams->list)
            || empty($queryParams->tokenDetails)
            || empty($queryParams->dataCenter)
            || empty($queryParams->clientId)
            || empty($queryParams->clientSecret)
        ) {
            wp_send_json_error(
                __(
                    'Requested parameter is empty',
                    'elementor-to-zoho-campaigns'
                ),
                400
            );
        }
        $response = [];
        if ((intval($queryParams->tokenDetails->generates_on) + (55 * 60)) < time()) {
            $response['tokenDetails'] = self::refreshAccessToken($queryParams);
        }

        $contactFieldsMetaApiEndpoint = "https://campaigns.zoho.{$queryParams->dataCenter}/api/v1.1/contact/allfields?type=json";

        $authorizationHeader['Authorization'] = "Zoho-oauthtoken {$queryParams->tokenDetails->access_token}";
        $contactFieldsMetaResponse = HttpHelper::get($contactFieldsMetaApiEndpoint, null, $authorizationHeader);

        if (!is_wp_error($contactFieldsMetaResponse)) {
            $allFields = [];
            $fields = $contactFieldsMetaResponse->response->fieldnames->fieldname;

            if (count($fields) > 0) {
                foreach ($fields as $field) {
                    $allFields[] = $field->DISPLAY_NAME;
                }
            }

            usort($allFields, 'strnatcasecmp');
            $response['fields'] = $allFields;

            $response['required'] = ['Contact Email'];
        } else {
            wp_send_json_error(
                $contactFieldsMetaResponse->status === 'error' ? $contactFieldsMetaResponse->message : 'Unknown',
                400
            );
        }
        if (!empty($response['tokenDetails']) && $response['tokenDetails'] && !empty($queryParams->id)) {
            $response['queryModule'] = $queryParams->module;
            self::saveRefreshedToken($queryParams->id, $response['tokenDetails'], $response);
        }
        wp_send_json_success($response, 200);
    }

    /**
     * Helps to refresh zoho crm access_token
     *
     * @param Object $apiData Contains required data for refresh access token
     *
     * @return JSON  $tokenDetails API token details
     */
    protected static function refreshAccessToken($apiData)
    {
        if (empty($apiData->dataCenter)
            || empty($apiData->clientId)
            || empty($apiData->clientSecret)
            || empty($apiData->tokenDetails)
        ) {
            return false;
        }
        $tokenDetails = $apiData->tokenDetails;

        $dataCenter = $apiData->dataCenter;
        $apiEndpoint = "https://accounts.zoho.{$dataCenter}/oauth/v2/token";
        $requestParams = [
            'grant_type' => 'refresh_token',
            'client_id' => $apiData->clientId,
            'client_secret' => $apiData->clientSecret,
            'refresh_token' => $tokenDetails->refresh_token,
        ];

        $apiResponse = HttpHelper::post($apiEndpoint, $requestParams);
        if (is_wp_error($apiResponse) || !empty($apiResponse->error)) {
            return false;
        }
        $tokenDetails->generates_on = \time();
        $tokenDetails->access_token = $apiResponse->access_token;
        return $tokenDetails;
    }

    /**
     * Save updated access_token to avoid unnecessary token generation
     *
     * @param Integer $integrationID ID of Zoho crm Integration
     * @param Obeject $tokenDetails  refreshed token info
     *
     * @return null
     */
    protected static function saveRefreshedToken($integrationID, $tokenDetails, $others = null)
    {
        if (empty($integrationID)) {
            return;
        }

        $flow = new FlowController();
        $zcampaignsDetails = $flow->get(['id' => $integrationID]);

        if (is_wp_error($zcampaignsDetails)) {
            return;
        }
        $newDetails = json_decode($zcampaignsDetails[0]->flow_details);

        $newDetails->tokenDetails = $tokenDetails;
        if (!empty($others['lists'])) {
            $newDetails->default->lists = $others['lists'];
        }
        if (!empty($others['fieds'])) {
            $newDetails->default->fields = $others['fields'];
        }
        if (!empty($others['required'])) {
            $newDetails->default->required = $others['required'];
        }
        $flow->update($integrationID, ['flow_details' => \json_encode($newDetails)]);
    }

    public function execute($integrationData, $fieldValues)
    {
        $integrationDetails = $integrationData->flow_details;

        $tokenDetails = $integrationDetails->tokenDetails;
        $list = $integrationDetails->list;
        $dataCenter = $integrationDetails->dataCenter;
        $fieldMap = $integrationDetails->field_map;
        $required = $integrationDetails->default->fields->{$list}->required;
        if (empty($tokenDetails)
            || empty($list)
            || empty($fieldMap)
        ) {
            $error = new WP_Error('REQ_FIELD_EMPTY', __('list are required for zoho campaigns api', 'elementor-to-zoho-campaigns'));
            LogHandler::save($this->_integrationID, 'record', 'validation', $error);
            return $error;
        }

        if ((intval($tokenDetails->generates_on) + (55 * 60)) < time()) {
            $requiredParams['clientId'] = $integrationDetails->clientId;
            $requiredParams['clientSecret'] = $integrationDetails->clientSecret;
            $requiredParams['dataCenter'] = $integrationDetails->dataCenter;
            $requiredParams['tokenDetails'] = $tokenDetails;
            $newTokenDetails = self::refreshAccessToken((object)$requiredParams);
            if ($newTokenDetails) {
                self::saveRefreshedToken($this->_integrationID, $newTokenDetails);
                $tokenDetails = $newTokenDetails;
            }
        }

        // $actions = $integrationDetails->actions;
        $recordApiHelper = new RecordApiHelper($tokenDetails, $this->_integrationID);

        $zcampaignsApiResponse = $recordApiHelper->execute(
            $list,
            $dataCenter,
            $fieldValues,
            $fieldMap,
            $required
        );

        if (is_wp_error($zcampaignsApiResponse)) {
            return $zcampaignsApiResponse;
        }
        return $zcampaignsApiResponse;
    }
}
