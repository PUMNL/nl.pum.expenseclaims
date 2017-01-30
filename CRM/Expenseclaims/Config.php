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

  /**
   * CRM_Expenseclaims_Config constructor.
   */
  function __construct() {
    $this->setValidMainActivities();
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
        $this->_validMainActivities[$caseType['id']] = $caseType['label'];
      } catch (CiviCRM_API3_Exception $ex) {
        throw new Exception(ts('Could not find an option value in option group case_type with name '.$mainActivity.' in ')
          . __METHOD__ . ts(', contact your system administrator. Error message from API OptionValue getsingle: '.$ex->getMessage()));
      }
    }
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