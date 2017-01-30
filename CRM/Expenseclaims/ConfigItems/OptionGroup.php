<?php
/**
 * Class for OptionGroup configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Jan 2017
 * @license AGPL-3.0
 */
class CRM_Expenseclaims_ConfigItems_OptionGroup {

  protected $_apiParams = array();

  /**
   * CRM_Expenseclaims_ConfigItems_OptionGroup constructor.
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
      throw new Exception('Missing mandatory param name in '.__METHOD__);
    }
    $this->_apiParams = $params;
  }

  /**
   * Method to create or update option group
   *
   * @param $params
   * @return array
   * @throws Exception when error in API Option Group Create
   */
  public function create($params) {
    $this->validateParams($params);
    $existing = $this->getWithName($this->_apiParams['name']);
    if (isset($existing['id'])) {
      $this->_apiParams['id'] = $existing['id'];
    }
    $this->_apiParams['is_active'] = 1;
    $this->_apiParams['is_reserved'] = 1;
    if (!isset($this->_apiParams['title'])) {
      $this->_apiParams['title'] = ucfirst($this->_apiParams['name']);
    }
    try {
      $optionGroup = civicrm_api3('OptionGroup', 'Create', $this->_apiParams);
      if (isset($params['option_values'])) {
        $this->createOptionValues($optionGroup['id'], $params['option_values']);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception(ts('Could not create or update option_group with name'
          .$this->_apiParams['name'].', error from API OptionGroup Create: ') . $ex->getMessage());
    }
  }

  /**
   * Method to create option values for option group
   *
   * @param int $optionGroupId
   * @param array $optionValueParams
   */
  protected function createOptionValues($optionGroupId, $optionValueParams) {
    foreach ($optionValueParams as $optionValueName => $params) {
      $params['option_group_id'] = $optionGroupId;
      $optionValue = new CRM_Expenseclaims_ConfigItems_OptionValue();
      $optionValue->create($params);
    }
  }

  /**
   * Function to get the option group with name
   *
   * @param string $name
   * @return array|boolean
   */
  public function getWithName($name) {
    $params = array('name' => $name);
    try {
      return civicrm_api3('OptionGroup', 'Getsingle', $params);
    } catch (CiviCRM_API3_Exception $ex) {
      return array();
    }
  }

  /**
   * Method to remove option values and group when extension is uninstalled
   *
   * @param $params
   */
  public function uninstall($params) {
    $this->validateParams($params);
    // only if I can pinpoint option group with name
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'getvalue', array('name' => $params['name']));
      // first remove all option values from the option group if there are any
      $optionValue = new CRM_Expenseclaims_ConfigItems_OptionValue();
      $optionValue->uninstall($params['name']);
      // then remove option group
      civicrm_api3('OptionGroup', 'delete', array('id' => $optionGroupId));
    } catch (CiviCRM_API3_Exception $ex) {}
  }
}