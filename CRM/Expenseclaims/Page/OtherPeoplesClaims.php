<?php

class CRM_Expenseclaims_Page_OtherPeoplesClaims extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(ts('Other Peoples Claims'));
    $this->assign('currentTime', date('Y-m-d H:i:s'));
    $this->assign('otherPeople',$this->otherPeople());
    parent::run();
  }
  private function otherPeople() {

    $config = CRM_Expenseclaims_Config::singleton();
    $sql = "SELECT c.id, c.display_name FROM civicrm_contact c
JOIN civicrm_group_contact gc ON (gc.contact_id = c.id)
JOIN civicrm_group gr ON (gc.group_id = gr.id AND gr.title='Project Officers')";


    $otherPeople = [];
    $dao = CRM_Core_DAO::executeQuery($sql,array(
      '1' => array($config->getPumCfo(),'Integer'),
      '2' => array($config->getPumCpo(),'Integer'),
    ));
    while($dao->fetch()){
      $otherPerson = [];
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
