<?php
/**
 * Created by PhpStorm.
 * User: Klaas
 * Date: 31-5-2017
 * Time: 14:37
 */
function _civicrm_api3_claim_submit_spec(&$spec) {
  $spec['id'] = array(
    'name' => 'id',
    'title' => 'Claim Identifier',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  );
}

function civicrm_api3_claim_submit($params){
  $claim= civicrm_api3('claim','getsingle',array('id' => $params['id']));
  $params['id']=$claim['id'];
  $params['claim_type'] = $claim['claim_type_id'];
  $params['claim_link']=  $claim['claim_linked_to'];
  $bao = new CRM_Expenseclaims_BAO_Claim();
  $bao->createFirstStep($params);

  return civicrm_api3_create_success($claim, $params, 'Claim', 'Create');
}