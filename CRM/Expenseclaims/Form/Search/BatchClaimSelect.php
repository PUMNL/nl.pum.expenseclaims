<?php
/**
 * Custom search to select and find claims in batch
 * PUM Senior Experts
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 20 March 2017
 * @license AGPL-3.0
 */
class CRM_Expenseclaims_Form_Search_BatchClaimSelect extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {

  private $_whereClauses = array();
  private $_whereParams = array();
  private $_whereIndex = NULL;
  private $_claimTypeList = array();
  private $_batchData = array();

  /**
   * CRM_Expenseclaims_Form_Search_BatchClaimSelect constructor.
   *
   * @param $formValues
   */
  function __construct(&$formValues) {
    $this->setClaimTypeList();
    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    $this->getBatchInfo();
    CRM_Utils_System::setTitle(ts('Find PUM Senior Experts Expense Claim Batch'));
    if (!empty($this->_batchData)) {
      $form->assign('batchDescription', $this->_batchData['description']);
      $form->assign('batchCreatedDate', $this->_batchData['created_date']);
      $form->assign('batchStatus', $this->_batchData['batch_status']);
    }
    // find current claims in batch
    $form->assign('currentClaims', $this->getCurrentClaimsForBatch());
    // search on from .... to
    $form->addDate('claim_date_from', ts('Date From'), FALSE);
    $form->addDate('claim_date_to', ts('...to'), FALSE);
    // search on claim type
    $form->add('select', 'claim_type', ts('Claim Type(s)'), $this->_claimTypeList, FALSE,
      array('id' => 'claim_type', 'multiple' => 'multiple', 'title' => ts('- select -'))
    );
    $form->add('hidden', 'batch_id');
    $form->assign('elements', array('claim_date_from', 'claim_date_to', 'claim_type', 'batch_id'));
    $form->addButtons(array(array('type' => 'refresh', 'name' => ts('Search'), 'isDefault' => TRUE,),));
  }
  function setDefaultValues() {
    $defaults = array();
    if (isset($this->_batchData['id'])) {
      $defaults['batch_id'] = $this->_batchData['id'];
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
    $params = array(
      1 => array(3, 'Integer'),
      2 => array($config->getClaimStatusOptionGroup('id'), 'Integer'),
      3 => array($this->_batchData['id'], 'Integer'),
      4 => array('civicrm_activity', 'String')
    );
    $dao = CRM_Core_DAO::executeQuery($query, $params);
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
    return $currentClaims;
  }

  /**
   * Method to get the batch data and store in property
   *
   * @return array
   * @access private
   */
  private function getBatchInfo() {
    $requestValues = CRM_Utils_Request::exportValues();
    // get batch id from request or form
    if (isset($requestValues['bid'])) {
      $batchId = $requestValues['bid'];
    } else {
      if (isset($requestValues['batch_id'])) {
        $batchId = $requestValues['batch_id'];
      } else {
        if (isset($this->_formValues['batch_id'])) {
          $batchId = $this->_formValues['batch_id'];
        }
      }
    }
    try {
      $this->_batchData = civicrm_api3('ClaimBatch', 'getsingle', array('id' => $batchId));
      $config = CRM_Expenseclaims_Config::singleton();
      $this->_batchData['batch_status'] = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => $config->getClaimStatusOptionGroup('id'),
        'value' => $this->_batchData['batch_status_id'],
        'return' => 'label'
      ));
    } catch (CiviCRM_API3_Exception $ex) {}
  }

  /**
   * Method to get the list of claim types
   *
   * @return array
   * @access private
   */
  private function setClaimTypeList() {
    $config = CRM_Expenseclaims_Config::singleton();
    $claimTypes = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => $config->getClaimTypeOptionGroup('id'),
      'is_active' => 1
    ));
    foreach ($claimTypes['values'] as $claimType) {
      $this->_claimTypeList[$claimType['value']] = $claimType['label'];
    }
    asort($this->_claimTypeList);
    return;
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      ts('Batch ID') => 'batch_id',
      ts('Description') => 'description',
      ts('Status') => 'batch_status',
      ts('Date Created') => 'created_date',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    // delegate to $this->sql(), $this->select(), $this->from(), $this->where(), etc.
    return $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "DISTINCT(batch.id) AS batch_id, batch.description, batch.created_date, ov.label AS batch_status";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    $config = CRM_Expenseclaims_Config::singleton();
    return "FROM pum_claim_batch batch
    LEFT JOIN civicrm_option_value ov ON batch.batch_status_id COLLATE utf8_unicode_ci = ov.value AND ov.option_group_id = ".$config->getBatchStatusOptionGroup();
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @param bool $includeContactIDs
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $this->_whereClauses = array();
    $this->_whereParams = array();
    $this->addStatusWhereClauses();
    $this->addTypeWhereClauses();
    $this->addPeriodWhereClauses();
    if (!empty($this->_whereClauses)) {
      $where = implode(' AND ', $this->_whereClauses);
    } else {
      return "";
    }
    return $this->whereClause($where, $this->_whereParams);
  }

  /**
   * Method to set date range clauses
   *
   * @param $fieldName
   * @param $columnName
   */
  private function setDateRangeClauses($fieldName, $columnName) {
    if (isset($this->_formValues[$fieldName.'_from']) && !empty($this->_formValues[$fieldName.'_from'])) {
      $fromDate = new DateTime($this->_formValues[$fieldName.'_from']);
      $this->_whereIndex++;
      $fromIndex = $this->_whereIndex;
      $this->_whereParams[$fromIndex] = array($fromDate->format('Y-m-d'), 'String');
    }
    if (isset($this->_formValues[$fieldName.'_to']) && !empty($this->_formValues[$fieldName.'_to'])) {
      $toDate = new DateTime($this->_formValues[$fieldName.'_to']);
      $this->_whereIndex++;
      $toIndex = $this->_whereIndex;
      $this->_whereParams[$toIndex] = array($toDate->format('Y-m-d'), 'String');
    }
    if (isset($fromIndex) && isset($toIndex)) {
      $this->_whereClauses[] = $columnName.' BETWEEN %'.$fromIndex.' AND %'.$toIndex;
    } else {
      if (isset($fromIndex)) {
        $this->_whereClauses[] = $columnName.' >= %'.$fromIndex;
      }
      if (isset($toIndex)) {
        $this->_whereClauses[] = $columnName.' <= %'.$toIndex;
      }
    }
  }

  /**
   * Method to add the status where clauses
   */
  private function addStatusWhereClauses() {
    if (isset($this->_formValues['claim_status'])) {
      $claimStatuses = array();
      foreach ($this->_formValues['claim_status'] as $claimStatus) {
        $this->_whereIndex++;
        $claimStatuses[$this->_whereIndex] = $claimStatus;
        $this->_whereParams[$this->_whereIndex] = array($claimStatus, 'String');
      }
      if (!empty($claimStatuses)) {
        $this->_whereClauses[] = '(batch.claim_status IN('.implode(', ', $claimStatuses).'))';
      }
    }
  }

  /**
   * Method to add the type where clauses
   */
  private function addTypeWhereClauses() {
    if (isset($this->_formValues['claim_type'])) {
      $claimTypes = array();
      foreach ($this->_formValues['claim_type'] as $claimType) {
        $this->_whereIndex++;
        $claimTypes[$this->_whereIndex] = $claimType;
        $this->_whereParams[$this->_whereIndex] = array($claimTypes, 'String');
      }
      if (!empty($claimTypes)) {
        $this->_whereClauses[] = '(batch.claim_type IN('.implode(', ', $claimTypes).'))';
      }
    }
  }

  /**
   * Method to add the batch date where clauses
   *
   * @access private
   */
  private function addPeriodWhereClauses() {
    if (isset($this->_formValues['batch_date_from']) || isset($this->_formValues['batch_date_to'])) {
      $this->setDateRangeClauses('batch_date', 'batch.created_date');
    }
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/Expenseclaims/Form/BatchClaimSelect.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @throws exception if function getOptionGroup not found
   * @return void
   */
  function alterRow(&$row) {
  }

  /**
   * Method to count selected batches
   *
   * @return string
   */
  function count() {
    return CRM_Core_DAO::singleValueQuery($this->sql('COUNT(DISTINCT batch.id) as total'));
  }

  /**
   * Overridden parent method - contact search expects contacts to be part of sql where in batches this does
   * not happen nor is it required.
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $returnSQL
   * @return string
   */
  function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = FALSE) {
    $sql = $this->sql(
      'DISTINCT(batch.id) AS batch_id',
      $offset,
      $rowcount,
      $sort
    );

    if ($returnSQL) {
      return $sql;
    }

    return CRM_Core_DAO::composeQuery($sql, CRM_Core_DAO::$_nullArray);
  }

}
