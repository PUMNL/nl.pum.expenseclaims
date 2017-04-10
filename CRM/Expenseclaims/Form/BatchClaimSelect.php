<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Expenseclaims_Form_BatchClaimSelect extends CRM_Core_Form {

  private $_batchId = NULL;
  private $_batchData = NULL;
  private $_subsetIndex = NULL;
  private $_subsetParams = array();
  private $_searchDateFrom = NULL;
  private $_searchDateTo = NULL;

  /**
   * Method to build the QuickForm
   */
  public function buildQuickForm() {
    // add form elements for batch data
    $this->add('hidden', 'batch_id', ts('Batch ID'), array('id' => 'batch_id'));
    $this->add('text', 'batch_description', ts('Description'), array('readonly' => 'readonly'));
    $this->add('text', 'batch_created_date', ts('Created Date'), array('readonly' => 'readonly'));
    $this->add('text', 'batch_status', ts('Status'), array('readonly' => 'readonly'));
    // add form element for selection criteria
    $this->addDate('claim_from_date', ts('Claim Date from'), false);
    $this->addDate('claim_to_date', ts('Claim Date to'), false);
    // add buttons
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Search'), 'isDefault' => true,)));
    parent::buildQuickForm();
  }

  /**
   * Method to get the batch data and store in property
   *
   * @access private
   */
  private function getBatchData() {
    try {
      $this->_batchData = civicrm_api3('ClaimBatch', 'getsingle', array('id' => $this->_batchId));
      $config = CRM_Expenseclaims_Config::singleton();
      $this->_batchData['batch_status'] = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimStatusOptionGroup('id'),
        'value' => $this->_batchData['batch_status_id'],
        'return' => 'label'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Overridden parent function to set default values
   *
   * @return mixed
   */
  public function setDefaultValues()   {
    $defaults['batch_id'] = $this->_batchId;
    $defaults['batch_description'] = $this->_batchData['description'];
    $defaults['batch_created_date'] = date('Y M d', strtotime($this->_batchData['created_date']));
    $defaults['batch_status'] = $this->_batchData['batch_status'];
    // set selection criteria if set
    if (!empty($this->_searchDateFrom)) {
      list($defaults['claim_from_date']) = CRM_Utils_Date::setDateDefaults($this->_searchDateFrom);
    }
    if (!empty($this->_searchDateTo)) {
      list($defaults['claim_to_date']) = CRM_Utils_Date::setDateDefaults($this->_searchDateTo);
    }
    return $defaults;
  }

  /**
   * Method to get the claims currently in selected batch
   *
   * @return array
   */
  private function getCurrentClaimsForBatch() {
    $currentClaims = array();
    $dao = CRM_Core_DAO::executeQuery($this->getCurrentClaimsQuery(), $this->getCurrentClaimsParams());
    while ($dao->fetch()) {
      $claimLink = $dao->claim_link;
      // get case subject with claim_link if claim_type = project
      if ($dao->claim_type == 'project') {
        try {
          $claimLink = civicrm_api3('Case', 'getvalue', array('id' => $dao->claim_link, 'return' => 'subject'));
        } catch (CiviCRM_API3_Exception $ex) {
          $claimLink = ts('not found');
        }
      }
      $currentClaims[] = array(
        'claim_id' => $dao->claim_id,
        'claim_description' => $dao->claim_description,
        'claim_submitted_by' => $dao->claim_submitted_by,
        'claim_submitted_date' => $dao->claim_submitted_date,
        'claim_link' => $claimLink,
        'claim_total_amount' => $dao->claim_total_amount,
        'claim_status' => $dao->claim_status,
        'pcbe_id' => $dao->pcbe_id,
      );
    }
    $this->assign('currentClaims', $currentClaims);
  }

  /**
   * Method to process results from the form
   */
  public function postProcess() {
    if (isset($this->_submitValues['batch_id'])) {
      $this->_batchId = $this->_submitValues['batch_id'];
    }
    // set search criteria and redirect
    $this->setSearchCriteria($this->_submitValues);
    parent::postProcess();
  }

  /**
   * Method to set the search criteria and reload form to filter the approved claims with criteria
   *
   * @param $values
   */
  private function setSearchCriteria($values) {
    $redirectParams = array();
    if (isset($values['claim_from_date']) && !empty($values['claim_from_date'])) {
      $redirectParams[] = 'sdf='.$values['claim_from_date'];
    }
    if (isset($values['claim_to_date']) && !empty($values['claim_to_date'])) {
      $redirectParams[] = 'sdt='.$values['claim_to_date'];
    }
    if (!empty($redirectParams)) {
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pumexpenseclaims/form/batchclaimselect',
        '&action=update&bid=' . $this->_batchId.'&'.implode('&', $redirectParams), TRUE));
    } else {
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pumexpenseclaims/form/batchclaimselect',
        '&action=update&bid=' . $this->_batchId, TRUE));
    }
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    CRM_Utils_System::setTitle(ts('PUM Senior Experts Select Batch Claims'));
    $this->_subsetParams = array();
    $this->_subsetIndex = NULL;
    // retrieve batch id from the request
    $requestValues = CRM_Utils_Request::exportValues();
    //exit();
    if (isset($requestValues['bid'])) {
      $this->_batchId = $requestValues['bid'];
    }
    // check if search criteria in request and if so, set
    if (isset($requestValues['sdf'])) {
      $this->_searchDateFrom = date('Y-m-d', strtotime($requestValues['sdf']));
    }
    if (isset($requestValues['sdt'])) {
      $this->_searchDateTo = date('Y-m-d', strtotime($requestValues['sdt']));
    }
    if (!empty($this->_batchId)) {
      $this->getBatchData();
      $this->getClaimSubset();
      $this->getCurrentClaimsForBatch();
    }
  }

  /**
   * Method to create the query for the current claims in batch
   *
   * @return string
   */
  private function getCurrentClaimsQuery() {
    $config = CRM_Expenseclaims_Config::singleton();
    $query = "SELECT pcbe.id as pcbe_id, pcbe.entity_id AS claim_id, cc.display_name AS claim_submitted_by, 
      cvci.{$config->getClaimDescriptionCustomField('column_name')} AS claim_description, act.activity_date_time AS claim_submitted_date,
      cvci.{$config->getClaimLinkCustomField('column_name')} AS claim_link, cvci.{$config->getClaimTotalAmountCustomField('column_name')}
      AS claim_total_amount, csov.label AS claim_status, cvci.{$config->getClaimTypeCustomField('column_name')} AS claim_type
      FROM pum_claim_batch_entity pcbe
      JOIN civicrm_activity_contact cac ON pcbe.entity_id = cac.activity_id AND cac.record_type_id = %1
      JOIN civicrm_activity act ON pcbe.entity_id = act.id
      JOIN civicrm_contact cc ON cac.contact_id = cc.id
      LEFT JOIN {$config->getClaimInformationCustomGroup('table_name')} cvci ON pcbe.entity_id = cvci.entity_id
      LEFT JOIN civicrm_option_value csov ON cvci.{$config->getClaimStatusCustomField('column_name')} = csov.value AND csov.option_group_id = %2
      WHERE pcbe.batch_id = %3 AND pcbe.entity_table = %4";
    return $query;
  }

  /**
   * Method to create the params array for the current claims in batch query
   *
   * @return array
   */
  private function getCurrentClaimsParams() {
    $config = CRM_Expenseclaims_Config::singleton();
    $params = array(
      1 => array($config->getTargetRecordTypeId(), 'Integer'),
      2 => array($config->getClaimStatusOptionGroup('id'), 'Integer'),
      3 => array($this->_batchId, 'Integer'),
      4 => array('civicrm_activity', 'String')
    );
    return $params;
  }

  /**
   * Method to get the subset of claims based on the request params
   * (always only claims that have a status of approved and are not in the batch yet)
   */
  private function getClaimSubset() {
    $subsetClaims = array();
    $this->getSubsetParams();
    $whereClauses = $this->getSubsetWhereClauses();
    $dao = CRM_Core_DAO::executeQuery($this->getSubsetQuery($whereClauses), $this->_subsetParams);
    while ($dao->fetch()) {
      $claimLink = $dao->claim_link;
      // get case subject with claim_link if claim_type = project
      if ($dao->claim_type == 'project') {
        try {
          $claimLink = civicrm_api3('Case', 'getvalue', array('id' => $dao->claim_link, 'return' => 'subject'));
        } catch (CiviCRM_API3_Exception $ex) {
          $claimLink = ts('not found');
        }
      }
      $subsetClaims[] = array(
        'claim_id' => $dao->claim_id,
        'claim_description' => $dao->claim_description,
        'claim_submitted_by' => $dao->claim_submitted_by,
        'claim_submitted_date' => $dao->claim_submitted_date,
        'claim_link' => $claimLink,
        'claim_total_amount' => $dao->claim_total_amount,
        'claim_status' => $dao->claim_status,
      );
    }
    $this->assign('subsetClaims', $subsetClaims);
  }

  /**
   * Method to get the initial query params for the subset claims
   */
  private function getSubsetParams() {
    $config = CRM_Expenseclaims_Config::singleton();
    $this->_subsetParams = array(
      1 => array($config->getClaimActivityTypeId(), 'Integer'),
      2 => array(1, 'Integer'),
      3 => array(0, 'Integer'),
      4 => array($config->getApprovedClaimStatusValue(), 'String'),
      5 => array($config->getTargetRecordTypeId(), 'Integer'),
      6 => array($config->getClaimStatusOptionGroup('id'), 'Integer'),
      7 => array($this->_batchId, 'Integer'),
      8 => array('civicrm_activity', 'String')
    );
    $this->_subsetIndex = 8;
  }
  /**
   * Method to set the where clauses for the claims subset
   *
   * @return array
   */
  private function getSubsetWhereClauses() {
    $config = CRM_Expenseclaims_Config::singleton();
    $whereClauses = array(
      'act.activity_type_id = %1',
      'act.is_current_revision = %2',
      'act.is_deleted = %3',
      'act.is_test = %3',
      'cvci.' . $config->getClaimStatusCustomField('column_name') . ' = %4'
    );
    if (!empty($this->_searchDateFrom)) {
      $this->_subsetIndex++;
      $whereClauses[] = 'act.activity_date_time >= %' . $this->_subsetIndex;
      $this->_subsetParams[$this->_subsetIndex] = array($this->_searchDateFrom, 'String');
    }
    if (!empty($this->_searchDateTo)) {
      $this->_subsetIndex++;
      $whereClauses[] = 'act.activity_date_time <= %' . $this->_subsetIndex;
      $this->_subsetParams[$this->_subsetIndex] = array($this->_searchDateTo, 'String');
    }
    return $whereClauses;
  }

  /**
   * Method to get the query for the claims subset
   *
   * @param $whereClauses
   * @return string
   */
  private function getSubsetQuery($whereClauses) {
    $config = CRM_Expenseclaims_Config::singleton();
    $query = "SELECT cvci.entity_id AS claim_id, cc.display_name AS claim_submitted_by, 
      cvci.{$config->getClaimDescriptionCustomField('column_name')} AS claim_description, act.activity_date_time AS claim_submitted_date,
      cvci.{$config->getClaimLinkCustomField('column_name')} AS claim_link, cvci.{$config->getClaimTotalAmountCustomField('column_name')}
      AS claim_total_amount, csov.label AS claim_status, cvci.{$config->getClaimTypeCustomField('column_name')} AS claim_type
      FROM civicrm_activity act
      JOIN civicrm_activity_contact cac ON act.id = cac.activity_id AND cac.record_type_id = %5
      JOIN civicrm_contact cc ON cac.contact_id = cc.id
      LEFT JOIN {$config->getClaimInformationCustomGroup('table_name')} cvci ON act.id = cvci.entity_id
      LEFT JOIN civicrm_option_value csov ON cvci.pum_claim_status = csov.value AND csov.option_group_id = %6
      WHERE act.id NOT IN(SELECT entity_id FROM pum_claim_batch_entity WHERE batch_id = %7 AND entity_table = %8) AND "
      .implode(' AND ', $whereClauses);
    return $query;
  }

  /**
   * Overridden parent method to set validation rules
   */
  public function addRules() {
    $this->addFormRule(array('CRM_Expenseclaims_Form_BatchClaimSelect', 'validateDates'));
  }

  /**
   * Method to validate from date is not bigger than to date
   *
   * @param $fields
   * @return bool|array
   */
  public static function validateDates($fields) {
    if (isset($fields['claim_from_date']) && !empty($fields['claim_from_date'])) {
      if (isset($fields['claim_to_date']) && !empty($fields['claim_to_date'])) {
        $fromDate = new DateTime($fields['claim_from_date']);
        $toDate = new DateTime($fields['claim_to_date']);
        if ($fromDate >= $toDate) {
          $errors['claim_from_date'] = ts('From date is bigger than or equal to to date, there will be no results');
          return $errors;
        }
      }
    }
    return TRUE;
  }
}