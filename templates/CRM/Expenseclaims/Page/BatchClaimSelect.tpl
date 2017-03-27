<div class="crm-content-block crm-form-block">
  {* include batch info on top of page *}
  {include file="CRM/Expenseclaims/Page/BatchData.tpl"}
  {* include claims currently in batch *}
  {include file="CRM/Expenseclaims/Page/BatchCurrentClaims.tpl"}
  {* include selection criteria for claims that can be selected for batch *}
  {include file="CRM/Expenseclaims/Page/BatchClaimSelectCriteria.tpl"}
  {* include list of claims that can be selected for batch *}
  {include file="CRM/Expenseclaims/Page/BatchSubsetClaims.tpl"}
</div>
