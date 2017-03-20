<div class="crm-block crm-form-block">
  <h2>{ts}Batch Data{/ts}</h2>
  <div class="crm-section">
    <div class="label">{ts}Date From{/ts}:</div>
    <div class="content" id="batch_description">
      <input class="form-text huge" readonly="readonly" type="text" name="batch_description" title="batch description" value="{$batchDescription}">
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{ts}Created Date{/ts}:</div>
    <div class="content" id="batch_created_date">
      <input class="form-text" readonly="readonly" type="text" name="batch_created_date" title="batch created on date" value="{$batchCreatedDate|crmDate}">
    </div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{ts}Status{/ts}:</div>
    <div class="content" id="batch_status">
      <input class="form-text" readonly="readonly" type="text" name="batch_status" title="batch status" value="{$batchStatus}">
    </div>
    <div class="clear"></div>
  </div>
</div>
