<?php
/**
 * Custom search to Find Batch for PUM Senior Experts Expense Claims
 * PUM Senior Experts
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 7 March 2017
 * @license AGPL-3.0
 */
class CRM_Expenseclaims_Form_Search_FindBatch extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {

  // properties for clauses, params, searchColumns and likes
  private $_whereClauses = array();
  private $_whereParams = array();
  private $_whereIndex = NULL;

  // properties for select lists
  private $_batchStatusList = array();
  /**
   * CRM_Expenseclaims_Form_Search_FindBatch constructor.
   *
   * @param $formValues
   */
  function __construct(&$formValues) {
    $this->setBatchStatusList();
    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(ts('Find PUM Senior Experts Expense Claim Batch'));

    // search on from .... to
    $form->addDate('batch_date_from', ts('Date From'), FALSE);
    $form->addDate('batch_date_to', ts('...to'), FALSE);

    $form->add('select', 'batch_status', ts('Batch status(us)'), $this->_batchStatusList, FALSE,
      array('id' => 'batch_status', 'multiple' => 'multiple', 'title' => ts('- select -'))
    );

    $form->assign('elements', array('batch_date_from', 'batch_date_to', 'claim_status', 'claim_type'));
    $form->assign('addUrl', CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimbatch', 'action=add&reset=1', true));

    $form->addButtons(array(array('type' => 'refresh', 'name' => ts('Search'), 'isDefault' => TRUE,),));
  }

  /**
   * Method to get the list of batch status types
   *
   * @return array
   * @access private
   */
  private function setBatchStatusList() {
    $config = CRM_Expenseclaims_Config::singleton();
    $batchStatuses = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => $config->getBatchStatusOptionGroup('id'),
      'is_active' => 1
    ));
    foreach ($batchStatuses['values'] as $batchStatus) {
      $this->_batchStatusList[$batchStatus['value']] = $batchStatus['label'];
    }
    asort($this->_batchStatusList);
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

    $sqlall = $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
    CRM_Core_Error::debug_var("Find Batch All",$sqlall);

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
    LEFT JOIN civicrm_option_value ov ON batch.batch_status_id COLLATE utf8_unicode_ci = ov.value AND ov.option_group_id = "
      .$config->getBatchStatusOptionGroup();
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
    $this->addBatchStatusWhereClauses();
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
   * Method to add the batch status where clauses
   */
  private function addBatchStatusWhereClauses() {
    if (isset($this->_formValues['batch_status'])) {
      $batchStatuses = array();
      foreach ($this->_formValues['batch_status'] as $batchStatus) {
        $this->_whereIndex++;
        $batchStatuses[$this->_whereIndex] = $batchStatus;
        $this->_whereParams[$this->_whereIndex] = array($batchStatus, 'String');
      }
      if (!empty($batchStatuses)) {
        $this->_whereClauses[] = '(ov.value IN('.implode(', ', $batchStatuses).'))';
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
    return 'CRM/Expenseclaims/Form/FindBatch.tpl';
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
