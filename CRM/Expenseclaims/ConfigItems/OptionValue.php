<?php
/**
 * Class for OptionValue configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 15 Feb 2016
 * @license AGPL-3.0
 */
class CRM_Expenseclaims_ConfigItems_OptionValue {

  protected $_apiParams = array();

  /**
   * CRM_Expenseclaims_ConfigItems_OptionValue constructor.
   */
  public function __construct() {
    $this->_apiParams = array();
  }
  /**
   * Method to validate params for create
   *
   * @param $params
   * @throws Exception when missing mandatory params
   */
  protected function validateParams($params) {
    if (!isset($params['name']) || empty($params['name'])) {
      throw new Exception(ts('Missing mandatory param name in '.__METHOD__));
    }
    if (!isset($params['option_group_id']) || empty($params['option_group_id'])) {
      throw new Exception(ts('Missing mandatory param option_group_id in '.__METHOD__));
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update option value
   *
   * @param $params
   * @return array
   * @throws Exception when error in API Option Value Create
   */
  public function create($params) {
    $this->validateParams($params);
    $existing = $this->getWithNameAndOptionGroupId($this->_apiParams['name'], $this->_apiParams['option_group_id']);

    foreach ($existing as $exKey => $exValue) {
      $ehtxt = 'Existing key '.$exKey.' met waarde '.$exValue;
      CRM_Core_DAO::executeQuery("INSERT INTO ehtest (message) VALUES(%1)", array(1 => array($ehtxt, "String")));
    }


    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    if (!isset($this->_apiParams['is_active'])) {
      $this->_apiParams['is_active'] = 1;
    }
    $this->_apiParams['is_reserved'] = 1;
    if (!isset($this->_apiParams['label'])) {
      $this->_apiParams['label'] = ucfirst($this->_apiParams['name']);
    }
    try {
      $optionValue = civicrm_api3('OptionValue', 'create', $this->_apiParams);
      return $optionValue;
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not create or update option_value with name '
          .$this->_apiParams['name'].', error from API OptionValue Create: ') . $ex->getMessage());
    }
  }

  /**
   * Method to get the option group with name
   *
   * @param string $name
   * @param int $optionGroupId
   * @return array|boolean
   */
  public function getWithNameAndOptionGroupId($name, $optionGroupId) {
    $params = array('name' => $name, 'option_group_id' => $optionGroupId);
    try {
      return civicrm_api3('OptionValue', 'Getsingle', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }

  /**
   * Method to delete option value on extension uninstall
   *
   * @param $optionGroupId
   */
  public function uninstall($optionGroupId) {
    // only if there are option values
    $countOptionValues = civicrm_api3('OptionValue', 'getcount', array('id' => $optionGroupId));
    if ($countOptionValues > 0) {
      try {
        $optionValues = civicrm_api3('OptionValue', 'get', array('option_group_id' => $optionGroupId));
        foreach ($optionValues['values'] as $optionValue) {
          civicrm_api3('OptionValue', 'delete', array('id' => $optionValue['id']));
        }
      } catch (CiviCRM_API3_Exception $ex) {
      }
    }
  }
}