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
  private $_newClaim = array();


  /**
   * CRM_Expenseclaims_BAO_Claim constructor.
   */
  public function __construct()   {
    try {
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
      1 => array($config->getTargetRecordTypeId(), 'Integer'),
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
    // first complete current log record if there is one or create new one
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
        'new_status_id' => $config->getApprovedClaimStatusValue(),
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
    $config = CRM_Expenseclaims_Config::singleton();
    $countryCoordinatorRelationshipTypeId = $config->getCountryCoordinatorRelationshipTypeId();
    $grantCoordinatorRelationshipTypeId = $config->getGrantCoordinatorRelationshipTypeId();
    $recruitmentTeamRelationshipTypeId = $config->getRecruitmentTeamRelationshipTypeId();
    $sectorCoordinatorRelationshipTypeId = $config->getSectorCoordinatorRelationshipTypeId();

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
        case $countryCoordinatorRelationshipTypeId:
          if (!array_key_exists($this->_countryCoordinatorLinkValue, $result)) {
            $result[$this->_countryCoordinatorLinkValue] = $this->_countryCoordinatorLinkLabel;
          }
          break;
        case $grantCoordinatorRelationshipTypeId:
          if (!array_key_exists($this->_hbfLinkValue, $result)) {
            $result[$this->_hbfLinkValue] = $this->_hbfLinkLabel;
          }
          break;
        case $recruitmentTeamRelationshipTypeId:
          if (!array_key_exists($this->_recruitmentLinkValue, $result)) {
            $result[$this->_recruitmentLinkValue] = $this->_recruitmentLinkLabel;
          }
          break;
        case $sectorCoordinatorRelationshipTypeId:
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
      2 => array($config->getProgrammeManagerGroupId(), 'Integer')
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

  /**
   * Method to create a new claim :
   * - add activity with custom data
   * - add claim log entry for the correct approval contact id
   *
   * @param array $params
   * @return bool
   */
  public function createNew($params) {
    $config = CRM_Expenseclaims_Config::singleton();
    $params['activity_type_id'] = $config->getClaimActivityTypeId();
    // create activity
    $activityParams = array(
      'activity_type_id' => $config->getClaimActivityTypeId(),
      'activity_date_time' => date('Y-m-d', strtotime($params['expense_date'])),
      'status_id' => $config->getScheduledActivityStatusId(),
      'target_contact_id' => $params['claim_contact_id'],
      'subject' => 'Claim entered on website'
    );
    try {
      $activity = civicrm_api3('Activity', 'create', $activityParams);
    } catch (CiviCRM_API3_Exception $ex) {
      return FALSE;
    }
    // then add custom data
    $this->_newClaim = $activity['values'][$activity['id']];
    $this->createCustomData($params);
    // finally determine who needs to approve claim and create claim log entry
    $this->createFirstStep();
    return $this->_newClaim;
  }

  /**
   * Method to find approval contact and set claim log for new claim
   */
  private function createFirstStep() {
    $config = CRM_Expenseclaims_Config::singleton();
    // find approval contact based on claim link
    $approvalContactId = $this->findFirstApprovalContact();
    if ($approvalContactId) {
      civicrm_api3('ClaimLog', 'create', array(
        'claim_activity_id' => $this->_newClaim['id'],
        'approval_contact_id' => $approvalContactId,
        'old_status_id' => $config->getWaitingForApprovalClaimStatusValue(),
        'is_approved' => 0,
        'is_payable' => 0,
        'is_rejected' => 0
      ));
    } else {
      $errorTxt = array();
      foreach ($this->_newClaim as $key => $value) {
        $errorTxt[] = 'parameter '.$key.' and value '.$value;
      }
      throw new Exception('Could not create a claim in '.__METHOD__.' with values '.implode('; ', $errorTxt));
    }
  }

  /**
   * Method to determine the first approval contact based on claim type
   *
   * @return bool|int
   */
  private function findFirstApprovalContact() {
    switch ($this->_newClaim['claim_type']) {
      // if claim type is 7162 or 7165 approval by CFO
      case "7162":
        $config = CRM_Threepeas_CaseRelationConfig::singleton();
        return $config->getPumCfo();
        break;
      case "7165":
        $config = CRM_Threepeas_CaseRelationConfig::singleton();
        return $config->getPumCfo();
        break;
      // if claim type is 7163 or 7164 approval bij CPO
      case "7163":
        $config = CRM_Expenseclaims_Config::singleton();
        return $config->getPumCpo();
        break;
      case "7164":
        $config = CRM_Expenseclaims_Config::singleton();
        return $config->getPumCpo();
        break;
      // if project, check my role (if SC then approval by CPO) else approval based on levels
      case "project":
        if (CRM_Expenseclaims_Utils::isClaimEnteredBySC($this->_newClaim['id'], $this->_newClaim['claim_link']) == TRUE) {
          $config = CRM_Expenseclaims_Config::singleton();
          return $config->getPumCpo();
        } else {
          return $this->findFirstApprovalProjectContact();
        }
        break;
      default:
        return FALSE;
        break;
    }
  }

  /**
   * Method to find out who needs to approve the new claim for a main activity
   * - always fall back to cfo if no other contact found
   * - always use project officer as first approval step
   *
   * @return mixed
   */
  private function findFirstApprovalProjectContact() {
    // in case of doubt go to CFO
    $config = CRM_Threepeas_CaseRelationConfig::singleton();
    $contactId = $config->getPumCfo();
    $config = CRM_Expenseclaims_Config::singleton();
    // get project officer for case
    $relation = civicrm_api3('Relationship', 'get', array(
      'relationship_type_id' => $config->getProjectOfficerRelationshipTypeId(),
      'case_id' => $this->_newClaim['claim_link'],
      'options' => array('limit' => 1)
    ));
    if (!empty($relation['values'][$relation['id']]['contact_id_b'])) {
      $contactId = $relation['values'][$relation['id']]['contact_id_b'];
    }
    return $contactId;
  }

  /**
   * Function to insert custom record for claim activity and save in $this->_newClaim
   *
   * @param $params
   */
  private function createCustomData($params) {
    $config = CRM_Expenseclaims_Config::singleton();
    $sqlClauses = array('entity_id = %1');
    $sqlParams[1] = array($this->_newClaim['id'], 'Integer');
    $sqlClauses[] = $config->getClaimStatusCustomField('column_name').' = %2';
    $sqlParams[2] = array($config->getWaitingForApprovalClaimStatusValue(), 'String');
    $index = 2;
    if (isset($params['claim_type'])) {
      $index++;
      $sqlClauses[] = $config->getClaimTypeCustomField('column_name').' = %'.$index;
      $sqlParams[$index] = array($params['claim_type'], 'String');
      $this->_newClaim['claim_type'] = $params['claim_type'];
    }
    if (isset($params['claim_link'])) {
      $index++;
      $sqlClauses[] = $config->getClaimLinkCustomField('column_name').' = %'.$index;
      $sqlParams[$index] = array($params['claim_link'], 'String');
      $this->_newClaim['claim_link'] = $params['claim_link'];
    }
    if (isset($params['claim_total_amount'])) {
      $index++;
      $sqlClauses[] = $config->getClaimTotalAmountCustomField('column_name').' = %'.$index;
      $sqlParams[$index] = array($params['claim_total_amount'], 'Money');
      $this->_newClaim['claim_total_amount'] = $params['claim_total_amount'];
    }
    if (isset($params['claim_description'])) {
      $index++;
      $sqlClauses[] = $config->getClaimDescriptionCustomField('column_name').' = %'.$index;
      $sqlParams[$index] = array($params['claim_description'], 'String');
      $this->_newClaim['claim_description'] = $params['claim_description'];
    }
    $sql = 'INSERT INTO '.$config->getClaimInformationCustomGroup('table_name').' SET '.implode(', ', $sqlClauses);
    CRM_Core_DAO::executeQuery($sql, $sqlParams);
  }

  /**
   * Method to process buildForm hook:
   * - hide activity_date_time with jQuery in template in update mode
   *
   * @param $formName
   * @param $form
   */
  public static function buildForm($formName, &$form) {
    if ($formName = 'CRM_Activity_Form_Activity') {
      if (isset($form->_activityTypeName) && $form->_activityTypeName == 'Claim') {
        CRM_Core_Region::instance('page-body')->add(array('template' => 'CRM/Expenseclaims/ClaimActivityDateTime.tpl'));
      }
    }
  }
}