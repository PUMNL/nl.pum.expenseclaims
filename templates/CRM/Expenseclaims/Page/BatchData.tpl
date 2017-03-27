{strip}
  <div class="crm-content-block crm-form-block">
    <div class="crm-accordion-wrapper crm-batch_data_search-accordion">
      <div class="crm-accordion-header crm-master-accordion-header">
        {ts}Batch Data{/ts}
      </div>
      <div class="crm-accordion-body">
        <div id="help">
          {ts}This section shows the details of the batch you are working with.{/ts}
        </div>
        <div class="crm-section">
          <div class="label">{ts}Date From{/ts}:</div>
          <div class="content" id="batch_description">
            <input class="form-text huge" readonly="readonly" type="text" name="batch_description" title="batch description" value="{$batchDescription}" />
          </div>
          <div class="clear"></div>
        </div>
        <div class="crm-section">
          <div class="label">{ts}Created Date{/ts}:</div>
          <div class="content" id="batch_created_date">
            <input class="form-text" readonly="readonly" type="text" name="batch_created_date" title="batch created on date" value="{$batchCreatedDate|crmDate}"/>
          </div>
          <div class="clear"></div>
        </div>
        <div class="crm-section">
          <div class="label">{ts}Status{/ts}:</div>
          <div class="content" id="batch_status">
            <input class="form-text" readonly="readonly" type="text" name="batch_status" title="batch status" value="{$batchStatus}"/>
          </div>
          <div class="clear"></div>
        </div>
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

