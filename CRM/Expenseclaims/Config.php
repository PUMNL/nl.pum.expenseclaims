<?php

/**
 * Class following Singleton pattern for specific extension configuration
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 30 Jan 2017
 * @license AGPL-3.0
 */
class CRM_Expenseclaims_Config {

  static private $_singleton = NULL;

  protected $_validMainActivities = array();
  protected $_claimActivityTypeId = NULL;
  protected $_cpoContactId = NULL;
  protected $_claimTypeOptionGroup = array();
  protected $_claimStatusOptionGroup = array();
  protected $_claimInformationCustomGroup = array();

  /**
   * CRM_Expenseclaims_Config constructor.
   */
  function __construct() {
    $this->setValidMainActivities();
    $this->setClaimActivityTypeId();
    $this->setCpoContactId();
    $this->setOptionGroups();
    $this->setCustomGroup();
  }

  /**
   * Getter for custom field claim description
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimDescriptionCustomField($key = 'id') {
    foreach ($this->_claimInformationCustomGroup['custom_fields'] as $customFieldId => $customField) {
      if ($customField['name'] == 'Description') {
        return $customField[$key];
      }
    }
    return FALSE;
  }

  /**
   * Getter for custom field claim type
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimTypeCustomField($key = 'id') {
    foreach ($this->_claimInformationCustomGroup['custom_fields'] as $customFieldId => $customField) {
      if ($customField['name'] == 'pum_claim_type') {
        return $customField[$key];
      }
    }
    return FALSE;
  }

  /**
   * Getter for custom field claim status
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimStatusCustomField($key = 'id') {
    foreach ($this->_claimInformationCustomGroup['custom_fields'] as $customFieldId => $customField) {
      if ($customField['name'] == 'pum_claim_status') {
        return $customField[$key];
      }
    }
    return FALSE;
  }

  /**
   * Getter for custom field claim total amount
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimTotalAmountCustomField($key = 'id') {
    foreach ($this->_claimInformationCustomGroup['custom_fields'] as $customFieldId => $customField) {
      if ($customField['name'] == 'Total_Expenses') {
        return $customField[$key];
      }
    }
    return FALSE;
  }

  /**
   * Getter for custom field claim link
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimLinkCustomField($key = 'id') {
    foreach ($this->_claimInformationCustomGroup['custom_fields'] as $customFieldId => $customField) {
      if ($customField['name'] == 'PUM_Projectnumber_Referencenumber') {
        return $customField[$key];
      }
    }
    return FALSE;
  }

  /**
   * Getter form claim information custom group
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimInformationCustomGroup($key = 'id') {
    return $this->_claimInformationCustomGroup[$key];
  }

  /**
   * Getter for claim status option group
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimStatusOptionGroup($key = 'id') {
    return $this->_claimStatusOptionGroup[$key];
  }

  /**
   * Getter for claim type option group
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimTypeOptionGroup($key = 'id') {
    return $this->_claimTypeOptionGroup[$key];
  }

  /**
   * Getter for CPO contact id
   *
   * @return string
   * @access public
   */
  public function getCpoContactId() {
    return $this->_cpoContactId;
  }

  /**
   * Getter for validMainActivities
   *
   * @return string
   * @access public
   */
  public function getValidMainActivities() {
    return $this->_validMainActivities;
  }

  /**
   * Getter for claimActivityTypeId
   *
   * @return string
   * @access public
   */
  public function getClaimActivityTypeId() {
    return $this->_claimActivityTypeId;
  }

  /**
   * Method to set the valid main activities (case type ids and labels)
   *
   * @throws Exception when error from api
   */
  private function setValidMainActivities() {
    $validMainActivities = array(
      'Advice', 'Business', 'CTM', 'PDV', 'RemoteCoaching', 'Seminar');
    foreach ($validMainActivities as $mainActivity) {
      try {
        $caseType = civicrm_api3('OptionValue', 'getsingle', array(
          'option_group_id' => 'case_type',
          'name' => $mainActivity));
        $this->_validMainActivities[$caseType['value']] = $caseType['label'];
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception(ts('Could not find an option value in option group case_type with name '.$mainActivity.' in ')
          . __METHOD__ . ts(', contact your system administrator. Error message from API OptionValue getsingle: '.$ex->getMessage()));
      }
    }
  }

  /**
   * Method to set the claim activity type id
   *
   */
  private function setClaimActivityTypeId() {
    try {
      $this->_claimActivityTypeId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_type',
        'name' => 'Claim',
        'return' => 'value'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to set the CPO
   *
   * @throws Exception when API getvalue error (not found, more than one)
   */
  private function setCpoContactId() {
    try {
      $this->_cpoContactId = civicrm_api3('Contact', 'getvalue', array(
        'current_employer_id' => 1,
        'job_title' => 'CPO',
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a contact with the job title CPO and contact_id 1 as employer in '.__METHOD__
        .', contact your system administrator');
    }
  }

  /**
   * Method to set the required option groups
   *
   * @throws Exception when error from api
   */
  private function setOptionGroups() {
    try {
      $this->_claimStatusOptionGroup = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'pum_claim_status'));
      $this->_claimTypeOptionGroup = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'pum_claim_type'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option group with name pum_claim_status or pum_claim_type in '.__METHOD__
        .', is required for PUm Senior Experts Claim Processing. Contact your system administrator, 
        error from API OptionGroup getsingle: '.$ex->getMessage());
    }
  }

  /**
   * Method to set the required custom group for claim information
   *
   * @throws Exception when error from api
   */
  private function setCustomGroup() {
    try {
      $this->_claimInformationCustomGroup = civicrm_api3('CustomGroup', 'getsingle', array(
        'name' => 'Claiminformation',
        'extends' => 'Activity'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a custom group with name Claiminformation in '.__METHOD__
        .', is required for PUM Senior Experts Claim Processing. Contact your system administrator, 
        error from API CustomGroup getsingle: '.$ex->getMessage());
    }
    // now get possible custom fields in the group
    try {
      $customFields = civicrm_api3('CustomField', 'get', array(
        'custom_group_id' => $this->_claimInformationCustomGroup['id']));
      $this->_claimInformationCustomGroup['custom_fields'] = $customFields['values'];
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to return singleton object
   *
   * @return object $_singleton
   * @access public
   * @static
   */
  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Expenseclaims_Config();
    }
    return self::$_singleton;
  }
}