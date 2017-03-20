<?php

/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 20 March 2017
 * @license AGPL-3.0
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Expenseclaims_Form_ClaimBatch extends CRM_Core_Form {

  /**
   * Method to build the QuickForm
   */
  public function buildQuickForm() {
    // add form elements
    $this->add('text', 'description', ts('Description'), array('size' => '80'), true);
    // add buttons
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
    parent::buildQuickForm();
  }

  /**
   * Method to process results from the form
   */
  public function postProcess() {
    $this->saveClaimBatch();
    parent::postProcess();
  }

  /**
   * Method to save the claim batch
   *
   */
  private function saveClaimBatch() {
    if (!empty($this->_submitValues)) {
      $config = CRM_Expenseclaims_Config::singleton();
      $nowDate = new DateTime();
      $params = array(
        'created_date' => $nowDate->format('Ymd'),
        'batch_status_id' => $config->getOpenBatchStatusId(),
        'description' => $this->_submitValues['description']
      );
      CRM_Expenseclaims_BAO_ClaimBatch::add($params);
    }
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    CRM_Utils_System::setTitle(ts('PUM Senior Experts New Claim Batch'));
    // get id of the custom search for claim batch and use that for userContext
    try {
      $customSearchId = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'custom_search',
        'name' => 'CRM_Expenseclaims_Form_Search_FindBatch',
        'return' => 'value'
      ));
      $session = CRM_Core_Session::singleton();
      $session->pushUserContext(CRM_Utils_System::url('civicrm/contact/search/custom', 'reset=1&csid=' . $customSearchId, true));
    } catch (CiviCRM_API3_Exception $ex) {}
  }
}
