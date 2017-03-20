<div class="crm-block crm-form-block">
  <h2>{ts}Claims Currently in Batch{/ts}</h2>
  <table class="selector" summary="{ts}Search results listings.{/ts}">
    <thead class="sticky">
      <th scope="col">{ts}Claim ID{/ts}</th>
      <th scope="col">{ts}Description{/ts}</th>
      <th scope="col">{ts}Submitted By{/ts}</th>
      <th scope="col">{ts}Submitted Date{/ts}</th>
      <th scope="col">{ts}Link{/ts}</th>
      <th scope="col">{ts}Total Amount{/ts}</th>
      <th scope="col">{ts}Status{/ts}</th>
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
