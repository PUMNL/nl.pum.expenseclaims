{strip}
  <div class="crm-content-block crm-form-block">
    <div class="crm-accordion-wrapper crm-select_filters_search-accordion">
      <div class="crm-accordion-header crm-master-accordion-header">
        {ts}Search Criteria for Approved Claims not in Batch{/ts}
      </div>
      <div class="crm-accordion-body">
        <div id="help">
          {ts}This section allows you to enter criteria for the selection of approved claims that are not yet in the batch.{/ts}
        </div>
        <div class="crm-section claim_date_from">
          <div class="label">{ts}Date from{/ts}:</div>
          <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=claim_date_from}</div>
          <div class="clear"></div>
        </div>
        <div class="crm-section claim_date_to">
          <div class="label">{ts}Date to{/ts}:</div>
          <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=claim_date_to}</div>
          <div class="clear"></div>
        </div>
        <div class="crm-section claim_types">
          <div class="label">{ts}Type(s){/ts}:</div>
          <div class="content crm-select-container" id="valid_types_block">
            <select id="claim_types" class="crm-select2 required" multiple="multiple" name="claim_types"></select>
          </div>
          <div class="clear"></div>
        </div>
      </div>
    </div>
  </div>
{/strip}
{literal}
  <script type="text/javascript">
    // add options to select for claim type
    cj(document).ready(function() {
      var selectClaimTypes = document.getElementById('claim_type');

      select = document.getElementById('selectElementId');
      for (var i = min; i<=max; i++){
        var opt = document.createElement('option');
        opt.value = i;
        opt.innerHTML = i;
        select.appendChild(opt);
      }

    };
    // core accordion
    cj(function() {
      cj().crmAccordions();
    });
  </script>
{/literal}
