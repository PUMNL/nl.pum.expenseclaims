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

  /**
   * Method to approve a claim, resulting in either next step or final approval
   *
   * @param $claimId
   * @param $contactId
   */
  public function approve($claimId, $contactId) {
    if (!empty($claimId) || empty($contactId)) {
      // get my role and then my level
      $myRole = CRM_Expenseclaims_Utils::getMyRole($claimId, $contactId);
      if ($myRole) {
        $myLevel = civicrm_api3('ClaimLevel', 'getsingle', array('level' => $myRole));
        // if my limit is 999999999.99 then final approval
        if ($myLevel['max_amount'] == 999999999.99) {
          $this->finalApprove($claimId, $contactId);
        } else {
          // if the claim total amount is less than my max amount, final approve else next step
          $totalAmount = $this->getTotalAmount($claimId);
          if ($totalAmount <= $myLevel['max_amount']) {
            $this->finalApprove($claimId, $contactId);
          } else {
            $this->nextStep($claimId, $contactId, $myLevel['authorizing_level']);
          }
        }
      }
    }
  }

  /**
   * Method to determine what the next step should be and processing that in the database
   *
   * @param $claimId
   * @param $contactId
   * @param $authorizingLevel
   * @throws Exception when one of the params is empty
   */
  private function nextStep($claimId, $contactId, $authorizingLevel) {
    if (empty($claimId) || empty($contactId) || empty($authorizingLevel)) {
      throw new Exception('ClaimId, ContactId or AuthorizingLevel empty when trying to determine next claim approval step in '.__METHOD__
        .', contact your system administrator');
    }
    $config = CRM_Expenseclaims_Config::singleton();
    // first complete current log record
    try {
      $claimLog = civicrm_api3('ClaimLog', 'getsingle', array(
        'claim_activity_id' => $claimId,
        'approval_contact_id' => $contactId));
      civicrm_api3('ClaimLog', 'create', array(
        'id' => $claimLog['id'],
        'new_status_id' => $config>getInitiallyApprovedClaimStatusValue(),
        'is_payable' => 0,
        'is_approved' => 1,
        'processed_date' => date('Y-m-d')));
      // now set next log record for the authorizing level contact
      $nextContactId = CRM_Expenseclaims_BAO_ClaimLevel::getNextLevelContactId($claimId, $authorizingLevel);
      if ($nextContactId) {
        civicrm_api3('ClaimLog', 'create', array(
          'claim_activity_id' => $claimId,
          'approval_contact_id' => $nextContactId,
          'old_status_id' => $config->getApprovedClaimStatusValue(),
          'is_approved' => 0,
          'is_payable' => 0,
          'is_rejected' => 0
        ));
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    // finally update claim status
    $sql = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET '.$config->getClaimStatusCustomField('column_value')
      .' = %1 WHERE entity_id = %2';
    CRM_Core_DAO::executeQuery($sql, array(
      1 => array($config->getInitiallyApprovedClaimStatusValue(), 'String'),
      2 => array($claimId, 'Integer')));
  }

  /**
   * Method to process final approval
   *
   * @param $claimId
   * @param $contactId
   * @throws Exception when claim id or contact id empty
   */
  private function finalApprove($claimId, $contactId) {
    if (empty($claimId) || empty($contactId)) {
      throw new Exception('ClaimId or ContactId empty when trying to final approve claim in '.__METHOD__.', contact your system administrator');
    }
    $config = CRM_Expenseclaims_Config::singleton();
    $sql = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET '.$config->getClaimStatusCustomField('column_value')
      .' = %1 WHERE entity_id = %2';
    CRM_Core_DAO::executeQuery($sql, array(
      1 => array($config->getApprovedClaimStatusValue(), 'String'),
      2 => array($claimId, 'Integer')));
    // now update claim log line for this approval
    try {
      $claimLog = civicrm_api3('ClaimLog', 'getsingle', array(
        'claim_activity_id' => $claimId,
        'approval_contact_id' => $contactId));
      civicrm_api3('ClaimLog', 'create', array(
        'id' => $claimLog['id'],
        'new_status_id' => $config>getApprovedClaimStatusValue(),
        'is_payable' => 1,
        'processed_date' => date('Y-m-d')));
    } catch (CiviCRM_API3_Exception $ex) {}
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

  /**
   * Method to update claim
   *
   * @param $params
   * @throws Exception when no claim_id in params
   */
  public function update($params) {
    if (!isset($params['claim_id']) || empty($params['claim_line'])) {
      throw new Exception('Mandatory parameter claim_id missing in array $params in '.__METHOD__.', contact your system administrator');
    }
    $config = CRM_Expenseclaims_Config::singleton();
    $clauses = array();
    $clausesParams = array();
    $index = 0;
    // if claim_description has to be updated
    if (isset($params['claim_description'])) {
      $index++;
      $clauses[] = $config->getClaimDescriptionCustomField('column_name').' = %'.$index;
      $clauseParams[$index] = array($params['claim_description'], 'String');
    }
    // if claim_link has to be updated
    if (isset($params['claim_link'])) {
      $index++;
      $clauses[] = $config->getClaimLinkCustomField('column_name').' = %'.$index;
      $clauseParams[$index] = array($params['claim_link'], 'String');
    }
    $index++;
    $sql = "UPDATE ".$config->getClaimInformationCustomGroup('id')." SET ".implode(',', $clauses)." WHERE entity_id = %".$index;
    $clausesParams[$index] = array($params['claim_id'], 'Integer');
    CRM_Core_DAO::executeQuery($sql, $clausesParams);
  }

  /**
   * Method to update the total amount of the claim with the euro amounts of all claim lines
   *
   * @param $claimId
   */
  public function updateTotalAmount($claimId) {
    if (!empty($claimId)) {
      $totalAmount = 0;
      $claimLines = civicrm_api3('ClaimLine', 'get', array('activity_id' => $claimId));
      foreach ($claimLines['values'] as $claimLineId => $claimLine) {
        $totalAmount = $totalAmount + $claimLine['euro_amount'];
      }
      $config = CRM_Expenseclaims_Config::singleton();
      $totalAmount = round($totalAmount, 2);
      $sql = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET '.
        $config->getClaimTotalAmountCustomField('column_name').' = %1 WHERE entity_id = %2';
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($totalAmount, 'Money'),
        2 => array($claimId, 'Integer')
        ));
    }
    return;
  }

  /**
   * Method to return a case id from the claim link field
   *
   * @param $claimId
   * @return bool|string
   */
  public function getProjectClaimCaseId($claimId) {
    if (!empty($claimId)) {
      $config = CRM_Expenseclaims_Config::singleton();
      $sql = 'SELECT ' . $config->getClaimLinkCustomField('column_name') . ' FROM ' . $config->getClaimInformationCustomGroup('table_name')
        . ' WHERE entity_id = %1 AND ' . $config->getClaimTypeCustomField('column_name') . ' = %2';
      $claimLink = CRM_Core_DAO::singleValueQuery($sql, array(
        1 => array($claimId, 'Integer'),
        2 => array('project' => 'String')));
      if ($claimLink) {
        return $claimLink;
      }
    }
    return FALSE;
  }

  /**
   * Method to get total amount of a claim
   *
   * @param $claimId
   * @return bool|string
   */
  public function getTotalAmount($claimId) {
    if (empty($claimId)) {
      return FALSE;
    }
    $config = CRM_Expenseclaims_Config::singleton();
    $sql = 'SELECT '.$config->getClaimTotalAmountCustomField('column_name').' FROM '.$config->getClaimInformationCustomGroup('table_name')
      .' WHERE entity_id = %1';
    $totalAmount = CRM_Core_DAO::singleValueQuery($sql, array(1 => array($claimId, 'Integer')));
    if ($totalAmount) {
      return $totalAmount;
    }
    return FALSE;
  }
}