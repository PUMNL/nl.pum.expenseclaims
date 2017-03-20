<?php
/**
 * Page BatchClaimSelect to list show claims in batch and allow adding claims to batch
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 20 March 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_Page_BatchClaimSelect extends CRM_Core_Page {

  private $_batchData = array();
  private $_dateFrom = NULL;
  private $_dateTo = NULL;
  private $_claimTypes = array();

  public function run() {
    $this->setPropertiesFromRequest();
    $this->getCurrentClaimsForBatch();
    $this->getClaimSubset();


    parent::run();
  }

  /**
   * Method to get the subset of claims based on the request params
   * (always only claims that have a status of approved and are not in the batch yet)
   */
  private function getClaimSubset() {

  }

  /**
   * Method to set the properties from the request
   */
  private function setPropertiesFromRequest() {
    $requestValues = CRM_Utils_Request::exportValues();
    if (isset($requestValues['bid'])) {
      $this->getBatchData($requestValues['bid']);
    }
    if (isset($requestValues['date_from'])) {
      $this->_dateFrom = $requestValues['date_from'];
    }
    if (isset($requestValues['date_to'])) {
      $this->_dateTo = $requestValues['date_to'];
    }
    if (isset($requestValues['claim_types'])) {
      $this->_claimTypes = $requestValues['claim_types'];
    }
  }
  /**
   * Method to get the batch data and store in property
   *
   * @param $batchId
   * @access private
   */
  private function getBatchData($batchId) {
    // get batch id from request
    try {
      $this->_batchData = civicrm_api3('ClaimBatch', 'getsingle', array('id' => $batchId));
      $config = CRM_Expenseclaims_Config::singleton();
      $this->_batchData['batch_status'] = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimStatusOptionGroup('id'),
        'value' => $this->_batchData['batch_status_id'],
        'return' => 'label'
      ));
      $this->assign('batchDescription', $this->_batchData['description']);
      $this->assign('batchCreatedDate', $this->_batchData['created_date']);
      $this->assign('batchStatus', $this->_batchData['batch_status']);
    } catch (CiviCRM_API3_Exception $ex) {}
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
        'claim_status' => $dao->claim_status
      );
    }
    $this->assign('currentClaims', $currentClaims);
  }

  /**
   * Method to create the query for the current claims in batch
   *
   * @return string
   */
  private function getCurrentClaimsQuery() {
    $config = CRM_Expenseclaims_Config::singleton();
    $query = "SELECT pcbe.entity_id AS claim_id, cc.display_name AS claim_submitted_by, 
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
      1 => array(3, 'Integer'),
      2 => array($config->getClaimStatusOptionGroup('id'), 'Integer'),
      3 => array($this->_batchData['id'], 'Integer'),
      4 => array('civicrm_activity', 'String')
    );
    return $params;

  }
}
