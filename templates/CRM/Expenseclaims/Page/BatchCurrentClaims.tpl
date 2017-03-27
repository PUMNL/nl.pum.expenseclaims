{strip}
  <div class="crm-content-block crm-form-block">
    <div class="crm-accordion-wrapper crm-current_claims_search-accordion">
      <div class="crm-accordion-header crm-master-accordion-header">
        {ts}Claims Currently in Batch{/ts}
      </div>
      <div class="crm-accordion-body">
        <div id="help">
          {ts}This section shows the claims that are included in the batch.{/ts}
        </div>
        <table class="selector-current" summary="{ts}Search results listings.{/ts}">
          <thead class="sticky">
            <tr>
              <th scope="col">{ts}Claim ID{/ts}</th>
              <th scope="col">{ts}Description{/ts}</th>
              <th scope="col">{ts}Submitted By{/ts}</th>
              <th scope="col">{ts}Submitted Date{/ts}</th>
              <th scope="col">{ts}Link{/ts}</th>
              <th scope="col">{ts}Total Amount{/ts}</th>
              <th scope="col">{ts}Status{/ts}</th>
            </tr>
          </thead>

          {counter start=0 skip=1 print=false}
          {foreach from=$currentClaims item=currentClaim}
            <tr id='rowid{$currentClaim.claim_id}' class="{cycle values="odd-row,even-row"}">
              <td>{$currentClaim.claim_id}</td>
              <td>{$currentClaim.claim_description}</td>
              <td>{$currentClaim.claim_submitted_by}</td>
              <td>{$currentClaim.claim_submitted_date|crmDate}</td>
              <td>{$currentClaim.claim_link}</td>
              <td>{$currentClaim.claim_total_amount}</td>
              <td>{$currentClaim.claim_status}</td>
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
  </script>
{/literal}
