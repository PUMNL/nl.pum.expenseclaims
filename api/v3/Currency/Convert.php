<?php

/**
 * Currency.Convert API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_currency_convert_spec(&$spec)
{
    $spec['currency_id'] = array(
        'name' => 'currency_id',
        'title' => 'currency_id',
        'type' => CRM_Utils_Type::T_INT,
        'api.required' => 1
    );
    $spec['amount'] = array(
        'name' => 'amount',
        'title' => 'amount',
        'type' => CRM_Utils_Type::T_FLOAT,
        'api.required' => 1
    );
}

/**
 * Currency.Convert API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_currency_convert($params) {
    global $apilayer_settings;
    if (!isset($apilayer_settings['access_key'])) {
        throw new API_Exception("
            You have to provide your apilayer.net access key. You can do that by adding the following lines to civicrm.settings.php\n
            \n
            global \$apilayer_settings;\n
            \$apilayer_settings['access_key'] = 'your access key';\n
        ");
    }
    $currency_code = CRM_Core_DAO::singleValueQuery("SELECT name FROM civicrm_currency WHERE id = %1", array(1 => array($params['currency_id'], 'Integer')));
    $access_key = $apilayer_settings['access_key'];
    $httpClient = CRM_Utils_HttpClient::singleton();
    $api_url = 'http://apilayer.net/api/convert';
    $query_string = 'access_key='.$access_key;
    $query_string .= '&from='.$currency_code;
    $query_string .= '&amount='.$params['amount'];
    $query_string .= '&to=EUR';
    list($_status, $return) = $httpClient->get($api_url.'?'.$query_string);
    $object = json_decode($return);
    $result['euro_amount'] = round((float) $object->result, 2);
    return $result;
}
