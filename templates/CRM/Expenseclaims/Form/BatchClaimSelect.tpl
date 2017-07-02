{* HEADER *}
{* include batch info on top of page *}
{include file="CRM/Expenseclaims/Page/BatchData.tpl"}
{* include claims currently in batch *}
{include file="CRM/Expenseclaims/Page/BatchCurrentClaims.tpl"}

{if $batchOpen}
<div class="crm-block crm-form-block">
  <div class="crm-accordion-wrapper crm-criteria_search-accordion">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Claim Selection Criteria{/ts}
    </div>
    <div class="crm-accordion-body">
      <div id="help">
        {ts}This section allows you to set criteria for the filtering of the approved claims that are not in the batch yet.{/ts}
      </div>
      <div class="crm-section">
        <div class="label">{$form.claim_from_date.label}</div>
        <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=claim_from_date}</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">{$form.claim_to_date.label}</div>
        <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=claim_to_date}</div>
        <div class="clear"></div>
      </div>
      {* FOOTER *}
      <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>

<div class="crm-block crm-form-block">
  <div class="crm-block crm-form-block">
    <div class="crm-accordion-wrapper crm-criteria_search-accordion">
      <div class="crm-accordion-header crm-master-accordion-header">
        {ts}Approved Claims not in Batch{/ts}
      </div>
      <div class="crm-accordion-body">
        <div id="help">
          {ts}You can select approved claims and add them to the batch.{/ts}
        </div>
        <div class="crm-submit-buttons">
          <a class="button new-option" href="#" id="add_claims_to_batch">
            <span><div class="icon add-icon"></div>Add Selected to Batch</span>
          </a>
        </div>
        <table class="selector subset" summary="{ts}Search results listings.{/ts}">
          <thead class="sticky">
          <tr>
            <th scope="col" title="Select All Rows">
              <input id="toggleSelectAll" name="toggleSelectAll" value="1" class="form-checkbox" type="checkbox"
                     title="select all claims" />
            </th>
            <th scope="col">{ts}Claim ID{/ts}</th>
            <th scope="col">{ts}Description{/ts}</th>
            <th scope="col">{ts}Submitted By{/ts}</th>
            <th scope="col">{ts}Submitted Date{/ts}</th>
            <th scope="col">{ts}Link{/ts}</th>
            <th scope="col">{ts}Total Amount{/ts}</th>
            <th scope="col">{ts}Status{/ts}</th>
            <th scope="col"></th>
          </tr>
          </thead>

          {counter start=0 skip=1 print=false}
          {foreach from=$subsetClaims item=subsetClaim}
            <tr id='rowid{$subsetClaim.claim_id}' class="{cycle values="odd-row,even-row"}">
              <td>
                <input id="selectClaim_{$subsetClaim.claim_id}" name="selectClaim" value="1"
                  class="form-checkbox-row select-claims-check" type="checkbox" title="select all claims">
              </td>
              <td>{$subsetClaim.claim_id}</td>
              <td>{$subsetClaim.claim_description}</td>
              <td>{$subsetClaim.claim_submitted_by}</td>
              <td>{$subsetClaim.claim_submitted_date|crmDate}</td>
              <td>{$subsetClaim.claim_link}</td>
              <td>{$subsetClaim.claim_total_amount}</td>
              <td>{$subsetClaim.claim_status}</td>
              <td>
                <span>
                  <a href="{crmURL p='civicrm/pumexpenseclaims/form/claim' q="action=view&id=`$subsetClaim.claim_id`"}"
                  class="action-item action-item-first" title="lines">{ts}Lines{/ts}</a>
                </span>
              </td>
            </tr>
          {/foreach}
        </table>
      </div>
    </div>
  </div>
</div>
{literal}
  <script type="text/javascript">
    cj('#add_claims_to_batch').click(function() {
      cj('.form-checkbox-row').each(function () {
        if (cj(this).attr('name') === 'selectClaim') {
          // if checked is true, add claim to batch
          var checked = cj(this).is(":checked");
          if (checked) {
            var claimId = cj(this).attr('id').substr(12);
            var batchId = cj('#batch_id').val();
            CRM.api3('ClaimBatchEntity', 'create', {'batch_id':batchId, 'entity_id':claimId, 'entity_table':'civicrm_activity'})
              .done(function () {
              });
          };
        };
      });
      // rebuild form to show newly added claims
      window.location.reload();
    });
    // toggle all checkboxes with select all
    cj('#toggleSelectAll').change(function() {
      var checkboxes = cj(this).closest('form').find('.select-claims-check');
      if(cj(this).is(':checked')) {
        checkboxes.prop('checked', true);
      } else {
        checkboxes.prop('checked', false);
      }
    });
    // uncheck all checkboxes when loading from
    cj(document).ready(function(){
      cj('input[type=checkbox]').prop("checked", false);
    });
  </script>
{/literal}
{/if}

