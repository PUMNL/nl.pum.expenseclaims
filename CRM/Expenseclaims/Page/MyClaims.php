<?php
/**
 * Page My Claims to list all claims that require my action
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 20 Feb 2017
 * @license AGPL-3.0
 */

class CRM_Expenseclaims_Page_MyClaims extends CRM_Core_Page {
  private $_userContactId = NULL;
  private $_approverId = NULL;

  /**
   * Standard run function created when generating page with Civix
   *
   * @access public
   */
  function run() {
    $this->setPageConfiguration();
    $this->initializePager();
    $this->_approverId = CRM_Utils_Request::retrieve('approverid', 'Positive', $this, FALSE, $this->_userContactId);

    if( $this->_approverId == $this->_userContactId){
       $whoseClaims = 'myself';
    } else {
       $whoseClaims = civicrm_api3('contact','getvalue',array(
         'id' => $this->_approverId,
         'return' => 'display_name'
       ));
    }

    if (($this->_approverId != $this->_userContactId) && (CRM_Core_Permission::check('view others claims') == FALSE)) {
      CRM_Core_Session::setStatus('Sorry, you are not allowed to view claims for this user', 'Claims', 'error');
    } else {
      $this->assign('whoseClaims',$whoseClaims);
      $myClaims = $this->getMyClaims($this->_approverId);
      CRM_Utils_System::setTitle(ts("Approve or reject claims for $whoseClaims"));
      $this->assign('myClaims', $myClaims);
    }

    parent::run();
  }

  /**
   * Function to get my claims
   *
   * @return array $myClaims
   * @access protected
   */
  protected function getMyClaims($contactId) {
    $myClaims = array();
    $config = CRM_Expenseclaims_Config::singleton();
    list($offset, $limit) = $this->_pager->getOffsetAndRowCount();
    $query = "
SELECT pclog.claim_activity_id
,      cac.contact_id AS claim_submitted_by
,      cact.activity_date_time AS claim_submitted_date
,      pcc.{$config->getClaimLinkCustomField('column_name')} AS claim_link
,      pcc.{$config->getClaimTotalAmountCustomField('column_name')} AS claim_total_amount
,      pcc.{$config->getClaimDescriptionCustomField('column_name')} AS claim_description
,      pcc.{$config->getClaimStatusCustomField('column_name')}     AS claim_status_id
,      pcc.{$config->getClaimTypeCustomField('column_name')} AS claim_type_id
,      csov.label AS claim_status
,      ctov.label AS claim_type
FROM pum_claim_log pclog
LEFT JOIN civicrm_activity cact ON pclog.claim_activity_id = cact.id
LEFT JOIN {$config->getClaimInformationCustomGroup('table_name')} pcc ON cact.id = pcc.entity_id
LEFT JOIN civicrm_activity_contact cac ON cact.id = cac.activity_id AND cac.record_type_id = %1
LEFT JOIN civicrm_option_value csov ON pcc.{$config->getClaimStatusCustomField('column_name')} = csov.value AND csov.option_group_id = %2
LEFT JOIN civicrm_option_value ctov ON pcc.{$config->getClaimTypeCustomField('column_name')} = ctov.value AND ctov.option_group_id = %3
INNER JOIN (SELECT claim_activity_id, max(id) id FROM pum_claim_log GROUP BY claim_activity_id) b ON pclog.id=b.id AND pclog.claim_activity_id = b.claim_activity_id
WHERE pclog.approval_contact_id = %4
AND pclog.processed_date IS NULL
AND pclog.claim_activity_id NOT IN (SELECT be.entity_id FROM pum_claim_batch_entity be)
LIMIT %6, %7";
    $queryParams = array(
      1 => array($config->getTargetRecordTypeId(), 'Integer'),
      2 => array($config->getClaimStatusOptionGroup('id'), 'Integer'),
      3 => array($config->getClaimTypeOptionGroup('id'), 'Integer'),
      4 => array($contactId, 'Integer'),
      6 => array($offset, 'Integer'),
      7 => array($limit, 'Integer')
    );
    $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
    while ($dao->fetch()) {
      $row = array();
      $row['type'] = $dao->claim_type;
      $row['submitted_by_cid'] = $dao->claim_submitted_by;
      $row['submitted_by_cid_url'] = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$row['submitted_by_cid']}");
      $row['submitted_by'] = CRM_Threepeas_Utils::getContactName($dao->claim_submitted_by);
      $row['submitted_date'] = $dao->claim_submitted_date;
      if ($dao->claim_type_id == 'project' | $dao->claim_type_id == 'representative') {
        $row['link'] = $this->getLinkCaseSubject($dao->claim_link);
        $row['link_url'] = $this->getLinkUrl($dao->claim_link);
      } else {
        $row['link'] = $dao->claim_link;
        $row['link_url'] = NULL;
      }
      $row['total_amount'] = $dao->claim_total_amount;
      $row['status'] = $dao->claim_status;
      $row['description'] = $dao->claim_description;
      $row['actions'] = $this->setRowActions($dao->claim_activity_id);
      $myClaims[$dao->claim_activity_id] = $row;
    }
    return $myClaims;
  }

  /**
   * Method to get the URL for Case View Summary
   *
   * @param $caseId
   * @return string
   */
  private function getLinkUrl($caseId) {
    if (method_exists('CRM_Threepeas_Utils', 'getCaseClientId')) {
      $caseClientId = CRM_Threepeas_Utils::getCaseClientId($caseId);
    } else {
      $sql = "SELECT contact_id FROM civicrm_case_contact WHERE case_id = %1";
      $caseClientId = CRM_Core_DAO::singleValueQuery($sql, array(1 => array($caseId, 'Integer')));
    }
    return CRM_Utils_System::url('civicrm/contact/view/case', 'reset=1&action=view&id='.$caseId.'&cid='
      .$caseClientId, TRUE);
  }
  /**
   * Method to get the case subject for a main activity claim link
   *
   * @param $claimLinkCaseId
   * @return string
   */
  private function getLinkCaseSubject($claimLinkCaseId) {
    $link = 'Main Activity: ';
    if (!is_numeric($claimLinkCaseId)) {
      $link .= ' (could not find main activity with ID '.$claimLinkCaseId.')';
    }
    try {
      $link .= civicrm_api3('Case', 'getvalue', array(
        'id' => $claimLinkCaseId,
        'return' => 'subject'
      ));
    } catch (CiviCRM_API3_Exception $ex) {
      $link .= ' (could not find main activity with ID '.$claimLinkCaseId.')';
    }
    return $link;
  }

  /**
   * Function to set the row action urls and links for each row
   *
   * @param int $claimId
   * @return array $actions
   * @access protected
   */
  protected function setRowActions($claimId) {
    $actions = array();
    $manageUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claim', 'action=update&id='.$claimId.'&approverid='.$this->_approverId, true);
    $assignUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/form/claimassigntouser', 'reset=1&claim_id='.$claimId.'&approverid='.$this->_approverId, true);
    $actions[] = '<a class="action-item" title="Manage" href="'.$manageUrl.'">Manage</a>';
    $actions[] = '<a class="action-item" title="Assign to other user" href="'.$assignUrl.'">Assign to other user</a>';
    return $actions;
  }

  /**
   * Function to set the page configuration
   *
   * @access protected
   */
  protected function setPageConfiguration() {
    CRM_Utils_System::setTitle(ts("PUM Senior Experts Expense My Claims"));
    $session = CRM_Core_Session::singleton();
    $this->_userContactId = $session->get('userID');
    $session->pushUserContext(CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1', true));
  }

  /**
   * Method to initialize pager
   *
   * @access protected
   */
  protected function initializePager() {
    $config = CRM_Expenseclaims_Config::singleton();
    if (!is_int($this->_userContactId)) {
      $this->_userContactId = (int)$this->_userContactId;
    }
    $this->_approverId = '';

    try{
      $this->_approverId = (int)CRM_Utils_Request::retrieve('approverid', 'Integer');
    } catch (Exception $ex) {

    }

    if (is_int($this->_userContactId) | is_int($this->_approverId)) {
      try {
        $values = array(
          1 => array($config->getTargetRecordTypeId(), 'Integer'),
          2 => array($config->getClaimStatusOptionGroup('id'), 'Integer'),
          3 => array($config->getClaimTypeOptionGroup('id'), 'Integer'),
          4 => array($this->_userContactId, 'Integer')
        );
        if(!empty($this->_approverId) && is_int($this->_approverId)) {
          $values[4] = array($this->_approverId, 'Integer');
        } elseif(is_int($this->_userContactId)) {
          $values[4] = array($this->_userContactId, 'Integer');
        }

        $params           = array(
          'total' => CRM_Core_DAO::singleValueQuery("
            SELECT COUNT(*)
              FROM pum_claim_log pclog
              LEFT JOIN civicrm_activity cact ON pclog.claim_activity_id = cact.id
              LEFT JOIN {$config->getClaimInformationCustomGroup('table_name')} pcc ON cact.id = pcc.entity_id
              LEFT JOIN civicrm_activity_contact cac ON cact.id = cac.activity_id AND cac.record_type_id = %1
              LEFT JOIN civicrm_option_value csov ON pcc.{$config->getClaimStatusCustomField('column_name')} = csov.value AND csov.option_group_id = %2
              LEFT JOIN civicrm_option_value ctov ON pcc.{$config->getClaimTypeCustomField('column_name')} = ctov.value AND ctov.option_group_id = %3
              WHERE pclog.approval_contact_id = %4 AND pclog.processed_date IS NULL",
            $values
          ),
          'rowCount' => 20,
          'status' => ts('Expense Claim Levels %%StatusMessage%%'),
          'buttonBottom' => 'PagerBottomButton',
          'buttonTop' => 'PagerTopButton',
          'pageID' => $this->get(CRM_Utils_Pager::PAGE_ID),
        );

        $this->_pager = new CRM_Utils_Pager($params);
        $this->assign_by_ref('pager', $this->_pager);
      } catch (Exception $ex) {

      }
    }
  }

}
