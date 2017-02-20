{* HEADER *}
<h3>{ts}Claim Info{/ts}</h3>

<div class="crm-block crm-form-block">
    <div class="crm-section pum_claim_submitted_by">
      <div class="label">{$form.claim_submitted_by.label}</div>
      <div class="content">{$form.claim_submitted_by.value}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section pum_claim_submitted_date">
      <div class="label">{$form.claim_submitted_date.label}</div>
      <div class="content">{$form.claim_submitted_date.value|crmDate}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section pum_claim_description">
      <div class="label">{$form.claim_description.label}</div>
      <div class="content">{$form.claim_description.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section pum_claim_link">
      <div class="label">{$form.claim_link.label}</div>
      <div class="content">{$form.claim_link.html}</div>
      <div class="clear"></div>
    </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
