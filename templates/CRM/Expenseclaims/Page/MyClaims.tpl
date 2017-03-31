<div class="crm-content-block crm-block">
  <div id="help">
    {ts}My Claims (needing approval or were rejected).{/ts}
  </div>
  {include file="CRM/common/pager.tpl" location="top"}
  {include file='CRM/common/jsortable.tpl'}
  <div id="my_claims-wrapper" class="dataTables_wrapper">
    <table id="my_claims-table" class="display">
      <thead>
      <tr>
        <th>{ts}Claim ID{/ts}</th>
        <th>{ts}Claim Type{/ts}</th>
        <th>{ts}Submitted By{/ts}</th>
        <th>{ts}Submitted Date{/ts}</th>
        <th>{ts}Link{/ts}</th>
        <th>{ts}Total Amount{/ts}</th>
        <th>{ts}Status{/ts}</th>
        <th>{ts}Description{/ts}</th>
        <th id="nosort"></th>
      </tr>
      </thead>
      <tbody>
      {assign var="rowClass" value="odd-row"}
      {assign var="rowCount" value=0}
      {foreach from=$myClaims key=claimId item=myClaim}
        {assign var="rowCount" value=$rowCount+1}
        <tr id="row{$rowCount}" class="{cycle values="odd,even"}">
          <td>{$claimId}</td>
          <td>{$myClaim.type}</td>
          <td>{$myClaim.submitted_by}</td>
          <td>{$myClaim.submitted_date|crmDate}</td>
          {if !empty($myClaim.link_url)}
            <td><a href="{$myClaim.link_url}">{$myClaim.link}</a></td>
          {else}
            <td>{$myClaim.link}</td>
          {/if}
          <td>{$myClaim.total_amount|crmMoney}</td>
          <td>{$myClaim.status}</td>
          <td>{$myClaim.description}</td>
          <td>
              <span>
                {foreach from=$myClaim.actions item=actionLink}
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
  {include file="CRM/common/pager.tpl" location="bottom"}
</div>