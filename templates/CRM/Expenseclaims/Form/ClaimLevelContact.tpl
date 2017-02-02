{* HEADER *}
{if !empty($actionHeader)}
  <h3>{$actionHeader}</h3>
{/if}
<div id="help">
  {ts}Select one or more contacts for this claim level and press save{/ts}
</div>
<div class="crm-block crm-form-block">
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <div class="crm-section pum_claim_level_contacts">
    <div class="label">
      <label for="claim_level_contacts">{$form.claim_level_contacts.label}</label>
    </div>
    <div class="content crm-select-container" id="claim_level_contacts_block">
      {$form.claim_level_contacts.html}
    </div>
    <div class="clear"></div>
  </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
