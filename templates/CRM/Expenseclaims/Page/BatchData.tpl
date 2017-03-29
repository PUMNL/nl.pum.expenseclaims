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
          <div class="label">{$form.batch_description.label}</div>
          <div class="content" id="batch_description">
            {$form.batch_description.value}
          </div>
          <div class="clear"></div>
        </div>
        <div class="crm-section">
          <div class="label">{$form.batch_created_date.label}</div>
          <div class="content" id="batch_created_date">
            {$form.batch_created_date.value}
          </div>
          <div class="clear"></div>
        </div>
        <div class="crm-section">
          <div class="label">{$form.batch_status.label}</div>
          <div class="content" id="batch_status">
            {$form.batch_status.value}
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

