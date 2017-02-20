<?php
/**
 * Class BAO Claim (specific activity type)
 *
 * @author Erik Hommel (CiviCooP)
 * @date 17 Feb 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_BAO_Claim {

  private $_caseErrorStatusId = NULL;
  private $_countryCoordinatorRelationshipTypeId = NULL;
  private $_countryCoordinatorLinkLabel = NULL;
  private $_hbfLinkLabel = NULL;
  private $_sectorCoordinatorLinkLabel = NULL;
  private $_recruitmentLinkLabel = NULL;
  private $_programmeManagerLinkLabel = NULL;
  private $_aspectAdvisorsLinkLabel = NULL;
  private $_countryCoordinatorLinkValue = NULL;
  private $_hbfLinkValue = NULL;
  private $_sectorCoordinatorLinkValue = NULL;
  private $_recruitmentLinkValue = NULL;
  private $_programmeManagerLinkValue = NULL;
  private $_aspectAdvisorsLinkValue = NULL;
  private $_sectorCoordinatorRelationshipTypeId = NULL;
  private $_grantCoordinatorRelationshipTypeId = NULL;
  private $_recruitmentTeamRelationshipTypeId = NULL;
  private $_programmeManagerGroupId = NULL;


  /**
   * CRM_Expenseclaims_BAO_Claim constructor.
   */
  public function __construct()   {
    try {
      $this->_countryCoordinatorRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Country Coordinator is',
        'name_b_a' => 'Country Coordinator for',
        'return' => 'id'
      ));
      $this->_grantCoordinatorRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Grant Coordinator',
        'name_b_a' => 'Grant Coordinator',
        'return' => 'id'
      ));
      $this->_recruitmentTeamRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Recruitment Team Member',
        'name_b_a' => 'Recruitment Team Member',
        'return' => 'id'
      ));
      $this->_sectorCoordinatorRelationshipTypeId = civicrm_api3('RelationshipType', 'getvalue', array(
        'name_a_b' => 'Sector Coordinator',
        'name_b_a' => 'Sector Coordinator',
        'return' => 'id'
      ));
      $this->_programmeManagerGroupId = civicrm_api3('Group', 'getvalue', array(
        'name' => 'Programme_Managers_58',
        'return' => 'id'
      ));
      $this->_caseErrorStatusId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'case_status',
        'name' => 'Error',
        'return' => 'value'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
    $this->setLinkValuesAndLabels();
  }

  /**
   * Method to set the link values and labels
   */
  private function setLinkValuesAndLabels() {
    $config = CRM_Expenseclaims_Config::singleton();
    $this->_aspectAdvisorsLinkValue = '7164';
    $this->_countryCoordinatorLinkValue = '7160';
    $this->_hbfLinkValue = '7162';
    $this->_programmeManagerLinkValue = '7165';
    $this->_recruitmentLinkValue = '7163';
    $this->_sectorCoordinatorLinkValue = '7161';
    try {
      $this->_aspectAdvisorsLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_aspectAdvisorsLinkValue,
        'return' => 'label'
      ));
      $this->_countryCoordinatorLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_countryCoordinatorLinkValue,
        'return' => 'label'
      ));
      $this->_hbfLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_hbfLinkValue,
        'return' => 'label'
      ));
      $this->_programmeManagerLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_programmeManagerLinkValue,
        'return' => 'label'
      ));
      $this->_recruitmentLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_recruitmentLinkValue,
        'return' => 'label'
      ));
      $this->_sectorCoordinatorLinkLabel = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimTypeOptionGroup('id'),
        'value' => $this->_sectorCoordinatorLinkValue,
        'return' => 'label'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to get all claims where the contact should approve
   *
   * @param int $contactId
   * @return array $result
   */
  public function getMyClaims($contactId) {
    $result = array();
    return $result;
  }

  /**
   * Method to get claim with id
   * @param $claimId
   * @return bool|Object
   */
  public function getWithId($claimId) {
    $config = CRM_Expenseclaims_Config::singleton();
    $sql = "SELECT act.activity_date_time AS claim_submitted_date, cac.contact_id AS claim_submitted_by, 
      cust.{$config->getClaimLinkCustomField('column_name')} AS claim_link, 
      cust.{$config->getClaimTotalAmountCustomField('column_name')} AS claim_total_amount,
      cust.{$config->getClaimDescriptionCustomField('column_name')} AS claim_description 
      FROM civicrm_activity act 
      LEFT JOIN civicrm_activity_contact cac ON act.id = cac.activity_id AND cac.record_type_id = %1
      LEFT JOIN {$config->getClaimInformationCustomGroup('table_name')} cust ON act.id = cust.entity_id
      WHERE act.id = %2";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
      1 => array(3, 'Integer'),
      2 => array($claimId, 'Integer')));
    if ($dao->fetch()) {
      return $dao;
    } else {
      return FALSE;
    }
  }
  public function getExportableClaims() {

  }
  public function nextStepForClaim($claimId) {

  }

  /**
   * Method to get the possible claim links for a contact
   * - all main activities where the contact has a role
   * - based on being a project officer, cc, sc, project manager, cfo, ceo, cpo
   *
   * @param int $contactId
   * @return array|bool
   */
  public function getMyLinks($contactId) {
    if (empty($contactId)) {
      return FALSE;
    }
    $result = array();
    // get all case ids and relationship type that are not deleted where the contact has (had) a role
    $caseSql = "SELECT cc.id AS case_id, cc.subject AS case_subject, rel.relationship_type_id 
      FROM civicrm_relationship rel LEFT JOIN civicrm_case cc ON rel.case_id = cc.id
      WHERE rel.contact_id_b = %1 AND cc.is_deleted != %2 AND cc.status_id != %3";
    $cases = CRM_Core_DAO::executeQuery($caseSql, array(
      1 => array($contactId, 'Integer'),
      2 => array(1, 'Integer'),
      3 => array($this->_caseErrorStatusId, 'Integer')));
    while ($cases->fetch()) {
      $result['case_id-'.$cases->case_id] = 'Main Activity '.$cases->case_subject;
      // add additional options based on case relationship
      switch ($cases->relationship_type_id) {
        case $this->_countryCoordinatorRelationshipTypeId:
          if (!array_key_exists($this->_countryCoordinatorLinkValue, $result)) {
            $result[$this->_countryCoordinatorLinkValue] = $this->_countryCoordinatorLinkLabel;
          }
          break;
        case $this->_grantCoordinatorRelationshipTypeId:
          if (!array_key_exists($this->_hbfLinkValue, $result)) {
            $result[$this->_hbfLinkValue] = $this->_hbfLinkLabel;
          }
          break;
        case $this->_recruitmentTeamRelationshipTypeId:
          if (!array_key_exists($this->_recruitmentLinkValue, $result)) {
            $result[$this->_recruitmentLinkValue] = $this->_recruitmentLinkLabel;
          }
          break;
        case $this->_sectorCoordinatorRelationshipTypeId:
          if (!array_key_exists($this->_sectorCoordinatorLinkValue, $result)) {
            $result[$this->_sectorCoordinatorLinkValue] = $this->_sectorCoordinatorLinkLabel;
          }
        }
    }
    // add generic options
    if (!array_key_exists($this->_aspectAdvisorsLinkValue, $result)) {
      $result[$this->_aspectAdvisorsLinkValue] = $this->_aspectAdvisorsLinkLabel;
    }
    // add roles based on group membership programmeManagers
    $groupSql = 'SELECT COUNT(*) FROM civicrm_group_contact WHERE contact_id = %1 AND group_id = %2';
    $groupCount = CRM_Core_DAO::singleValueQuery($groupSql, array(
      1 => array($contactId, 'Integer'),
      2 => array($this->_programmeManagerGroupId, 'Integer')
    ));
    if ($groupCount > 0) {
      if (!array_key_exists($this->_programmeManagerLinkValue, $result)) {
        $result[$this->_programmeManagerLinkValue] = $this->_programmeManagerLinkLabel;
      }
    }
    return $result;
  }
}