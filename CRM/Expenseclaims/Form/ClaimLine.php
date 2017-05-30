<?php

/**
 * Form controller class
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 21 Feb 2017
 * @license AGPL-3.0
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Expenseclaims_Form_ClaimLine extends CRM_Core_Form {

  protected $_claimLineId = NULL;
  protected $_expenseTypeList = array();
  protected $_currencyList = array();
  protected $_claimLine = array();

  /**
   * Method to build the QuickForm
   */
  public function buildQuickForm() {
    // add form elements
    $this->add('hidden', 'claim_line_id');
    $this->addDate('expense_date', ts('Expense Date'), true);
    $this->add('text', 'description', ts('Description'), array(),true);
    $this->add('select', 'expense_type', ts('Expense Type'), $this->_expenseTypeList, true);
    $this->add('select', 'currency_id', ts('Currency'), $this->_currencyList, true);
    $this->add('text', 'currency_amount', ts('Amount in Currency'), array(), true);
    $this->add('text', 'euro_amount', ts('Amount in Euro'));
    $this->add('text', 'reason_for_change', ts('Reason for Change'), array() ,true);
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
    if (isset($this->_submitValues['claim_line_id'])) {
      $this->_claimLineId = $this->_submitValues['claim_line_id'];
    }
    $this->saveClaimLine();
    parent::postProcess();
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['claim_line_id'] = $this->_claimLineId;
    if (isset($this->_claimLine['expense_date']) && !empty($this->_claimLine['expense_date'])) {
      list($defaults['expense_date']) = CRM_Utils_Date::setDateDefaults($this->_claimLine['expense_date']);
    } else {
      list($defaults['expense_date']) = '';
    }
    if (isset($this->_claimLine['description'])) {
      $defaults['description'] = $this->_claimLine['description'];
    }
    if (isset($this->_claimLine['expense_type'])) {
      $defaults['expense_type'] = $this->_claimLine['expense_type'];
    }
    if (isset($this->_claimLine['currency_id'])) {
      $defaults['currency_id'] = $this->_claimLine['currency_id'];
    }
    if (isset($this->_claimLine['currency_amount'])) {
      $defaults['currency_amount'] = $this->_claimLine['currency_amount'];
    }
    if (isset($this->_claimLine['euro_amount'])) {
      $defaults['euro_amount'] = $this->_claimLine['euro_amount'];
    }
    if (isset($this->_claimLine['reason_for_change'])) {
      $defaults['reason_for_change'] = $this->_claimLine['reason_for_change'];
    }
    return $defaults;
  }

  /**
   * Method to save the claim line
   *
   */
  private function saveClaimLine() {
    if (!empty($this->_submitValues)) {

      if($this->_submitValues['currency_amount']==0){
        $euro_amount=0;
      } else {
        $euro_amount=CRM_Expenseclaims_Utils::calculateEuroAmount($this->_submitValues['currency_amount'],
          $this->_submitValues['currency_id']);
      }

      $params = array(
        'id' => $this->_claimLineId,
        'activity_id' => $this->_claimLine['activity_id'],
        'expense_date' => date('Y-m-d', strtotime($this->_submitValues['expense_date'])),
        'expense_type' => $this->_submitValues['expense_type'],
        'currency_id' => $this->_submitValues['currency_id'],
        'currency_amount' => $this->_submitValues['currency_amount'],
        'euro_amount' => $euro_amount,
        'description' => $this->_submitValues['description']
      );
      // add reason for change for log entry
      if (isset($this->_submitValues['reason_for_change'])) {
        $params['change_reason'] = $this->_submitValues['reason_for_change'];
      }
      CRM_Expenseclaims_BAO_ClaimLine::add($params);
    }
  }

  /**
   * Overridden parent method to initiate form
   *
   * @access public
   */
  function preProcess() {
    $config = CRM_Expenseclaims_Config::singleton();
    $values = CRM_Utils_Request::exportValues();
    if (isset($values['claim_line_id'])) {
      $this->_claimLineId = $values['claim_line_id'];
    } else {
      if (isset($values['id'])) {
        $this->_claimLineId = $values['id'];
      }
    }
    if ($this->_action == CRM_Core_Action::UPDATE) {
      $this->_claimLine = CRM_Expenseclaims_BAO_ClaimLine::getWithId($this->_claimLineId);
    }
    CRM_Utils_System::setTitle(ts('PUM Senior Experts Expense Edit Claim Line'));
    $currency = new CRM_Financial_DAO_Currency();
    $currency->find();
    while ($currency->fetch()) {
      $this->_currencyList[$currency->id] = $currency->name;
    }
    $expenseTypes = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => $config->getClaimLineTypeOptionGroup('id'),
      'is_active' => 1));
    foreach ($expenseTypes['values'] as $expenseType) {
      $this->_expenseTypeList[$expenseType['value']] = $expenseType['label'];
    }
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claim', 'action=update&id='.$this->_claimLine['activity_id'], true));
  }
}
