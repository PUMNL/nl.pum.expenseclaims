{literal}
  <script type="text/javascript">
    cj(document).ready(function() {
      cj('.crm-activity-form-block-activity_date_time td').each(function() {
        cj(this).children().prop("disabled", true);
      })
      cj('.crm-activity-form-block-activity_date_time span').each(function() {
        cj(this).children().prop("disabled", true);
      })
    });
  </script>
{/literal}