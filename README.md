# nl.pum.expenseclaims

CiviCRM native extension for PUM Senior Experts dealing with all expense claim processing.

## API For currency conversion:

Use the _currency.convert_ api to convert an amount in a source currency to an amount in EURO.

This api makes use of the http://apilayer.net webservices. 
Before using this make sure you have added the access key to the **civicrm.settings.php** file:

    global $apilayer_settings;
    $apilayer_settings['access_key'] = 'your access key';
    

