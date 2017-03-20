<div class="crm-content-block crm-block">
  <div id="help">
    {ts}On this you can view the claims that are in the batch and add other claims to the batch.
      Use the selection criteria to influence the claims you can select from.{/ts}
  </div>
    {* include batch info on top of page *}
    {include file="CRM/Expenseclaims/Page/BatchData.tpl"}
    {* include claims currently in batch *}
    {include file="CRM/Expenseclaims/Page/BatchCurrentClaims.tpl"}
    {* include selection criteria for claims that can be selected for batch *}
    {include file="CRM/Expenseclaims/Page/BatchClaimSelectCriteria.tpl"}
    {* include list of claims that can be selected for batch *}
</div>
