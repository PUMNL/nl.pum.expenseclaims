{* HEADER *}

<div id="help">
  {ts}Select the new contact for this claim and press save{/ts}
</div>
<div class="crm-block crm-form-block">

  {foreach from=$elementNames item=elementName}
    <div class="crm-section pum_claim_assign_to_user">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}

  {* FIELD EXAMPLE: OPTION 2 (MANUAL LAYOUT)

    <div>
      <span>{$form.favorite_color.label}</span>
      <span>{$form.favorite_color.html}</span>
    </div>

  {* FOOTER *}
  <div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>