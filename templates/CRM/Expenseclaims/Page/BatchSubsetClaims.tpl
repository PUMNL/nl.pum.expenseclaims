{strip}
  <div class="crm-content-block crm-form-block">
    <div class="crm-accordion-wrapper crm-select_filters_search-accordion">
      <div class="crm-accordion-header crm-master-accordion-header">
        {ts}Approved Claims not in Batch{/ts}
      </div>
      <div class="crm-accordion-body">
        <div id="help">
          {ts}This section shows all approved claims that are not yet in the batch. You can add one or more claims to the batch
          by selecting them and clicking the Add To Batch button{/ts}
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
                   class="form-checkbox-row" type="checkbox" title="select all claims">
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
                  <a href="{crmURL p='civicrm/pumexpenseclaims/form/claim' q="action=update&id=`$subsetClaim.claim_id`"}"
                  class="action-item action-item-first" title="lines">{ts}Lines{/ts}</a>
                </span>
              </td>
            </tr>
          {/foreach}
        </table>
      </div>
    </div>
  </div>
{/strip}
{literal}
  <script type="text/javascript">
    cj(function() {
      cj().crmAccordions();
    });
    cj('#add_claims_to_batch').click(function() {
      cj('.form-checkbox-row').each(function() {
        if (cj(this).attr('name') === 'selectClaim') {
          var claimId = cj(this).attr('id').substr(12);
          var message = 'Id is ' + cj(this).attr('id') + ' waarde is ' + cj(this).is(":checked") + ' met claim ' + claimId;
          CRM.alert(message);
        };
      });
    });
  </script>
{/literal}
