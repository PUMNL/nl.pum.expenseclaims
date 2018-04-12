<?php

class CRM_Expenseclaims_Page_AdministerClaims extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('Administer Claims'));

    $this->assign('administerClaims',$this->administerClaims());

    parent::run();
  }

  private function administerClaims() {
    $config = CRM_Expenseclaims_Config::singleton();
    $sql = "SELECT lc.contact_id AS 'id', c.display_name FROM pum_claim_level_contact lc JOIN civicrm_contact c ON c.id = lc.contact_id";

    $otherPeople = array();
    $dao = CRM_Core_DAO::executeQuery($sql);
    while($dao->fetch()){
      $otherPerson = array();
      $otherPerson['id'] = $dao->id;
      $otherPerson['display_name'] = $dao->display_name;
      $otherPerson['contact_id'] = $dao->id;
      $manageUrl = CRM_Utils_System::url('civicrm/pumexpenseclaims/page/myclaims', 'reset=1&approverid='.$dao->id, true);
      $otherPerson['action'] = '<a class="action-item" title="Manage" href="'.$manageUrl.'">Manage</a>';
      $otherPeople[$dao->id]=$otherPerson;
    }
    $this->assign('otherPeople',$otherPerson);
    return $otherPeople;
  }
}
