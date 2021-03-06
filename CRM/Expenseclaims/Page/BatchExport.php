<?php
/**
 * @author Klaas Eikelboom (CiviCooP) klaas.eikelboom@civicoop.org
 * @date  02 jun 2017
 * @license AGPL-3.0
 */
class CRM_Expenseclaims_Page_BatchExport extends CRM_Core_Page {

  public static function claimsql(){
    $config = CRM_Expenseclaims_Config::singleton();

    $claimsql = "
 SELECT   DISTINCT cact.id  AS claim_id
 ,        ci.{$config->getClaimLinkCustomField('column_name')}          AS claim_link
 ,        ci.{$config->getClaimTotalAmountCustomField('column_name')}   AS claim_total_amount
 ,        ci.{$config->getClaimDescriptionCustomField('column_name')}   AS claim_description
 ,        ci.{$config->getClaimStatusCustomField('column_name')}        AS claim_status_id
 ,        ci.{$config->getClaimTypeCustomField('column_name')}          AS claim_type_id
 ,        cac.contact_id                                                AS claim_submitted_by
 ,        adata.shortname_14                                            AS shortname
 ,        c.display_name                                                AS display_name
 ,        adr.street_address                                            AS street_address
 ,        adr.postal_code                                               AS postal_code
 ,        adr.city                                                      AS city
 ,        adr.country_id                                                AS country_id

 ,        {$config->getBankInformationCustomFields()['IBAN_nummer']}                AS iban_number
 ,        {$config->getBankInformationCustomFields()['BIC_Swiftcode']}              AS bic_swiftcode
 ,        {$config->getBankInformationCustomFields()['Accountholder_name']}         AS accountholder_name
 ,        {$config->getBankInformationCustomFields()['Accountholder_address']}      AS accountholder_address
 ,        {$config->getBankInformationCustomFields()['Accountholder_postal_code']}  AS accountholder_postal_code
 ,        {$config->getBankInformationCustomFields()['Accountholder_city']}         AS accountholder_city
 ,        {$config->getBankInformationCustomFields()['Accountholder_country']}      AS accountholder_country
 ,        {$config->getBankInformationCustomFields()['Foreign_Bank_Account']}       AS foreign_bank_account
 ,        {$config->getBankInformationCustomFields()['Bank_Account_Number']}        AS bank_account_number
 ,        cact.activity_date_time                   AS claim_submitted_date
 ,        csov.label                                AS claim_status

 ,        ctov.label                                AS claim_type
 ,        ctov.grouping                             AS gl_account_number
 ,        ctov.name                                 AS fa_default_donor
 ,        line.expense_date                         AS expense_date
 ,        line.expense_type                         AS expense_type
 ,        ltov.grouping                             AS pum_account_number
 ,        curr.name                                 AS currency
 ,        line.currency_amount                      AS currency_amount
 ,        line.euro_amount                          AS euro_amount
 ,        line.exchange_rate                        AS exchange_rate
 ,        line.description                          AS description
 ,        line.distance_km                          AS distance_km
 ,        line.cost_center                          AS cost_center
 ,        pum_case.case_sequence                    AS case_sequence
 ,        pum_case.case_type                        AS case_type
 ,        pum_case.case_country                     AS case_country
 ,        IFNULL(ccny.name, pccl.display_name)      AS client_country
 ,        seg.label                                 AS expert_sector
 ,        cap.label                                 AS case_name
 FROM         pum_claim_batch                   cb
 JOIN         pum_claim_batch_entity            cbe       ON (cbe.batch_id = cb.id and cbe.entity_table='civicrm_activity')
 LEFT   JOIN  civicrm_activity                  cact      ON (cact.id = cbe.entity_id)
 LEFT   JOIN  civicrm_activity_contact          cac       ON (cbe.entity_id = cac.activity_id and cac.record_type_id=2)
 LEFT   JOIN  civicrm_contact                   c         ON (cac.contact_id = c.id)
 LEFT   JOIN  civicrm_address                   adr       ON (adr.contact_id = c.id and adr.is_primary=1)
 LEFT   JOIN  civicrm_value_bank_information_11 bank      ON (bank.entity_id = c.id)
 LEFT   JOIN  civicrm_value_additional_data_4   adata     ON (adata.entity_id = c.id)
 LEFT   JOIN  pum_claim_line                    line      ON line.activity_id = cact.id
 LEFT   JOIN  {$config->getClaimInformationCustomGroup('table_name')} ci on (ci.entity_id = cbe.entity_id)
 LEFT   JOIN  civicrm_currency                  curr      ON (curr.id = line.currency_id)
 LEFT   JOIN  civicrm_case_pum                  pum_case  ON (pum_case.entity_id =  ci.{$config->getClaimLinkCustomField('column_name')})
 LEFT   JOIN  civicrm_option_value              csov      ON ci.{$config->getClaimStatusCustomField('column_name')} = csov.value AND csov.option_group_id = {$config->getClaimStatusOptionGroup('id')}
 LEFT   JOIN  civicrm_option_value              ctov      ON ci.{$config->getClaimTypeCustomField('column_name')} = ctov.value AND ctov.option_group_id = {$config->getClaimTypeOptionGroup('id')}
 LEFT   JOIN  civicrm_option_value              ltov      ON (line.expense_type = ltov.value collate utf8_general_ci AND ltov.option_group_id = {$config->getClaimLineTypeOptionGroup('id')})
 LEFT   JOIN  civicrm_case                      cas       ON cas.id = ci.pum_projectnumber_referencenumbe_387
 LEFT   JOIN  civicrm_option_value              cap       ON cap.option_group_id = (SELECT id FROM civicrm_option_group WHERE name = 'case_type') AND cap.value = cas.case_type_id
 /* To display country in export */
  LEFT JOIN civicrm_case_contact pccc ON pccc.case_id = ci.pum_projectnumber_referencenumbe_387
  LEFT JOIN civicrm_contact pccl ON (
    CASE pccl.contact_sub_type WHEN CONCAT(char(1),'Country',char(1)) THEN pccl.id = pccc.contact_id END
  )
  LEFT JOIN civicrm_address padr ON padr.contact_id = pccc.contact_id AND padr.is_primary = 1
  LEFT JOIN civicrm_country ccny ON ccny.id = padr.country_id
 /* End display country in export */
 /* To display expert sector in export */
  LEFT JOIN civicrm_contact_segment               cs        ON cs.contact_id = c.id AND cs.is_active = 1 AND cs.is_main = 1
  LEFT JOIN civicrm_segment                       seg       ON seg.id = cs.segment_id AND seg.is_active = 1
 /* End display expert sector in export */
 WHERE  cb.id = %1";

    return $claimsql;
  }

  private function donorCode($caseId) {
    if (empty($caseId)) {
      return FALSE;
    }
    else {

      $contact_id = CRM_Threepeas_BAO_PumDonorLink::getCaseFADonor($caseId);
      $donorCode = CRM_Core_DAO::singleValueQuery("SELECT donor_code_363 FROM civicrm_value_donor_details_fa_65 WHERE entity_id=%1", [
        '1' => [$contact_id, 'Integer']
      ]);
      return $donorCode;
    }
}

function run() {

    $bid    = CRM_Utils_Request::retrieve('bid', 'Positive', $this, TRUE);

    $claimsql = CRM_Expenseclaims_Page_BatchExport::claimsql();

    $heading  = array(
      'claim_id',
      'claim_link_id',
      'claim_pum_case_number',
      'fa_donor',
      'claim_total_amount',
      'claim_feedback',
      'claim_status',
      'claim_role',
      'project_number',
      'submitted_by',
      'shortname',
      'display_name',
      'street_address',
      'postal_code',
      'city',
      'country',
      'iban_number',
      'bic_swiftcode',
      'accountholder_name',
      'accountholder_address',
      'accountholder_postal_code',
      'accountholder_city',
      'accountholder_country',
      'foreign_bank_account',
      'bank_account_number',
      'submitted_date',
      'expense_date',
      'expense_type',
      'gl_account_number',
      'currency',
      'currency_amount',
      'euro_amount',
      'exchange_rate',
      'description',
      'distance_km',
      'cost_center',
      'Projecttype',
      'Sector',
      'Artikel',        //Strange column name for client country is due to financial system that require these columns
      'Omschrijving'    //Strange column name for client country is due to financial system that require these columns
    );

    $buffer = implode(';',array_map("CRM_Expenseclaims_Utils::csvField",$heading))."\n";

    $dao = CRM_Core_DAO::executeQuery($claimsql,array(
      '1' => array($bid,'Integer')
    ));
    while($dao->fetch()){
     $donorCode = $this->donorCode($dao->claim_link);
     $pumCaseNumber = $dao->case_country.$dao->case_sequence.$dao->case_type;
     $line = array (
       $dao->claim_id,
       $dao->claim_link,
       $pumCaseNumber?$pumCaseNumber:$dao->gl_account_number, //claim_pum_case_number, request from finance: if pumCaseNumber == empty, show gl_account_number
       $donorCode?$donorCode:$dao->fa_default_donor,    //fa_donor
       $dao->claim_total_amount,
       $dao->claim_description,                         //claim_feedback
       $dao->claim_status,
       $dao->claim_type,                                //claim_role
       $dao->gl_account_number,                         //project_number: grouping from option group pum_claim_type
                                                        //don't ask my why but finance department don't want general ledger code in general ledger column :(
       $dao->claim_submitted_by,
       $dao->shortname,
       $dao->display_name,
       $dao->street_address,
       $dao->postal_code,
       $dao->city,
       CRM_Core_Pseudoconstant::getName('CRM_Core_BAO_Address', 'country_id', $dao->country_id),

       $dao->iban_number,
       $dao->bic_swiftcode,
       $dao->accountholder_name,
       $dao->accountholder_address,
       $dao->accountholder_postal_code,
       $dao->accountholder_city,
       $dao->accountholder_country,
       $dao->foreign_bank_account,
       $dao->bank_account_number,

       $dao->claim_submitted_date,
       $dao->expense_date,
       $dao->expense_type,

       $dao->pum_account_number,                        //gl_account_number: general ledger code (grouping from option_group pum_claim_line_type)
                                                        //don't ask my why but finance department wants general ledger code in column 'gl_account_number' :(
       $dao->currency,
       $dao->currency_amount,
       $dao->euro_amount,
       $dao->exchange_rate,
       $dao->description,
       $dao->distance_km,
       $dao->cost_center,

       $dao->case_name,
       $dao->expert_sector,
       $dao->client_country,
       $dao->client_country                             //2 times client country is correct, it due to financial system that require these columns
     );

     $buffer .= implode(';',array_map("CRM_Expenseclaims_Utils::csvField",$line))."\n";

    }

    if (!$buffer) {
      CRM_Core_Error::statusBounce('The file is either empty or you do not have permission to retrieve the file');
    }

    CRM_Utils_System::download(
      CRM_Utils_File::cleanFileName(basename("claimbatch-$bid.csv")),
      'text/csv',
      $buffer
    );
  }

}