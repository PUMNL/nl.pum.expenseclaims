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

  private $_validMainActivities = array();
  private $_claimActivityTypeId = NULL;
  private $_cpoContactId = NULL;
  private $_cfoLevelId = NULL;
  private $_cfoContactId = NULL;
  private $_claimTypeOptionGroup = array();
  private $_claimStatusOptionGroup = array();
  private $_claimLevelOptionGroup = array();
  private $_claimInformationCustomGroup = array();
  private $_claimLineTypeOptionGroup = array();
  private $_batchStatusOptionGroup = array();
  private $_seniorProjectOfficerRelationshipTypeId = NULL;
  private $_projectOfficerRelationshipTypeId = NULL;
  private $_approvedClaimStatusValue = NULL;
  private $_initiallyApprovedClaimStatusValue = NULL;
  private $_waitingForApprovalClaimStatusValue = NULL;
  private $_rejectedClaimStatusValue = NULL;
  private $_targetRecordTypeId = NULL;
  private $_scheduledActivityStatusId = NULL;
  private $_sectorCoordinatorRelationshipTypeId = NULL;
  private $_grantCoordinatorRelationshipTypeId = NULL;
  private $_recruitmentTeamRelationshipTypeId = NULL;
  private $_countryCoordinatorRelationshipTypeId = NULL;
  private $_programmeManagerGroupId = NULL;
  private $_openBatchStatusId = NULL;
  private $_euroCurrencyId = NULL;

  /**
   * CRM_Expenseclaims_Config constructor.
   */
  function __construct() {
    $this->setSeniorProjectOfficerRelationshipTypeId();
    $this->setProjectOfficerRelationshipTypeId();
    $this->setSectorCoordinatorRelationshipTypeId();
    $this->setGrantCoordinatorRelationshipTypeId();
    $this->setCountryCoordinatorRelationshipTypeId();
    $this->setRecruitmentTeamRelationshipTypeId();
    $this->setProgrammeManagerGroupId();
    $this->setValidMainActivities();
    $this->setClaimActivityTypeId();
    $this->setOptionGroups();
    $this->setCustomGroup();
    $this->setEuroCurrencyId();
    $this->setCpoCfoContactId();
    try {
      $this->_approvedClaimStatusValue = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $this->_claimStatusOptionGroup['id'],
        'name' => 'approved',
        'return' => 'value'
      ));
      $this->_initiallyApprovedClaimStatusValue = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $this->_claimStatusOptionGroup[ 'id'],
        'name' => 'initially_approved',
        'return' => 'value'
      ));
      $this->_waitingForApprovalClaimStatusValue = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $this->_claimStatusOptionGroup[ 'id'],
        'name' => 'waiting_for_approval',
        'return' => 'value'
      ));
      $this->_rejectedClaimStatusValue = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $this->_claimStatusOptionGroup[ 'id'],
        'name' => 'rejected',
        'return' => 'value'
      ));
      $this->_openBatchStatusId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $this->_batchStatusOptionGroup[ 'id'],
        'name' => 'open',
        'return' => 'value'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a claim status in '.__METHOD__
        .', contact your system administrator. Error from API OptionValue getvalue: '.$ex->getMessage());
    }
    try {
      $this->_targetRecordTypeId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_contacts',
        'name' => 'Activity Targets',
        'return' => 'value'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a target record id type in '.__METHOD__
        .', contact your system administrator. Error from API OptionValue getvalue: '.$ex->getMessage());
    }
    try {
      $this->_scheduledActivityStatusId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_status',
        'name' => 'Scheduled',
        'return' => 'value'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an activity status Scheduled record id type in '.__METHOD__
        .', contact your system administrator. Error from API OptionValue getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Getter for euro currency id
   *
   * @return null
   */
  public function getEuroCurrencyId() {
    return $this->_euroCurrencyId;
  }

  /**
   * Getter for open batch status id
   *
   * @return array|null
   */
  public function getOpenBatchStatusId() {
    return $this->_openBatchStatusId;
  }

  /**
   * Getter for country coordinator relationship type id
   * @return array|null
   */
  public function getCountryCoordinatorRelationshipTypeId() {
    return $this->_countryCoordinatorRelationshipTypeId;
  }

  /**
   * Getter for grant coordinator relationship type id
   * @return array|null
   */
  public function getGrantCoordinatorRelationshipTypeId() {
    return $this->_grantCoordinatorRelationshipTypeId;
  }

  /**
   * Getter for recruitment team relationship type id
   * @return array|null
   */
  public function getRecruitmentTeamRelationshipTypeId() {
    return $this->_recruitmentTeamRelationshipTypeId;
  }

  /**
   * Getter for sector coordinator relationship type id
   * @return array|null
   */
  public function getSectorCoordinatorRelationshipTypeId() {
    return $this->_sectorCoordinatorRelationshipTypeId;
  }

  /**
   * Getter for programme manager group id
   * @return array|null
   */
  public function getProgrammeManagerGroupId() {
    return $this->_programmeManagerGroupId;
  }

  /**
   * Getter for scheduled activity status
   * @return array|null
   */
  public function getScheduledActivityStatusId() {
    return $this->_scheduledActivityStatusId;
  }

  /**
   * Getter for target record type id (activity contact)
   * @return array|null
   */
  public function getTargetRecordTypeId() {
    return $this->_targetRecordTypeId;
  }

  /**
   * Getter for waiting for approval claims status value
   * @return null
   */
  public function getWaitingForApprovalClaimStatusValue() {
    return $this->_waitingForApprovalClaimStatusValue;
  }

  /**
   * Getter for initially approved claims status value
   * @return null
   */
  public function getInitiallyApprovedClaimStatusValue() {
    return $this->_initiallyApprovedClaimStatusValue;
  }

  /**
   * Getter for approved claims status value
   * @return null
   */
  public function getApprovedClaimStatusValue() {
    return $this->_approvedClaimStatusValue;
  }

  /**
   * Getter for rejected claims status value
   * @return null
   */
  public function getRejectedClaimStatusValue() {
    return $this->_rejectedClaimStatusValue;
  }

  /**
   * Getter for senior project officer relationship type id
   * @return null
   */
  public function getSeniorProjectOfficerRelationshipTypeId() {
    return $this->_seniorProjectOfficerRelationshipTypeId;
  }

  /**
   * Getter for project officer relationship type id
   * @return null
   */
  public function getProjectOfficerRelationshipTypeId() {
    return $this->_projectOfficerRelationshipTypeId;
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
   * Getter for claim batch status option group
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getBatchStatusOptionGroup($key = 'id') {
    return $this->_batchStatusOptionGroup[$key];
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
   * Getter for claim level option group
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimLevelOptionGroup($key = 'id') {
    return $this->_claimLevelOptionGroup[$key];
  }

  /**
   * Getter for claim line type option group
   *
   * @param string $key default = id
   * @return mixed
   */
  public function getClaimLineTypeOptionGroup($key = 'id') {
    return $this->_claimLineTypeOptionGroup[$key];
  }

  /**
   * Getter for CFO contact id
   *
   * @return string
   * @access public
   */
  public function getPumCfo() {
    return $this->_cfoContactId;
  }

  public function getCfoLevelId() {
    return $this->_cfoLevelId;
  }

  /**
   * Getter for CPO contact id
   *
   * @return string
   * @access public
   */
  public function getPumCpo() {
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
   * Method to set the CPO and CFO
   *
   * @throws Exception when API getvalue error (not found, more than one)
   */
  private function setCpoCfoContactId() {

    try {
      // first get levels for cfo and cpo
      $this->_cfoLevelId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $this->getClaimLevelOptionGroup('id'),
        'name' => 'cfo',
        'return' => 'value'
      ));
      $cpoLevel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $this->getClaimLevelOptionGroup('id'),
        'name' => 'cpo',
        'return' => 'value'
      ));
      
      $sql = "SELECT contact_id FROM pum_claim_level_contact lc
              JOIN   pum_claim_level level ON level.id = lc.claim_level_id
              WHERE level.level = %1 LIMIT 1";
      $this->_cfoContactId = CRM_Core_DAO::singleValueQuery($sql,array(1 => array($this->_cfoLevelId, 'Integer')));
      $this->_cpoContactId = CRM_Core_DAO::singleValueQuery($sql,array(1 => array($cpoLevel, 'Integer')));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a contact with authorization level CPO and/or CFO in '.__METHOD__
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
      $this->_claimLevelOptionGroup = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'pum_claim_level'));
      $this->_claimLineTypeOptionGroup = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'pum_claim_line_type'));
      $this->_batchStatusOptionGroup = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'pum_claim_batch_status'));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find an option group with name pum_claim_status, pum_claim_level, pum_claim_line_type, 
      pum_claim_batch_status or pum_claim_type in '.__METHOD__.', is required for PUM Senior Experts Claim Processing. 
      Contact your system administrator, error from API OptionGroup getsingle: '.$ex->getMessage());
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
   * Method to set the relationship type id of the senior project officer
   *
   * @throws Exception
   */
  private function setSeniorProjectOfficerRelationshipTypeId() {
    try {
      $this->_seniorProjectOfficerRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'senior_project_officer',
        'name_b_a' => 'senior_project_officer_is',
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a relationship Senior Project Officer in '.__METHOD__
        .', contact your system administrator. Error from API RelationshipType getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Method to set the relationship type id of the project officer
   *
   * @throws Exception
   */
  private function setProjectOfficerRelationshipTypeId() {
    try {
      $this->_projectOfficerRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Project Officer for',
        'name_b_a' => 'Project Officer is',
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a relationship Project Officer in '.__METHOD__
        .', contact your system administrator. Error from API RelationshipType getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Method to set the relationship type id of the country coordinator
   *
   * @throws Exception
   */
  private function setCountryCoordinatorRelationshipTypeId() {
    try {
      $this->_countryCoordinatorRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Country Coordinator is',
        'name_b_a' => 'Country Coordinator for',
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a relationship Country Coordinator in '.__METHOD__
        .', contact your system administrator. Error from API RelationshipType getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Method to set the relationship type id of the grant coordinator
   *
   * @throws Exception
   */
  private function setGrantCoordinatorRelationshipTypeId() {
    try {
      $this->_grantCoordinatorRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Grant Coordinator',
        'name_b_a' => 'Grant Coordinator',
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a relationship Grant Coordinator in '.__METHOD__
        .', contact your system administrator. Error from API RelationshipType getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Method to set the relationship type id of the sector coordinator
   *
   * @throws Exception
   */
  private function setSectorCoordinatorRelationshipTypeId() {
    try {
      $this->_sectorCoordinatorRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Sector Coordinator',
        'name_b_a' => 'Sector Coordinator',
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a relationship Sector Coordinator in '.__METHOD__
        .', contact your system administrator. Error from API RelationshipType getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Method to set the relationship type id of the recruitment team member
   *
   * @throws Exception
   */
  private function setRecruitmentTeamRelationshipTypeId() {
    try {
      $this->_recruitmentTeamRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Recruitment Team Member',
        'name_b_a' => 'Recruitment Team Member',
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a relationship Recruitment Team Member in '.__METHOD__
        .', contact your system administrator. Error from API RelationshipType getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Method to set the group id programme managers
   *
   * @throws Exception
   */
  private function setProgrammeManagerGroupId() {
    try {
      $this->_programmeManagerGroupId = civicrm_api3('Group', 'getvalue', array(
        'name' => 'Programme_Managers_58',
        'return' => 'id'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      throw new Exception('Could not find a group Programme Managers in '.__METHOD__
        .', contact your system administrator. Error from API Group getvalue: '.$ex->getMessage());
    }
  }

  /**
   * Set the property for the EURO currency id
   * @throws Exception
   */
  private function setEuroCurrencyId() {
    $sql = "SELECT id FROM civicrm_currency WHERE name = %1";
    try {
      $this->_euroCurrencyId = CRM_Core_DAO::singleValueQuery($sql, array(1 => array('EUR', 'String')));
    } catch (Exception $e) {
      throw new Exception('Could not find a EURO currency name in the table civicrm_currency in '.__METHOD__.', contact your system administrator!');
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