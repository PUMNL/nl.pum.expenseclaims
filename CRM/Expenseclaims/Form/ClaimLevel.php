<?php

/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 31 Jan 2017
 * @license AGPL-3.0
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Expenseclaims_Form_ClaimLevel extends CRM_Core_Form {

  protected $_claimLevelId = NULL;
  protected $_claimLevel = array();

  /**
   * Method to build the QuickForm
   */
  public function buildQuickForm() {
    // add form elements
    $this->add('hidden', 'claim_level_id');
    $this->add('select', 'label', ts('Label'), $this->getClaimLevelLabels());
    $this->add('text', 'max_amount', ts('Max Amount'), array('size' => 14), true);
    $this->add('select', 'valid_types', ts('Label'), $this->getValidTypes());
    $this->add('select', 'valid_main_activities', ts('Main Activities'), $this->getValidMainActivities());
    $this->add('select', 'authorization_level', ts('Label'), $this->getClaimLevelLabels());
    // add buttons
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => true,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));

    parent::buildQuickForm();
  }
  private function getClaimLevelLabels() {

  }
  private function getValidTypes() {

  }
  private function getValidMainActivities() {

  }

  /**
   * Method to process results from the form
   */
  public function postProcess() {
    $this->_claimLevelId = $this->_submitValues['claim_level_id'];
    if ($this->_action != CRM_Core_Action::VIEW) {
      $this->saveClaimLevel($this->_submitValues);
    }
    parent::postProcess();
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $this->_claimLevelId = CRM_Utils_Request::retrieve('id', 'Integer');
    if ($this->_action != CRM_Core_Action::ADD && $this->_claimLevelId) {
      $this->_claimLevel = civicrm_api3('ClaimLevel', 'Getsingle', array('id' => $this->_claimLevelId));
    }
    if ($this->_action == CRM_Core_Action::DELETE) {
      $this->deleteClaimLevelAndReturn();
    }
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $actionLabel = "Add Expense Claim Level";
        break;
      case CRM_Core_Action::UPDATE:
        $actionLabel = "Edit Expense Claim Level";
        break;
    }
    CRM_Utils_System::setTitle(ts('PUM Senior Experts Expense Claim Level'));
    $this->assign('actionLabel', $actionLabel);
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['segment_id'] = $this->_segmentId;
    if (!isset($this->_segment['parent_id']) || empty($this->_segment['parent_id'])) {
      $defaults['segment_type'] = 'parent';
    } else {
      $defaults['segment_type'] = 'child';
    }
    if ($this->_action == CRM_Core_Action::VIEW || $this->_action == CRM_Core_Action::UPDATE) {
      $defaults['segment_label'] = $this->_segment['label'];
      if ($this->_segment['parent_id']) {
        $defaults['segment_parent'] = $this->_segment['parent_id'];
      }
    }
    $defaults['is_active'] = true;
    if ($this->_segmentId && empty($this->_segment['is_active'])) {
      $defaults['is_active'] = false;
    }

    return $defaults;
  }

  /**
   * Function to save the segment
   *
   * @param $formValues
   * @access protected
   */
  protected function saveSegment($formValues) {
    $params = array();
    if ($formValues['segment_id']) {
      $params['id'] = $formValues['segment_id'];
    }
    $params['label'] = $formValues['segment_label'];
    $params['is_active'] = $formValues['is_active'] ? '1' : '0';
    $params['name'] = CRM_Contactsegment_Utils::generateNameFromLabel($params['label']);
    if ($this->_action == CRM_Core_Action::ADD) {
      $segmentType = key($formValues['segment_type_list']);
    } else {
      if ($formValues['segment_parent']) {
        $segmentType = 1;
      }
    }
    switch ($segmentType) {
      case 0:
        $params['parent_id'] = NULL;
        $statusTitle = $this->_parentLabel." saved";
        $statusMessage = $this->_parentLabel." ".$params['label']." saved";
        break;
      case 1:
        $params['parent_id'] = $formValues['segment_parent'];
        $statusTitle = $this->_childLabel." saved";
        $statusMessage = $this->_childLabel." ".$params['label']." from "
          .$this->_parentLabel." ".$this->getSegmentParentLabel($formValues['segment_parent'])." saved";
        break;
    }
    $this->_segment = civicrm_api3('Segment', 'Create', $params);
    $session = CRM_Core_Session::singleton();
    $session->setStatus($statusMessage, $statusTitle, "success");
  }

  /**
   * Method to delete segment
   *
   */
  protected function deleteSegmentAndReturn() {
    if (!$this->_segment['parent_id']) {
      $statusMessage = $this->_parentLabel." ".$this->_segment['label']." deleted";
      $statusTitle = $this->_parentLabel." deleted";
    } else {
      $statusMessage = $this->_childLabel." ".$this->_segment['label']." from "
        .$this->_parentLabel." ".$this->getSegmentParentLabel($this->_segment['parent_id'])." deleted";
      $statusTitle = $this->_childLabel." deleted";
    }
    civicrm_api3('Segment', 'Delete', array('id' => $this->_segmentId));
    $session = CRM_Core_Session::singleton();
    $session->setStatus($statusMessage, $statusTitle, "success");
    CRM_Utils_System::redirect($session->readUserContext());
  }

}
