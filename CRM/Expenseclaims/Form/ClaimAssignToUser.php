<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Expenseclaims_Form_ClaimAssignToUser extends CRM_Core_Form {

  private $_contacts = array();

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Assign claim to another user'));
    $values = $this->exportValues();
    $valuesToArray = $this->retrieveValuesFromURL($values['entryURL']);
    $session = CRM_Core_Session::singleton();
    $currentUser = $session->get('userID');

    if(!empty($values['claim_assign_contacts'])) {
      $valuesToArray['approver_id'] = $values['claim_assign_contacts'];
    }

    //Check permission
    if (($currentUser != $valuesToArray['approver_id']) && (CRM_Core_Permission::check('manage others claims') == FALSE)) {
      CRM_Core_Session::setStatus('Sorry, you are not allowed to manage this claim', 'Claims', 'error');
      parent::buildQuickForm();
    } else {

      // add form elements
      $this->add(
        'select', // field type
        'claim_assign_contacts', // field name
        ts('New Contact for this Claim'), // field label
        $this->_contacts, // list of options
        TRUE // is required
      );

      // add buttons
      $this->addButtons(array(
        array('type' => 'submit', 'name' => ts('Save'), 'isDefault' => TRUE),
        array('type' => 'cancel', 'name' => ts('Cancel'))
      ));

      $this->assign('approver_id',$valuesToArray['approver_id']);
      // export form elements
      $this->assign('elementNames', $this->getRenderableElementNames());
    }

    parent::buildQuickForm();
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $this->getContacts();
  }

  /**
   * This function sets the default values for the form. For edit/view mode
   * the default values are retrieved from the database
   *
   * @access public
   *
   * @return None
   */
  function setDefaultValues() {
    $defaults = $this->_values;

    $defaults['claim_assign_contacts'] = CRM_Utils_Array::value('claim_assign_contacts', $defaults);

    return $defaults;
  }


  public function postProcess() {
    $values = $this->exportValues();

    $valuesToArray = $this->retrieveValuesFromURL($values['entryURL']);
    if(!empty($values['claim_assign_contacts'])) {
      $valuesToArray['approver_id'] = $values['claim_assign_contacts'];
    }

    $config = CRM_Expenseclaims_Config::singleton();
    try {
      $sql = 'UPDATE '.$config->getClaimInformationCustomGroup('table_name').' SET pum_claim_status = %1 WHERE entity_id = %2 ORDER BY id DESC LIMIT 1';
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array((int)$config->getWaitingForApprovalClaimStatusValue(), 'Integer'),
        2 => array((int)$claimId, 'Integer')
      ));
      $sql = 'UPDATE pum_claim_log SET approval_contact_id = %1, acting_approval_contact_id = %2, is_approved = %3, is_rejected = %4, is_payable = %5, old_status_id = new_status_id, new_status_id = %6, processed_date = %7 WHERE claim_activity_id = %8 ORDER BY id DESC LIMIT 1';
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($valuesToArray['approver_id'], 'String'),
        2 => array(NULL,'Date'),
        3 => array((int)0, 'Integer'),
        4 => array((int)0, 'Integer'),
        5 => array((int)0, 'Integer'),
        6 => array((int)$config->getWaitingForApprovalClaimStatusValue(),'Integer'),
        7 => array(NULL,'Date'),
        8 => array((int)$valuesToArray['claim_id'], 'Integer')));
    } catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_log_message($e->getCode() & " - " & $e->getMessage(), FALSE); //log message to log file
      CRM_Core_Session::setStatus('Failed to assign claim to another user: '.$e->getMessage(), 'error', 'error'); //show message on screen
    }

    $approverID = CRM_Core_Session::singleton()->getLoggedInContactID();
    $MyClaimsURL = CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1&approverid='.$approverID, TRUE);
    CRM_Utils_System::redirect($MyClaimsURL);

    try {
      $whoseClaims = civicrm_api3('contact', 'getvalue', [
        'id' => (int)$valuesToArray['approver_id'],
        'return' => 'display_name'
      ]);
      CRM_Core_Session::setStatus('Claim: '.$valuesToArray['claim_id'].' is successfully assigned to: '.$whoseClaims.'. Please make sure that you also assign the case to the assigned project officer, otherwise this claim cannot be approved.', 'success', 'success');
    } catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_log_message($e->getCode() & " - " & $e->getMessage(), FALSE); //log message to log file
      CRM_Core_Session::setStatus('Failed to get assigned user: '.$e->getMessage(), 'error', 'error'); //show message on screen
    }

    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Method to get available contacts (employees of PUM)
   * (with CRM_Core_DAO for performance reasons)
   *
   * @return array
   */
  private function getContacts() {
    $this->_contacts = array();
    if (empty($this->_claimLevelId) && isset($this->_submitValues['claim_level_id'])) {
      $this->_claimLevelId = $this->_submitValues['claim_level_id'];
    }
    $sql = 'SELECT ct.id, ct.sort_name FROM civicrm_contact ct
            LEFT JOIN civicrm_group_contact gc ON gc.contact_id = ct.id
            WHERE ct.contact_type = %1 AND
                  gc.group_id IN (SELECT id FROM civicrm_group WHERE title = %2) AND
                  gc.status = %3
            ORDER BY sort_name';
    $sqlParams = array(
      1 => array('Individual', 'String'),
      2 => array('PUM Staff', 'String'),
      3 => array('Added', 'String')
    );
    $contact = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    while ($contact->fetch()) {
      $this->_contacts[$contact->id] = $contact->sort_name;
    }
  }

  /**
   *
   */
  private function retrieveValuesFromURL($entryURL) {
    $queryStr = parse_url($entryURL, PHP_URL_QUERY);
    $queryStrStripped = str_replace('amp;','&',$queryStr);
    parse_str($queryStrStripped, $urlParams);

    return $urlParams;
  }
}
