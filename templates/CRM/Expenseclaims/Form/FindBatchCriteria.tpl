{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{* Search criteria form elements - Find Experts *}

{* Set title for search criteria accordion *}
{capture assign=editTitle}{ts}Edit Search Criteria for Expense Claim Batch{/ts}{/capture}

{strip}
  <div class="crm-block crm-form-block crm-basic-criteria-form-block">
    <div class="crm-accordion-wrapper crm-case_search-accordion {if $rows}collapsed{/if}">
      <div class="crm-accordion-header crm-master-accordion-header">
        {$editTitle}
      </div><!-- /.crm-accordion-header -->
      <div class="crm-accordion-body">

        {if $form.batch_date_from or $form.batch_date_to}
          <div class="crm-section batch_date-from-section">
            <div class="label">
              <label for="batch_date-from">{$form.batch_date_from.label}</label>
            </div>
            <div class="content" id="batch_date-from">
              {include file="CRM/common/jcalendar.tpl" elementName='batch_date_from'}
            </div>
            <div class="clear"></div>
          </div>
          <div class="crm-section batch-date-to-section">
            <div class="label">
              <label for="batch-date-to">{$form.batch_date_to.label}</label>
            </div>
            <div class="content" id="batch-date-to">
              {include file="CRM/common/jcalendar.tpl" elementName='batch_date_to'}
            </div>
            <div class="clear"></div>
          </div>
        {/if}

        {if $form.claim_status}
          <div class="crm-section claim_status-section">
            <div class="label">
              <label for="claim_status-select">{ts}Claim Status(es){/ts}</label>
            </div>
            <div class="content" id="claim_status-select">
              {$form.claim_status.html}
              {literal}
                <script type="text/javascript">
                cj(function() {
                  cj("select#claim_status").crmasmSelect({
                    respectParents: true
                  });
                });
                </script>
              {/literal}
            </div>
            <div class="clear"></div>
          </div>
        {/if}

        {if $form.claim_type}
          <div class="crm-section claim_type-section">
            <div class="label">
              <label for="claim_type-select">{ts}Claim Types(s){/ts}</label>
            </div>
            <div class="content" id="claim_type-select">
              {$form.claim_type.html}
              {literal}
                <script type="text/javascript">
                cj(function() {
                  cj("select#claim_type").crmasmSelect({
                    respectParents: true
                  });
                });
                </script>
              {/literal}
            </div>
            <div class="clear"></div>
          </div>
        {/if}

        <div class="crm-submit-buttons">
          {include file="CRM/common/formButtons.tpl"}
          <a class="button new-option" href="{$addUrl}">
            <span><div class="icon add-icon"></div>New Claim Batch</span>
          </a>
        </div>


      </div><!-- /.crm-accordion-body -->
    </div><!-- /.crm-accordion-wrapper -->
  </div><!-- /.crm-form-block -->
{/strip}
{literal}
  <script type="text/javascript">
    cj(function() {
      cj().crmAccordions();
    });
  </script>
{/literal}


