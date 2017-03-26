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
 *     	\file       htdocs/public/lyra/error.php
 *		\ingroup    lyra
 */

define("NOLOGIN",1);		// This means this output page does not require to be logged.
define("NOCSRFCHECK",1);	// We accept to go on this page from external web site.

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res && file_exists("../../../../main.inc.php")) $res=@include("../../../../main.inc.php");

dol_include_once("/lyra/lib/lyra.lib.php");

// Security check
if (empty($conf->lyra->enabled))
    accessforbidden('',1,1,1);


$langs->load("main");
$langs->load("other");
$langs->load("dict");
$langs->load("lyra@lyra");

// Define logo and logosmall
$urlLogo = '';
if (!empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small))
{
	$urlLogo = DOL_URL_ROOT.'/viewimage.php?modulepart=companylogo&amp;file='.urlencode('thumbs/'.$mysoc->logo_small);
}
elseif (! empty($mysoc->logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo))
{
	$urlLogo = DOL_URL_ROOT.'/viewimage.php?modulepart=companylogo&amp;file='.urlencode($mysoc->logo);
}

$substit = array(
    '__SOCNAM__' => $conf->global->MAIN_INFO_SOCIETE_NOM,
    '__SOCMAI__' => $conf->global->MAIN_INFO_SOCIETE_MAIL,
);

/*
 * View
 */
llxHeaderLyra($langs->trans("PaymentForm"));
print '<center>'."\n";
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
print '<tr class="liste_total"><td align="left" colspan="2">'.$langs->trans("ErrorOccurredDuringPayment").'</td></tr>'."\n";

print '<tr><td align="left" colspan="2">'.$langs->trans("ErrorOccurredDuringPaymentDesc").'</td></tr>'."\n";

print '</table>';

?>
