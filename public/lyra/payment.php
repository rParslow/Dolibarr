<?php
/* Copyright (C) 2012      Mikael Carlavan        <mcarlavan@qis-network.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *     	\file       htdocs/public/lyra/payment.php
 *		\ingroup    lyra
 *		\brief      File to offer a payment form for an invoice
 */

define("NOLOGIN",1);		// This means this output page does not require to be logged.
define("NOCSRFCHECK",1);	// We accept to go on this page from external web site.

$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res && file_exists("../../../../main.inc.php")) $res=@include("../../../../main.inc.php");


require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/security.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions2.lib.php");
require_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
dol_include_once("/lyra/class/lyra.class.php");
dol_include_once("/lyra/lib/lyra.lib.php");

// Security check
if (empty($conf->lyra->enabled))
    accessforbidden('',1,1,1);

$langs->load("main");
$langs->load("other");
$langs->load("dict");
$langs->load("bills");
$langs->load("companies");
$langs->load("errors");

$langs->load("lyra@lyra");

$refDolibarr = GETPOST("ref", 'alpha');

// Complete urls for post treatment
$SOURCE=GETPOST("source",'alpha');
$ref=$REF=GETPOST('ref','alpha');
$TAG=GETPOST("tag",'alpha');
$FULLTAG=GETPOST("fulltag",'alpha');		// fulltag is tag with more informations
$SECUREKEY=GETPOST("securekey");	        // Secure key

$amount=price2num(GETPOST("amount"));
if (! GETPOST("currency",'alpha')) $currency=$conf->currency;
else $currency=GETPOST("currency",'alpha');

// Get parameters
$params = $conf->global;
$err = false;
$errMsg = '';

// Get societe info
$societyName = $mysoc->name;
$creditorName = $societyName;

if (! GETPOST("action"))
{
	if (! GETPOST("amount") && ! GETPOST("source"))
	{
		dol_print_error('',$langs->trans('ErrorBadParameters')." - amount or source");
		exit;
	}
	if (is_numeric($amount) && ! GETPOST("tag") && ! GETPOST("source"))
	{
		dol_print_error('',$langs->trans('ErrorBadParameters')." - tag or source");
		exit;
	}
	if (GETPOST("source") && ! GETPOST("ref"))
	{
		dol_print_error('',$langs->trans('ErrorBadParameters')." - ref");
		exit;
	}
}

// Check security token
$valid=true;
if (! empty($conf->global->LYRA_SECURITY_TOKEN))
{
	if (! empty($conf->global->LYRA_SECURITY_TOKEN_UNIQUE))
	{
		if ($SOURCE && $REF) $token = dol_hash($conf->global->LYRA_SECURITY_TOKEN . $SOURCE . $REF, 2);    // Use the source in the hash to avoid duplicates if the references are identical
		else $token = dol_hash($conf->global->LYRA_SECURITY_TOKEN, 2);
	}
	else
	{
		$token = $conf->global->LYRA_SECURITY_TOKEN;
	}
	if ($SECUREKEY != $token) $valid=false;

	if (! $valid)
	{
		print '<div class="error">Bad value for key.</div>';
		//print 'SECUREKEY='.$SECUREKEY.' token='.$token.' valid='.$valid;
		exit;
	}
}


// Check module configuration
if (empty($params->LYRA_SITE_ID))
{
    $err = true;
    dol_print_error($langs->trans('LYRA_SITE_ID_UNDEFINED'));
    dol_syslog('LYRA: Configuration error : TPE number is not defined');
}

if (empty($params->LYRA_KEY_TEST))
{
    $err = true;
    dol_print_error($langs->trans('LYRA_KEY_TEST_UNDEFINED'));
    dol_syslog('LYRA: Configuration error : Key test is not defined');
}

if (empty($params->LYRA_KEY_PROD))
{
    $err = true;
    dol_print_error($langs->trans('LYRA_KEY_TEST_UNDEFINED'));
    dol_syslog('LYRA: Configuration error : key is not defined');
}

if (empty($params->LYRA_PLATFORM_URL))
{
    $err = true;
    dol_print_error($langs->trans('LYRA_PLATFORM_URL_UNDEFINED'));
    dol_syslog('LYRA: Configuration error : platform url is not defined');
}


if ( GETPOST('source') && empty($refDolibarr))
{
    $err = true;
    $errMsg = $langs->trans('LYRA_REF_PARAM_UNDEFINED');
    dol_syslog('LYRA: Invoice reference has not been defined');
}



// Common variables
$creditor=$mysoc->name;
$paramcreditor='LYRA_CREDITOR_'.$suffix;
if (! empty($conf->global->$paramcreditor)) $creditor=$conf->global->$paramcreditor;
else if (! empty($conf->global->LYRA_CREDITOR)) $creditor=$conf->global->LYRA_CREDITOR;


if (GETPOST("action") == 'dopayment')
{
	$lyra_api = new Lyra();
	
	$shipToName=GETPOST("shipToName");
	$shipToStreet=GETPOST("shipToStreet");
	$shipToCity=GETPOST("shipToCity");
	$shipToState=GETPOST("shipToState");
	$shipToCountryCode=GETPOST("shipToCountryCode");
	$shipToZip=GETPOST("shipToZip");
	$shipToStreet2=GETPOST("shipToStreet2");
	$phoneNum=GETPOST("phoneNum");
	$email=GETPOST("email");
	$desc=GETPOST("desc");

	$currency = $lyra_api->findCurrencyByAlphaCode($currency);
	
	$newamount = str_replace(',','',GETPOST("newamount"));
	$newamount = str_replace(' ','',$newamount);
	// Lyra Args
	$misc_params = array(
			'amount' => str_replace(',','',$newamount),
			'contrib' => 'Dolibarr ERP/CRM '.DOL_VERSION,
			'currency' => $currency->num,
			'order_id' => (! empty($refDolibarr) ? $refDolibarr : $FULLTAG),
			'order_info' => $FULLTAG,
			// billing address info
			'cust_id' => '',
			'cust_email' => $email,
	
			//'cust_first_name' => $adrfact->prenom,
			'cust_last_name' => $shipToName,
			'cust_address' => $shipToStreet,
			'cust_zip' => $shipToZip,
			'cust_country' => $shipToCountryCode, 
			'cust_phone' => $phoneNum,
			'cust_city' => $shipToCity,
	
			// shipping address info
			'ship_to_first_name' => '',
			'ship_to_last_name' => $shipToName,
			'ship_to_street' => $shipToStreet,
			'ship_to_street2' => $shipToStreet2,
			'ship_to_city' => $shipToCity,
			'ship_to_country' => $shipToCountryCode, 
			'ship_to_zip' => $shipToZip,
			'ship_to_phone_num' => $phoneNum,
	);

	$lyra_api->setFromArray($misc_params);
	
	if($lang && $lang->code && in_array(strtolower($lang->code), $lyra_api->getSupportedLanguages())) {
		$lyra_api->set('language', strtolower($lang->code));
	} else {
		$lyra_api->set('language', $params->LYRA_LANGUAGE);
		#$lyra_api->set('language', 'fr');

	}
	
	$config_keys = array(
			'site_id' => $params->LYRA_SITE_ID, 
			'key_test' => $params->LYRA_KEY_TEST, 
			'key_prod' => $params->LYRA_KEY_PROD, 
			'ctx_mode' => $params->LYRA_CTX_MODE, 
			'platform_url' => $params->LYRA_PLATFORM_URL, 
			'available_languages' => $params->LYRA_AVAILABLE_LANGUAGES, 
			'capture_delay' => $params->LYRA_CAPTURE_DELAY,
			'validation_mode' => $params->LYRA_VALIDATION_MODE, 
			'payment_cards' => $params->LYRA_PAYMENT_CARDS, 
			'redirect_enabled' => $params->LYRA_REDIRECT_ENABLED, 
			'redirect_success_timeout' => $params->LYRA_REDIRECT_SUCCESS_TIMEOUT, 
			'redirect_success_message' => '',
			'redirect_error_timeout' => '10',
			'redirect_error_message' => '', 
			'return_mode' => $params->LYRA_RETURN_MODE, 
			'url_return' => $params->LYRA_URL_RETURN,
			'payment_cards' => $params->LYRA_PAYMENT_CARDS,
	);
	$lyra_api->setFromArray($config_keys);
	
	print '<body onload="document.forms[0].submit()">';
		
	print $lyra_api->getRequestHtmlForm('name="lyra_form"');
	print '<script type="text/javascript">document.forms[0].style.display=\'none\';</script>';
	print '</body>';
	exit();
}

/*
 * View
*/

llxHeaderLyra($langs->trans("PaymentForm"));

if (! empty($conf->global->LYRA_CTX_MODE) && $conf->global->LYRA_CTX_MODE == "TEST")
{
	setEventMessage($langs->trans('YouAreCurrentlyInSandboxMode'),'warnings');
}

print '<span id="dolpaymentspan"></span>'."\n";
print '<center>'."\n";
print '<form id="dolpaymentform" name="paymentform" action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n";
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n";
print '<input type="hidden" name="action" value="dopayment">'."\n";
print '<input type="hidden" name="tag" value="'.GETPOST("tag",'alpha').'">'."\n";
print '<input type="hidden" name="suffix" value="'.GETPOST("suffix",'alpha').'">'."\n";
print '<input type="hidden" name="securekey" value="'.$SECUREKEY.'">'."\n";
print '<input type="hidden" name="entity" value="'.$entity.'" />';
print "\n";
print '<!-- Form to send a Lyra payment -->'."\n";
print '<!-- urlok = '.$urlok.' -->'."\n";
print '<!-- urlko = '.$urlko.' -->'."\n";
print "\n";


print '<table id="dolpaymenttable" summary="Payment form">'."\n";

// Show logo (search order: logo defined by PAYBOX_LOGO_suffix, then PAYBOX_LOGO, then small company logo, large company logo, theme logo, common logo)
$width=0;

// Print logo
$urllogo=DOL_URL_ROOT.'/theme/login_logo.png';

if (! empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small))
{
	$urllogo=DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=companylogo&amp;file='.urlencode('thumbs/'.$mysoc->logo_small);
}
elseif (! empty($mysoc->logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo))
{
	$urllogo=DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=companylogo&amp;file='.urlencode($mysoc->logo);
	$width=128;
}
elseif (is_readable(DOL_DOCUMENT_ROOT.'/theme/dolibarr_logo.png'))
{
	$urllogo=DOL_URL_ROOT.'/theme/dolibarr_logo.png';
}

// Output html code for logo
if ($urllogo)
{
	print '<tr>';
	print '<td align="center"><img id="dolpaymentlogo" title="'.$title.'" src="'.$urllogo.'"';
	if ($width) print ' width="'.$width.'"';
	print '></td>';
	print '</tr>'."\n";
}


// Output payment summary form
print '<tr><td align="center">';
print '<table with="100%" id="tablepublicpayment">';
print '<tr class="liste_total"><td align="left" colspan="2">'.$langs->trans("ThisIsInformationOnPayment").' :</td></tr>'."\n";


$found=false;
$error=0;
$var=false;

// Free payment
if (! GETPOST("source") && $valid)
{
	$found=true;
	$tag=GETPOST("tag");
	$fulltag=$tag;

	// Creditor
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Creditor");
	print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$creditor.'</b>';
	print '<input type="hidden" name="creditor" value="'.$creditor.'">';
	print '</td></tr>'."\n";

	// Amount
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Amount");
	if (empty($amount)) print ' ('.$langs->trans("ToComplete").')';
	print '</td><td class="CTableRow'.($var?'1':'2').'">';
	if (empty($amount) || ! is_numeric($amount))
	{
		print '<input type="hidden" name="amount" value="'.GETPOST("amount",'int').'">';
		print '<input class="flat" size=8 type="text" name="newamount" value="'.GETPOST("newamount","int").'">';
	}
	else {
		print '<b>'.price($amount).'</b>';
		print '<input type="hidden" name="amount" value="'.$amount.'">';
		print '<input type="hidden" name="newamount" value="'.price($amount).'">';
	}
	// Currency
	print ' <b>'.$langs->trans("Currency".$currency).'</b>';
	print '<input type="hidden" name="currency" value="'.$currency.'">';
	print '</td></tr>'."\n";

	// Tag
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("PaymentCode");
	print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$fulltag.'</b>';
	print '<input type="hidden" name="tag" value="'.$tag.'">';
	print '<input type="hidden" name="fulltag" value="'.$fulltag.'">';
	print '</td></tr>'."\n";

	// We do not add fields shipToName, shipToStreet, shipToCity, shipToState, shipToCountryCode, shipToZip, shipToStreet2, phoneNum
	// as they don't exists (buyer is unknown, tag is free).
}

/*
 *  Payment on customer order 
 */
if (GETPOST("source") == 'order' && $valid)
{
	$found=true;
	$langs->load("orders");

	require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';

	$order=new Commande($db);
	$result=$order->fetch('',$ref);
	if ($result < 0)
	{
		$mesg=$order->error;
		$error++;
	}
	else
	{
		$result=$order->fetch_thirdparty($order->socid);
	}

	$amount=$order->total_ttc;
	if (GETPOST("amount",'int')) $amount=GETPOST("amount",'int');
	$amount=price2num($amount);

	$fulltag='ORD='.$order->ref.'.CUS='.$order->thirdparty->id;
	//$fulltag.='.NAM='.strtr($order->thirdparty->name,"-"," ");
	if (! empty($TAG)) { $tag=$TAG; $fulltag.='.TAG='.$TAG; }
	$fulltag=dol_string_unaccent($fulltag);

	// Creditor
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Creditor");
	print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$creditor.'</b>';
	print '<input type="hidden" name="creditor" value="'.$creditor.'">';
	print '</td></tr>'."\n";

	// Debitor
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("ThirdParty");
	print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$order->thirdparty->name.'</b>';

	// Object
	$var=!$var;
	$text='<b>'.$langs->trans("PaymentOrderRef",$order->ref).'</b>';
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Designation");
	print '</td><td class="CTableRow'.($var?'1':'2').'">'.$text;
	print '<input type="hidden" name="source" value="'.GETPOST("source",'alpha').'">';
	print '<input type="hidden" name="ref" value="'.$order->ref.'">';
	print '</td></tr>'."\n";

	// Amount
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Amount");
	if (empty($amount)) print ' ('.$langs->trans("ToComplete").')';
	print '</td><td class="CTableRow'.($var?'1':'2').'">';
	if (empty($amount) || ! is_numeric($amount))
	{
		print '<input type="hidden" name="amount" value="'.GETPOST("amount",'int').'">';
		print '<input class="flat" size=8 type="text" name="newamount" value="'.price(GETPOST("newamount","int")).'">';
	}
	else {
		print '<b>'.price($amount).'</b>';
		print '<input type="hidden" name="amount" value="'.$amount.'">';
		print '<input type="hidden" name="newamount" value="'.price($amount).'">';
	}
	// Currency
	print ' <b>'.$langs->trans("Currency".$currency).'</b>';
	print '<input type="hidden" name="currency" value="'.$currency.'">';
	print '</td></tr>'."\n";

	// Tag
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("PaymentCode");
	print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$fulltag.'</b>';
	print '<input type="hidden" name="tag" value="'.$tag.'">';
	print '<input type="hidden" name="fulltag" value="'.$fulltag.'">';
	print '</td></tr>'."\n";

	// Shipping address
	$shipToName=$order->thirdparty->name;
	$shipToStreet=$order->thirdparty->address;
	$shipToCity=$order->thirdparty->town;
	$shipToState=$order->thirdparty->state_code;
	$shipToCountryCode=$order->thirdparty->country_code;
	$shipToZip=$order->thirdparty->zip;
	$shipToStreet2='';
	$phoneNum=$order->thirdparty->phone;
	if ($shipToName && $shipToStreet && $shipToCity && $shipToCountryCode && $shipToZip)
	{
		print '<input type="hidden" name="shipToName" value="'.$shipToName.'">'."\n";
		print '<input type="hidden" name="shipToStreet" value="'.$shipToStreet.'">'."\n";
		print '<input type="hidden" name="shipToCity" value="'.$shipToCity.'">'."\n";
		print '<input type="hidden" name="shipToState" value="'.$shipToState.'">'."\n";
		print '<input type="hidden" name="shipToCountryCode" value="'.$shipToCountryCode.'">'."\n";
		print '<input type="hidden" name="shipToZip" value="'.$shipToZip.'">'."\n";
		print '<input type="hidden" name="shipToStreet2" value="'.$shipToStreet2.'">'."\n";
		print '<input type="hidden" name="phoneNum" value="'.$phoneNum.'">'."\n";
	}
	else
	{
		print '<!-- Shipping address not complete, so we don t use it -->'."\n";
	}
	print '<input type="hidden" name="email" value="'.$order->thirdparty->email.'">'."\n";
	print '<input type="hidden" name="desc" value="'.$langs->trans("Order").' '.$order->ref.'">'."\n";
}

/*
 *  Payment on customer invoice
 */
if (GETPOST("source") == 'invoice' && $valid)
{
	$found=true;
	$langs->load("invoices");

	require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';

	$facture=new Facture($db);
	$result=$facture->fetch('',$ref);
	if ($result < 0)
	{
		$mesg=$facture->error;
		$error++;
	}
	else
	{
		$result=$facture->fetch_thirdparty($facture->socid);
	}

	$amount=$facture->total_ttc;
	if (GETPOST("amount",'int')) $amount=GETPOST("amount",'int');
	$amount=price2num($amount);

	$fulltag='INV='.$facture->ref.'.CUS='.$facture->thirdparty->id;
	//$fulltag.='.NAM='.strtr($facture->thirdparty->name,"-"," ");
	if (! empty($TAG)) { $tag=$TAG; $fulltag.='.TAG='.$TAG; }
	$fulltag=dol_string_unaccent($fulltag);

	// Creditor
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Creditor");
	print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$creditor.'</b>';
	print '<input type="hidden" name="creditor" value="'.$creditor.'">';
	print '</td></tr>'."\n";

	// Debitor
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("ThirdParty");
	print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$facture->thirdparty->name.'</b>';

	// Object
	$var=!$var;
	$text='<b>'.$langs->trans("PaymentInvoiceRef",$facture->ref).'</b>';
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Designation");
	print '</td><td class="CTableRow'.($var?'1':'2').'">'.$text;
	print '<input type="hidden" name="source" value="'.GETPOST("source",'alpha').'">';
	print '<input type="hidden" name="ref" value="'.$facture->ref.'">';
	print '</td></tr>'."\n";

	// Amount
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("Amount");
	if (empty($amount)) print ' ('.$langs->trans("ToComplete").')';
	print '</td><td class="CTableRow'.($var?'1':'2').'">';
	if (empty($amount) || ! is_numeric($amount))
	{
		print '<input type="hidden" name="amount" value="'.GETPOST("amount",'int').'">';
		print '<input class="flat" size=8 type="text" name="newamount" value="'.price(GETPOST("newamount","int")).'">';
	}
	else {
		print '<b>'.price($amount).'</b>';
		print '<input type="hidden" name="amount" value="'.$amount.'">';
		print '<input type="hidden" name="newamount" value="'.price($amount).'">';
	}
	// Currency
	print ' <b>'.$langs->trans("Currency".$currency).'</b>';
	print '<input type="hidden" name="currency" value="'.$currency.'">';
	print '</td></tr>'."\n";

	// Tag
	$var=!$var;
	print '<tr class="CTableRow'.($var?'1':'2').'"><td class="CTableRow'.($var?'1':'2').'">'.$langs->trans("PaymentCode");
	print '</td><td class="CTableRow'.($var?'1':'2').'"><b>'.$fulltag.'</b>';
	print '<input type="hidden" name="tag" value="'.$tag.'">';
	print '<input type="hidden" name="fulltag" value="'.$fulltag.'">';
	print '</td></tr>'."\n";

	// Shipping address
	$shipToName=$facture->thirdparty->name;
	$shipToStreet=$facture->thirdparty->address;
	$shipToCity=$facture->thirdparty->town;
	$shipToState=$facture->thirdparty->state_code;
	$shipToCountryCode=$facture->thirdparty->country_code;
	$shipToZip=$facture->thirdparty->zip;
	$shipToStreet2='';
	$phoneNum=$facture->thirdparty->phone;
	if ($shipToName && $shipToStreet && $shipToCity && $shipToCountryCode && $shipToZip)
	{
		print '<input type="hidden" name="shipToName" value="'.$shipToName.'">'."\n";
		print '<input type="hidden" name="shipToStreet" value="'.$shipToStreet.'">'."\n";
		print '<input type="hidden" name="shipToCity" value="'.$shipToCity.'">'."\n";
		print '<input type="hidden" name="shipToState" value="'.$shipToState.'">'."\n";
		print '<input type="hidden" name="shipToCountryCode" value="'.$shipToCountryCode.'">'."\n";
		print '<input type="hidden" name="shipToZip" value="'.$shipToZip.'">'."\n";
		print '<input type="hidden" name="shipToStreet2" value="'.$shipToStreet2.'">'."\n";
		print '<input type="hidden" name="phoneNum" value="'.$phoneNum.'">'."\n";
	}
	else
	{
		print '<!-- Shipping address not complete, so we don t use it -->'."\n";
	}
	print '<input type="hidden" name="email" value="'.$facture->thirdparty->email.'">'."\n";
	print '<input type="hidden" name="desc" value="'.$langs->trans("Order").' '.$facture->ref.'">'."\n";
}

print '</table>';
print '</td></tr>';

print '<tr><td align="center">'."\n";
print '<br><input class="button" type="submit" name="dopayment" value="'.$langs->trans("LyraDoPayment").'">';

print '</td></tr>'."\n";

print '</table>'."\n";
print "</form>\n";
print "\n";

$db->close();

// Global html output events ($mesgs, $errors, $warnings)
dol_htmloutput_events();

?>
