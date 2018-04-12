<div class="crm-content-block crm-block">
    <div id="help">
        {ts}Allows you to administer the approval process of claims {/ts}
    </div>
    <div id="my_claims-wrapper" class="dataTables_wrapper">
        <table id="my_claims-table" class="display">
        <thead>
        <tr>
            <th>{ts}Contact Id{/ts}</th>
            <th>{ts}Name{/ts}</th>
            <th id="nosort"></th>
        </tr>
        </thead>
        <tbody>
        {assign var="rowClass" value="odd-row"}

        {foreach from=$administerClaims key=personId item=otherPerson}
            <tr class="{$rowClass}">
                <td>{$otherPerson.contact_id}</td>
                <td>{$otherPerson.display_name}</td>
                <td>{$otherPerson.action}</td>
            </tr>
            {if $rowClass eq "odd-row"}
                {assign var="rowClass" value="even-row"}
            {else}
                {assign var="rowClass" value="odd-row"}
            {/if}
        {/foreach}
        </tbody>
        </table>
    </div>
</div>