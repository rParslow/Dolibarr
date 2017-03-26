
<?php
/* Copyright (C) 2014      Jean-François FERRY	<jfefe@aternatik.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		admin/lyra.php
 * 	\ingroup	lyra
 * 	\brief		configuration page for Lyra Payzen Systempay Sogecommerce module
 */
// Dolibarr environment
$res = @include "../../main.inc.php"; // From htdocs directory
if (! $res) {
	$res = @include "../../../main.inc.php"; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/lyra.lib.php';



require_once "../class/lyra.class.php";

// Translations
$langs->load("admin");
$langs->load("lyra@lyra");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */

if ($action == 'setvalue' && $user->admin)
{
	
	
	$db->begin();
	$result=dolibarr_set_const($db, "LYRA_CTX_MODE",GETPOST('LYRA_CTX_MODE','alpha'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_SITE_ID",GETPOST('LYRA_SITE_ID','alpha'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_KEY_TEST",GETPOST('LYRA_KEY_TEST','alpha'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_KEY_PROD",GETPOST('LYRA_KEY_PROD','alpha'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_PLATFORM_URL",GETPOST('LYRA_PLATFORM_URL','alpha'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_LANGUAGE",GETPOST('LYRA_LANGUAGE','alpha'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$available_langages = GETPOST('LYRA_AVAILABLE_LANGUAGES');
	if(is_array($available_langages))
		$available_langages = implode(";",$available_langages);
	$result=dolibarr_set_const($db, "LYRA_AVAILABLE_LANGUAGES",$available_langages,'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_CAPTURE_DELAY",GETPOST('LYRA_CAPTURE_DELAY','alpha'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_VALIDATION_MODE",GETPOST('LYRA_VALIDATION_MODE','alpha'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$available_payment_cards = GETPOST('LYRA_PAYMENT_CARDS');
	if(is_array($available_payment_cards))
		$available_payment_cards = implode(";",$available_payment_cards);
	$result=dolibarr_set_const($db, "LYRA_PAYMENT_CARDS",$available_payment_cards,'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_AMOUNT_MIN",GETPOST('LYRA_AMOUNT_MIN'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_REDIRECT_ENABLED",GETPOST('LYRA_REDIRECT_ENABLED'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_REDIRECT_URL",GETPOST('LYRA_REDIRECT_URL'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_REDIRECT_SUCCESS_TIMEOUT",GETPOST('LYRA_REDIRECT_SUCCESS_TIMEOUT'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_PAYONLINE_SENDEMAIL",GETPOST('LYRA_PAYONLINE_SENDEMAIL'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_RETURN_MODE",GETPOST('LYRA_RETURN_MODE'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_URL_RETURN",GETPOST('LYRA_URL_RETURN'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_URL_CHECK",GETPOST('LYRA_URL_CHECK'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_SECURITY_TOKEN",GETPOST('LYRA_SECURITY_TOKEN'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_SECURITY_TOKEN_UNIQUE",GETPOST('LYRA_SECURITY_TOKEN_UNIQUE'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	//$result=dolibarr_set_const($db, "LYRA_UPDATE_INVOICE_STATUT",GETPOST('LYRA_UPDATE_INVOICE_STATUT'),'chaine',0,'',$conf->entity);
	//if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_BANK_ACCOUNT_ID",GETPOST('LYRA_BANK_ACCOUNT_ID'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	$result=dolibarr_set_const($db, "LYRA_BANK_USER_LOGIN",GETPOST('LYRA_BANK_USER_LOGIN'),'chaine',0,'',$conf->entity);
	if (! $result > 0) $error++;
	
	if (! $error)
	{
		$db->commit();
		setEventMessage($langs->trans("SetupSaved"));
	}
	else
	{
		$db->rollback();
		dol_print_error($db);
	}
}


/*
 * View
 */

$form=new Form($db);


$page_name = "LyraSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = llxHeaderLyraadmin_prepare_head();
dol_fiche_head(
	$head,
	'lyraaccount'
	
);

// About page goes here
echo $langs->trans("LyraSetupPageInfo");

echo '<br />';


print '<br>';
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvalue">';


print '<table class="nobordernopadding" width="100%">';

$var=true;
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("AccountParameters").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print "</tr>\n";

$var=!$var;
$vars_mode = array('TEST' => 'test', 'PRODUCTION' => "Production"); 
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_CTX_MODE").'</td><td>';
print $form->selectarray("LYRA_CTX_MODE",$vars_mode,$conf->global->LYRA_CTX_MODE,0);
print '</td></tr>';


$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_SITE_ID").'</td><td>';
print '<input size="32" type="text" name="LYRA_SITE_ID" value="'.$conf->global->LYRA_SITE_ID.'">';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_KEY_TEST").'</td><td>';
print '<input size="32" type="text" name="LYRA_KEY_TEST" value="'.$conf->global->LYRA_KEY_TEST.'">';
print '<br>'.$langs->trans("Example").': 1111111111111111';
print '</td></tr>';


$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_KEY_PROD").'</td><td>';
print '<input size="32" type="text" name="LYRA_KEY_PROD" value="'.$conf->global->LYRA_KEY_PROD.'">';
print '<br>'.$langs->trans("Example").': 2222222222222222';
print '</td></tr>';


$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_PLATFORM_URL").'</td><td>';
print '<input size="32" type="text" name="LYRA_PLATFORM_URL" value="'.$conf->global->LYRA_PLATFORM_URL.'">';
print '</td></tr>';


$var=!$var;
$langages = array(
	'fr' => 'Fran&ccedil;ais',
	'de' => 'Allemand',
	'en' => 'Anglais',
	'es' => 'Espagnol',
	'zh' => 'Chinois',
	'it' => 'Italien',
	'ja' => 'Japonnais',
	'pt' => 'Portugais',
	'nl' => 'N&eacute;erlandais'						
);
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_LANGUAGE").'</td><td>';
print $form->selectarray("LYRA_LANGUAGE",$langages,$conf->global->LYRA_LANGUAGE,0);
print '</td></tr>';


$var=!$var;
$available_langages = array(
	'' => 'Toutes',
	'fr' => 'Fran&ccedil;ais',
	'de' => 'Allemand',
	'en' => 'Anglais',
	'es' => 'Espagnol',
	'zh' => 'Chinois',
	'it' => 'Italien',
	'ja' => 'Japonnais',
	'pt' => 'Portugais',
	'nl' => 'N&eacute;erlandais'
);

$conf_available_langages_array = explode(";",$conf->global->LYRA_AVAILABLE_LANGUAGES);


print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_AVAILABLE_LANGUAGES").'</td><td>';
print $form->multiselectarray("LYRA_AVAILABLE_LANGUAGES",$available_langages,$conf_available_langages_array,1);
print '</td></tr>';


$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_CAPTURE_DELAY").'</td><td>';
print '<input size="32" type="text" name="LYRA_CAPTURE_DELAY" value="'.$conf->global->LYRA_CAPTURE_DELAY.'">';
print '</td></tr>';

$var=!$var;
$validation_modes = array(
	'' => 'Par d&eacute;faut',
	'0' => 'Automatique',
	'1' => 'Manuel'
);
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_VALIDATION_MODE").'</td><td>';
print $form->selectarray("LYRA_VALIDATION_MODE",$validation_modes,$conf->global->LYRA_VALIDATION_MODE,0);
print '</td></tr>';


$var=!$var;
$cbs = array(
	'' => 'Toutes',
	'AMEX' => 'American express',
	'CB' => 'CB',
	'MASTERCARD' => 'Mastercard',
	'VISA' => 'Visa'
);
$conf_available_cards_array = explode(";",$conf->global->LYRA_PAYMENT_CARDS);
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_PAYMENT_CARDS").'</td><td>';
print $form->multiselectarray("LYRA_PAYMENT_CARDS",$cbs,$conf_available_cards_array,0);
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_AMOUNT_MIN").'</td><td>';
print '<input size="32" type="text" name="LYRA_AMOUNT_MIN" value="'.$conf->global->LYRA_AMOUNT_MIN.'">';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_AMOUNT_MAX").'</td><td>';
print '<input size="32" type="text" name="LYRA_AMOUNT_MAX" value="'.$conf->global->LYRA_AMOUNT_MAX.'">';
print '</td></tr>';

$var=!$var;
$return_modes = array(
	'GET' => 'GET',
	'POST' => 'POST'
);
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_RETURN_MODE").'</td><td>';
print $form->selectarray("LYRA_RETURN_MODE",$return_modes,$conf->global->LYRA_RETURN_MODE,0);
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_URL_RETURN").'</td><td>';
print '<input size="32" type="text" name="LYRA_URL_RETURN" value="'.$conf->global->LYRA_URL_RETURN.'">';
print '<br>'.$langs->trans("Example").': '.dol_buildpath('/lyra/public/lyra/confirm.php',2);
print '</td></tr>';


$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_REDIRECT_ENABLED").'</td><td>';
print $form->selectyesno("LYRA_REDIRECT_ENABLED",$conf->global->LYRA_REDIRECT_ENABLED,1);
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_REDIRECT_URL").'</td><td>';
print '<input size="32" type="text" name="LYRA_REDIRECT_URL" value="'.$conf->global->LYRA_REDIRECT_URL.'">';
print '<br>'.$langs->trans("Example").': '.dol_buildpath('/lyra/public/lyra/success.php',2);

print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_REDIRECT_SUCCESS_TIMEOUT").'</td><td>';
print '<input size="32" type="text" name="LYRA_REDIRECT_SUCCESS_TIMEOUT" value="'.$conf->global->LYRA_REDIRECT_SUCCESS_TIMEOUT.'">';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td class="fieldrequired">';
print $langs->trans("LYRA_PAYONLINE_SENDEMAIL").'</td><td>';
print '<input size="32" type="text" name="LYRA_PAYONLINE_SENDEMAIL" value="'.$conf->global->LYRA_PAYONLINE_SENDEMAIL.'">';
print '</td></tr>';


$var=true;
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("UrlGenerationParameters").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print "</tr>\n";

$var=!$var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("SecurityToken").'</td><td>';
print '<input size="48" type="text" id="LYRA_SECURITY_TOKEN" name="LYRA_SECURITY_TOKEN" value="'.$conf->global->LYRA_SECURITY_TOKEN.'">';
if (! empty($conf->use_javascript_ajax))
	print '&nbsp;'.img_picto($langs->trans('Generate'), 'refresh', 'id="generate_token" class="linkobject"');
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("SecurityTokenIsUnique").'</td><td>';
print $form->selectyesno("LYRA_SECURITY_TOKEN_UNIQUE",(empty($conf->global->LYRA_SECURITY_TOKEN_UNIQUE)?0:$conf->global->LYRA_SECURITY_TOKEN_UNIQUE),1);
print '</td></tr>';



$var=true;
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("OthersParameters").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print "</tr>\n";

/*
$var=!$var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("LYRA_UPDATE_INVOICE_STATUT").'</td><td>';
print $form->selectyesno("LYRA_UPDATE_INVOICE_STATUT", $conf->global->LYRA_UPDATE_INVOICE_STATUT, 1);
print '</td></tr>';
*/

$var=!$var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("LYRA_BANK_ACCOUNT_ID").'</td><td>';
//print '<input size="48" type="text" id="LYRA_BANK_ACCOUNT_ID" name="LYRA_BANK_ACCOUNT_ID" value="'.$conf->global->LYRA_BANK_ACCOUNT_ID.'">';
print $form->select_comptes($conf->global->LYRA_BANK_ACCOUNT_ID, 'LYRA_BANK_ACCOUNT_ID', 0, '', 1);
print '</td></tr>';


$var=!$var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("LYRA_BANK_USER_LOGIN").'</td><td>';
print  $form->select_users($conf->global->LYRA_BANK_USER_LOGIN,'LYRA_BANK_USER_LOGIN');
print '</td></tr>';

print '<tr><td colspan="2" align="center"><br><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td></tr>';

print '</table>';

print '</form>';

// Url list
print '<u>'.$langs->trans("FollowingUrlAreAvailableToMakePayments").':</u><br>';
print img_picto('','object_globe.png').' '.$langs->trans("ToOfferALinkForOnlinePaymentOnFreeAmount",$servicename).':<br>';
print '<strong>'.getLyraPaymentUrl(1,'free')."</strong><br><br>\n";

print img_picto('','object_order.png').' '.$langs->trans("ToOfferALinkForOnlinePaymentOnOrder",$servicename).':<br>';
print '<strong>'.getLyraPaymentUrl(1,'order','PROV24')."</strong><br><br>\n";


// Page end
dol_fiche_end();


if (! empty($conf->use_javascript_ajax))
{
	print "\n".'<script type="text/javascript">';
	print '$(document).ready(function () {
           
            $("#generate_token").click(function() {
            	$.get( "'.DOL_URL_ROOT.'/core/ajax/security.php", {
            		action: \'getrandompassword\',
            		generic: true
				},
				function(token) {
					$("#LYRA_SECURITY_TOKEN").val(token);
				});
            });
    });';
	print '</script>';
}


llxFooter();
