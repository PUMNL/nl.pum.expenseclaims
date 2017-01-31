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
    <div class="label">
      <label for="valid_types">{$form.valid_types.label}</label>
    </div>
    <div class="content crm-select-container" id="valid_types_block">
      {$form.valid_types.html}
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_level_valid_main_activities">
    <div class="label">
      <label for="valid_main_activities">{$form.valid_main_activities.label}</label>
    </div>
    <div class="content crm-select-container" id="valid_main_activities_block">
      {$form.valid_main_activities.html}
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_level_authorizing_level">
    <div class="label">{$form.authorizing_level.label}</div>
    <div class="content">{$form.authorizing_level.html}<br/><em>{ts}The role to authorize claims from this role{/ts}</em></div>
    <div></div>
    <div class="clear"></div>
  </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
