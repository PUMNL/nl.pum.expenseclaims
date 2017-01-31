{* HEADER *}
<h3>{$actionHeader}</h3>

<div class="crm-block crm-form-block">
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <div class="crm-section pum_claim_level_label">
    <div class="label">{$form.label.label}</div>
    <div class="content">{$form.label.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_level_max_amount">
    <div class="label">{$form.max_amount.label}</div>
    <div class="content">{$form.max_amount.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_level_valid_types">
    <div class="label">{$form.valid_types.label}</div>
    <div class="content">{$form.valid_types.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_level_valid_main_activities">
    <div class="label">{$form.valid_main_activities.label}</div>
    <div class="content">{$form.valid_main_activities.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_level_authorizing_level">
    <div class="label">{$form.authorizing_level.label}</div>
    <div class="content">{$form.authorizing_level.html}</div>
    <div class="clear"></div>
  </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
