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
  {* include claim lines part if there are any *}
  {if !empty($claimLines)}
    <h3>{ts}Claim Lines{/ts}</h3>
    <div class="crm-block crm-form-block">
      <div id="claim_lines-wrapper" class="dataTables_wrapper">
        <table id="claim_lines-table" class="display">
          <thead>
          <tr>
            <th>{ts}Date{/ts}</th>
            <th>{ts}Description{/ts}</th>
            <th>{ts}Type{/ts}</th>
            <th>{ts}Amt. in Currency{/ts}</th>
            <th>{ts}Amt. in Euro{/ts}</th>
            <th>{ts}Exchange Rate{/ts}</th>
            <th id="nosort"></th>
          </tr>
          </thead>
          <tbody>
          {assign var="rowClass" value="odd-row"}
          {assign var="rowCount" value=0}
          {foreach from=$claimLines key=claimLineId item=claimLine}
            {assign var="rowCount" value=$rowCount+1}
            <tr id="row{$rowCount}" class={$rowClass}>
              <td hidden="1">{$claimLineId}
              <td>{$claimLine.date|crmDate}</td>
              <td>{$claimLine.description}</td>
              <td>{$claimLine.type}</td>
              <td>{$claimLine.currency_amount|crmNumberFormat:2}&nbsp;{$claimLine.currency}</td>
              <td>{$claimLine.euro_amount|crmMoney}</td>
              <td>{$claimLine.exchange_rate|crmNumberFormat:2}</td>
              <td>
                  <span>
                    {foreach from=$claimLine.actions item=actionLink}
                      {$actionLink}
                    {/foreach}
                  </span>
              </td>
            </tr>
            {if $rowClass eq "odd-row"}
              {assign var="rowClass" value="even-row"}
            {else}
              {assign var="rowClass" value="odd-row"}
            {/if}
          {/foreach}
          </tbody>
        </table>
      </div>
    </div>
  <div class="crm-block crm-form-block">
    <div class="crm-section pum_claim_total_amount">
      <strong>
        <div class="label">{$form.claim_total_amount.label}</div>
        <div class="content">{$form.claim_total_amount.value|crmMoney}</div>
        <div class="clear"></div>
      </strong>
    </div>
  </div>

  {/if}
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
