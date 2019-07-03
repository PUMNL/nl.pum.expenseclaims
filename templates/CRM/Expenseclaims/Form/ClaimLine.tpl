{* HEADER *}
<h3>Edit Claim Line</h3>

<div class="crm-block crm-form-block">
  <div class="crm-section pum_claim_line_expense_date">
    <div class="label">{$form.expense_date.label}</div>
    <div class="content">{include file="CRM/common/jcalendar.tpl" elementName=expense_date}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_line_description">
    <div class="label">{$form.description.label}</div>
    <div class="content">{$form.description.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_line_type">
    <div class="label">{$form.expense_type.label}</div>
    <div class="content">{$form.expense_type.html}</div>
    <div class="clear"></div>
  </div>
  {literal}
    <script type="text/javascript">
      cj('#ClaimLine #expense_type')
        .change(function() {
          if(cj('#ClaimLine #expense_type').val() == 9) {
            cj('#ClaimLine .pum_claim_line_distance_km').show();
          } else {
            cj('#ClaimLine .pum_claim_line_distance_km').hide();
          }
        })
        .ready(function() {
          if(cj('#ClaimLine #expense_type').val() == 9) {
            cj('#ClaimLine .pum_claim_line_distance_km').show();
          } else {
            cj('#ClaimLine .pum_claim_line_distance_km').hide();
          }
        });
    </script>
  {/literal}
  <div class="crm-section pum_claim_line_distance_km">
    <div class="label">{$form.distance_km.label}</div>
    <div class="content">{$form.distance_km.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_line_cost_center">
    <div class="label">{$form.cost_center.label}</div>
    <div class="content">{$form.cost_center.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_line_currency">
    <div class="label">{$form.currency_id.label}</div>
    <div class="content">{$form.currency_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_line_currency_amount">
    <div class="label">{$form.currency_amount.label}</div>
    <div class="content">{$form.currency_amount.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_line_euro_amount">
    <div class="label">{$form.euro_amount.label}</div>
    <div class="content">{$form.euro_amount.value|crmMoney}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section pum_claim_line_reason_for_change">
    <div class="label">{$form.reason_for_change.label}</div>
    <div class="content">{$form.reason_for_change.html}</div>
    <div class="clear"></div>
  </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
