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
        {if $batchOpen}
        <div class="crm-submit-buttons">
          <a class="button new-option" href="#" id="remove_claims_form_batch">
            <span><div class="icon delete-icon"></div>Remove Selected from Batch</span>
          </a>
        </div>
        {/if}
        <table class="selector-current" summary="{ts}Search results listings.{/ts}">
          <thead class="sticky">
            <tr>
              {if $batchOpen}
              <th scope="col" title="Select All Rows">
                <input id="toggleSelectAllSelected" name="toggleSelectAllSelected" value="1" class="form-checkbox" type="checkbox"
                       title="select all claims" />
              </th>
              {/if}
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
              {if $batchOpen}
                <td>
                <input id="selectClaim_{$currentClaim.pcbe_id}" name="selectClaim" value="1"
                       class="form-checkbox-row unselect-claims-check" type="checkbox" title="select all claims">
                </td>
              {/if}

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
    cj('#remove_claims_form_batch').click(function() {
        var requests = new Array();
        cj('.form-checkbox-row').each(function () {
            if (cj(this).attr('name') == 'selectClaim') {
                // if checked is true, add claim to batch
                var checked = cj(this).is(":checked");

                if (checked == true) {
                    var pcbeId = cj(this).attr('id').substr(12);
                    requests.push(['ClaimBatchEntity', 'delete', {'id':pcbeId}]);
                }
            }
        });

        CRM.api3(requests)
          .done(function (result) {
            CRM.alert('Successfully removed claims from batch','Remove claims from batch','success');
            window.location.reload();
          })
          .fail(function (result) {
            CRM.alert('Failed to remove claims from batch','Remove claims from batch','error');
            window.location.reload();
          });
    });

    cj('#toggleSelectAllSelected').change(function() {
        var checkboxes = cj(this).closest('form').find('.unselect-claims-check');
        if(cj(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });
</script>
{/literal}

