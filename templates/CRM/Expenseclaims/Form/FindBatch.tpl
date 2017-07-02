{* Template for "FindExpert" custom search component. *}
{assign var="showBlock" value="'searchForm'"}
{assign var="hideBlock" value="'searchForm_show','searchForm_hide'"}

<div class="crm-form-block crm-search-form-block">
  <div id="searchForm">
    {include file="CRM/Expenseclaims/Form/FindBatchCriteria.tpl"}
  </div>
</div>
<div class="crm-submit-buttons">
  <a class="button new-option" href="{$addUrl}">
    <span><div class="icon add-icon"></div>New Claim Batch</span>
  </a>
</div>

{if $rowsEmpty}
  {include file="CRM/Contact/Form/Search/Custom/EmptyResults.tpl"}
{/if}

{if $summary}
  {$summary.summary}: {$summary.total}
{/if}

{if $rows}
  {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
  {assign var="showBlock" value="'searchForm_show'"}
  {assign var="hideBlock" value="'searchForm'"}

  <fieldset>

    {* This section displays the rows along and includes the paging controls *}
    <p>

      {include file="CRM/common/pager.tpl" location="top"}

      {include file="CRM/common/pagerAToZ.tpl"}

      {strip}
    <table class="selector" summary="{ts}Search results listings.{/ts}">
      <thead class="sticky">
      {foreach from=$columnHeaders item=header}
        <th scope="col">
          {if $header.sort}
            {assign var='key' value=$header.sort}
            {$sort->_response.$key.link}
          {else}
            {$header.name}
          {/if}
        </th>
      {/foreach}
      <th>&nbsp;</th>
      </thead>

      {counter start=0 skip=1 print=false}
      {foreach from=$rows item=row}
        <tr id='rowid{$row.batch_id}' class="{cycle values="odd-row,even-row"}">
          {foreach from=$columnHeaders item=header}
            {assign var=fName value=$header.sort}
            <td>{$row.$fName}</td>
          {/foreach}
          <td>
            <span>
              <a href="{crmURL p='civicrm/pumexpenseclaims/form/batchclaimselect' q="action=update&bid=`$row.batch_id`"}"
               class="action-item action-item-first" title="claims">{ts}Claims{/ts}</a>
            </span>
            {if $row.batch_status eq Open}
            <span>
              <a href="#"  onclick="closeBatch({$row.batch_id},'{$row.description}',{$exportedBatchStatusId})"
                 class="action-item action-item-first" title="claims">{ts}Close for export{/ts}
              </a>
            </span>
            {else}
            <span>
              <a href="{crmURL p='civicrm/pumexpenseclaims/claimexport' q="reset=1&bid=`$row.batch_id`"}"
                 class="action-item action-item-first" title="export">{ts}Export CSV{/ts}</a>
            </span>
            {/if}
          </td>
        </tr>
      {/foreach}
    </table>
    {/strip}

    {include file="CRM/common/pager.tpl" location="bottom"}

    </p>
  </fieldset>
  {* END Actions/Results section *}
{/if}

<div id="enableDisableStatusMsg" class="crm-container" style="display:none"></div>
{literal}
<script type="text/javascript">
    function closeBatch(id,description,batch_status_id) {
        cj("#enableDisableStatusMsg").dialog({
            title: 'Confirm Changes',
            modal: true,
            open: function () {
                cj('#enableDisableStatusMsg').show().html('Do you want to close batch '+description+' for export?');
            },
            buttons: {
                "Cancel": function () {
                    cj(this).dialog("close");
                },
                "OK": function () {
                    CRM.api3('ClaimBatch', 'create', {'id':id, 'batch_status_id':batch_status_id})
                        .done(function () {

                        });
                    cj(this).dialog("close");
                    cj('#Custom').submit();

                }
            }
        });
    }
</script>
{/literal}


