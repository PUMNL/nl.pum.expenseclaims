<div class="crm-content-block crm-block">
  <div id="help">
    {ts}The existing PUM Senior Experts Claim Authorization Levels.{/ts}
  </div>
  <div class="action-link">
    <a class="button new-option" href="{$addUrl}">
      <span><div class="icon add-icon"></div>New Claim Authorization Level</span>
    </a>
  </div>
  {include file="CRM/common/pager.tpl" location="top"}
  {include file='CRM/common/jsortable.tpl'}
  <div id="claim_level-wrapper" class="dataTables_wrapper">
    <table id="claim_level-table" class="display">
      <thead>
        <tr>
          <th>{ts}Level{/ts}</th>
          <th>{ts}Max Amount{/ts}</th>
          <th>{ts}Valid Types{/ts}</th>
          <th>{ts}Valid Main Activities{/ts}</th>
          <th>{ts}Authorizing Level{/ts}</th>
          <th id="nosort"></th>
        </tr>
      </thead>
      <tbody>
      {assign var="rowClass" value="odd-row"}
      {assign var="rowCount" value=0}
      {foreach from=$claimLevels key=levelId item=level}
        {assign var="rowCount" value=$rowCount+1}
        <tr id="row{$rowCount}" class={$rowClass}>
          <td hidden="1">{$levelId}
          <td>{$level.label}</td>
          <td>{$level.max_amount}</td>
          <td>{$level.valid_types}</td>
          <td>{$level.valid_main_activities}</td>
          <td>{$level.authorizing_level}</td>
          <td>
              <span>
                {foreach from=$level.actions item=actionLink}
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
  <div class="action-link">
    <a class="button new-option" href="{$addUrl}">
      <span><div class="icon add-icon"></div>New Claim Authorization Level</span>
    </a>
  </div>
</div>