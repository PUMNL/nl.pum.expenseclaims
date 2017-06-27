{* HEADER *}
<h3>{ts}Claim Info{/ts}</h3>

{* dialog for claim line history *}
<div id="pum_claims_line_history_dialog-block">
  <p><label id="claim_line_history"></label></p>
</div>


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
      {if $action eq 2}
         <div class="content">{$form.claim_description.html}</div>
      {/if}
      {if $action eq 4}
            <div class="content">{$form.claim_description.value}</div>
      {/if}
      <div class="clear"></div>
    </div>
    <div class="crm-section pum_claim_link">
      {if $action eq 2}
          <div class="label">{$form.claim_link.label}</div>
          <div class="content">{$form.claim_link.html}</div>
      {/if}
      {if $action eq 4}
          <div class="label">Link</div>
          <div class="content">{$claimLinkDescription}</div>
      {/if}
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
            <th>{ts}Line ID{/ts}</th>
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
              <td>{$claimLineId}</td>
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
                <span>
                  <a class="action-item" title="History" href="#" onclick="showClaimLineHistory({$claimLineId})">History</a>
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
  <h3>{ts}Audit Trail{/ts}</h3>
    <div class="crm-block crm-form-block">
        <div id="audittrail-wrapper" class="dataTables_wrapper">
            <table id="audittrail-table" class="display">
                <tr>
                    <th>{ts}Approver{/ts}</th>
                    <th>{ts}Processing Date{/ts}</th>
                    <th>Is approved?</th>
                    <th>Is rejected?</th>
                    <th>Is payable</th>
                </tr>
                {assign var="rowClass" value="odd-row"}
                {foreach from=$claimLogs key=claimLogId item=claimLog}
                <tr class="{$rowClass}">
                    <td>{$claimLog.display_name}</td>
                    <td>{$claimLog.processed_date|crmDate}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                    {if $rowClass eq "odd-row"}
                        {assign var="rowClass" value="even-row"}
                    {else}
                        {assign var="rowClass" value="odd-row"}
                    {/if}
                {/foreach}
            </table>
        </div>
    </div>


  {if !empty($attachments)}
    <h3>{ts}Attachments{/ts}</h3>
    <div class="crm-block crm-form-block">
      <ul>
      {foreach from=$attachments key=attachmentId item=attachment}
        <li> {$attachment} </li>
      {/foreach}
      </ul>
    </div>
  {/if}
  {* FOOTER *}


  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
{literal}
  <script>
    function showClaimLineHistory(claimLineId) {
      CRM.api3('ClaimLineLog', 'get', {"claim_line_id": claimLineId})
        .done(function(result) {
          cj("#pum_claims_line_history_dialog-block").dialog({
            width: 700,
            height: 450,
            title: "Claim Line History line " + claimLineId,
            buttons: {
              "Done": function() {
                cj(this).dialog("close");
              }
            }
          });
          var dialogText = [];
          var dialogIndex = 0;
          cj.each(result.values, function(resultkey, resultValue) {
            var line = [];
            var lineValues = [];
            var lineIndex = 0;
            var valueIndex = 0;
            cj.each(resultValue, function(paramKey, paramValue){
              if (paramKey === 'changed_date') {
                line[lineIndex] = 'Change on ' + paramValue;
                lineIndex++;
              }
              if (paramKey === 'changed_by') {
                line[lineIndex] = ' by ' + paramValue;
                lineIndex++;
              }
              if (paramKey === 'change_reason') {
                line[lineIndex] = ' with reason ' + paramValue;
                lineIndex++;
              };
              if (paramKey === 'old_expense_date') {
                lineValues[valueIndex] = '<li>old expense date was ' + paramValue + '</li>';
                valueIndex++;
              }
              if (paramKey === 'new_expense_date') {
                lineValues[valueIndex] = '<li>new expense date is ' + paramValue + '</li>';
                valueIndex++;
              }
              if (paramKey === 'old_currency') {
                lineValues[valueIndex] = '<li>old currency was ' + paramValue + '</li>';
                valueIndex++;
             }
              if (paramKey === 'new_currency') {
                lineValues[valueIndex] = '<li>new currency is ' +  paramValue + '</li>';
                valueIndex++;
              }
              if (paramKey === 'old_currency_amount') {
                lineValues[valueIndex] = '<li>old amount in currency was ' + paramValue + '</li>';
                valueIndex++;
              }
              if (paramKey === 'new_currency_amount') {
                lineValues[valueIndex] = '<li>new amount in currency is ' + paramValue + '</li>';
                valueIndex++;
              }
            });
            if (line) {
              dialogText[dialogIndex] = line.join(' ') + '<ul>' + lineValues.join('') + '</ul>';
              dialogIndex++;
            }
          });
          cj("#pum_claims_line_history_dialog-block").html(dialogText.join('\n'));
        });
    };
  </script>
  <script>
      function showClaimHistory(claimId){
          cj("#pum_claims_line_history_dialog-block").dialog({
              width: 700,
              height: 450,
              title: "Claim Line History line " + claimId,
              buttons: {
                  "Done": function() {
                      cj(this).dialog("close");
                  }
              }
          });
      };
  </script>
{/literal}
