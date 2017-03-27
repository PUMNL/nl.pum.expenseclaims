<div class="crm-content-block crm-block">
  {if !empty($pageHeader)}
    <div id="help">
      {ts}{$pageHeader}{/ts}
    </div>
  {/if}
  <div class="action-link">
    <a class="button new-option" href="{$addUrl}">
      <span><div class="icon add-icon"></div>New Contact(s) for Claim Level</span>
    </a>
  </div>
  {include file="CRM/common/pager.tpl" location="top"}
  {include file='CRM/common/jsortable.tpl'}
  <div id="claim_level_contact-wrapper" class="dataTables_wrapper">
    <table id="claim_level_contact-table" class="display">
      <thead>
      <tr>
        <th>{ts}Contact{/ts}</th>
        <th id="nosort"></th>
      </tr>
      </thead>
      <tbody>
      {assign var="rowClass" value="odd-row"}
      {assign var="rowCount" value=0}
      {foreach from=$claimLevelContacts key=claimLevelContactId item=contact}
        {assign var="rowCount" value=$rowCount+1}
        <tr id="row{$rowCount}" class="{cycle values="odd,even"}"}>
          <td hidden="1">{$claimLevelContactId}
          <td>{$contact.contact_name}</td>
          <td>
              <span>
                {foreach from=$contact.actions item=actionLink}
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
      <span><div class="icon add-icon"></div>New Contact(s) for Claim Level</span>
    </a>
  </div>
</div>