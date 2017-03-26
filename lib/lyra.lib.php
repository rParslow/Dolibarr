<?php
/* Copyright (C) 2014		2014      Jean-François FERRY	<jfefe@aternatik.fr>
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
 *	\file			htdocs/lyra/lib/lyra.lib.php
 *  \ingroup		lyra
 *  \brief			Library for common lyra functions
 */


/**
 * Show header
 *
 * @param 	string	$title		Title
 * @param 	string	$head		More header to add
 * @return	void
 */
function llxHeaderLyra($title, $head = "")
{
	global $user, $conf, $langs;

	header("Content-type: text/html; charset=".$conf->file->character_set_client);

	$appli='Dolibarr';
	if (!empty($conf->global->MAIN_APPLICATION_TITLE)) $appli=$conf->global->MAIN_APPLICATION_TITLE;

	print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
	//print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd>';
	print "\n";
	print "<html>\n";
	print "<head>\n";
	print '<meta name="robots" content="noindex,nofollow">'."\n";
	print '<meta name="keywords" content="dolibarr,payment,online">'."\n";
	print '<meta name="description" content="Welcome on '.$appli.' online payment form">'."\n";
	print "<title>".$title."</title>\n";
	if ($head) print $head."\n";
	if (! empty($conf->global->PAYPAL_CSS_URL)) print '<link rel="stylesheet" type="text/css" href="'.$conf->global->PAYPAL_CSS_URL.'?lang='.$langs->defaultlang.'">'."\n";
	else
	{
		print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.$conf->css.'?lang='.$langs->defaultlang.'">'."\n";
		print '<style type="text/css">';
		print '.CTableRow1      { margin: 1px; padding: 3px; font: 12px verdana,arial; background: #e6E6eE; color: #000000; -moz-border-radius-topleft:6px; -moz-border-radius-topright:6px; -moz-border-radius-bottomleft:6px; -moz-border-radius-bottomright:6px;}';
		print '.CTableRow2      { margin: 1px; padding: 3px; font: 12px verdana,arial; background: #FFFFFF; color: #000000; -moz-border-radius-topleft:6px; -moz-border-radius-topright:6px; -moz-border-radius-bottomleft:6px; -moz-border-radius-bottomright:6px;}';
		print '</style>';
	}

	if ($conf->use_javascript_ajax)
	{
		print '<!-- Includes for JQuery (Ajax library) -->'."\n";
		print '<link rel="stylesheet" type="text/css" href="'.DOL_URL_ROOT.'/includes/jquery/plugins/jnotify/jquery.jnotify-alt.min.css" />'."\n";          // JNotify

		// Output standard javascript links
		$ext='.js';

		// JQuery. Must be before other includes
		print '<!-- Includes JS for JQuery -->'."\n";
		print '<script type="text/javascript" src="'.DOL_URL_ROOT.'/includes/jquery/js/jquery-latest.min'.$ext.'"></script>'."\n";
		// jQuery jnotify
		if (empty($conf->global->MAIN_DISABLE_JQUERY_JNOTIFY))
		{
			print '<script type="text/javascript" src="'.DOL_URL_ROOT.'/includes/jquery/plugins/jnotify/jquery.jnotify.min'.$ext.'"></script>'."\n";
			print '<script type="text/javascript" src="'.DOL_URL_ROOT.'/core/js/jnotify'.$ext.'"></script>'."\n";
		}
	}
	print "</head>\n";
	print '<body style="margin: 20px;">'."\n";
}

/**
 * Show footer
 *
 * @return	void
 */
function llxFooterPaypal()
{
	print "</body>\n";
	print "</html>\n";
}


/**
 * Show footer of company in HTML pages
 *
 * @param   Societe		$fromcompany	Third party
 * @param   Translate	$langs			Output language
 * @return	void
 */
function html_print_lyra_footer($fromcompany,$langs)
{
	global $conf;

	// Juridical status
	$line1="";
	if ($fromcompany->forme_juridique_code)
	{
		$line1.=($line1?" - ":"").getFormeJuridiqueLabel($fromcompany->forme_juridique_code);
	}
	// Capital
	if ($fromcompany->capital)
	{
		$line1.=($line1?" - ":"").$langs->transnoentities("CapitalOf",$fromcompany->capital)." ".$langs->transnoentities("Currency".$conf->currency);
	}
	// Prof Id 1
	if ($fromcompany->idprof1 && ($fromcompany->country_code != 'FR' || ! $fromcompany->idprof2))
	{
		$field=$langs->transcountrynoentities("ProfId1",$fromcompany->country_code);
		if (preg_match('/\((.*)\)/i',$field,$reg)) $field=$reg[1];
		$line1.=($line1?" - ":"").$field.": ".$fromcompany->idprof1;
	}
	// Prof Id 2
	if ($fromcompany->idprof2)
	{
		$field=$langs->transcountrynoentities("ProfId2",$fromcompany->country_code);
		if (preg_match('/\((.*)\)/i',$field,$reg)) $field=$reg[1];
		$line1.=($line1?" - ":"").$field.": ".$fromcompany->idprof2;
	}

	// Second line of company infos
	$line2="";
	// Prof Id 3
	if ($fromcompany->idprof3)
	{
		$field=$langs->transcountrynoentities("ProfId3",$fromcompany->country_code);
		if (preg_match('/\((.*)\)/i',$field,$reg)) $field=$reg[1];
		$line2.=($line2?" - ":"").$field.": ".$fromcompany->idprof3;
	}
	// Prof Id 4
	if ($fromcompany->idprof4)
	{
		$field=$langs->transcountrynoentities("ProfId4",$fromcompany->country_code);
		if (preg_match('/\((.*)\)/i',$field,$reg)) $field=$reg[1];
		$line2.=($line2?" - ":"").$field.": ".$fromcompany->idprof4;
	}
	// IntraCommunautary VAT
	if ($fromcompany->tva_intra != '')
	{
		$line2.=($line2?" - ":"").$langs->transnoentities("VATIntraShort").": ".$fromcompany->tva_intra;
	}

	print '<br><br><hr>'."\n";
	print '<center><font style="font-size: 10px;">'."\n";
	print $fromcompany->nom.'<br>';
	print $line1.'<br>';
	print $line2;
	print '</font></center>'."\n";
}

/**
 *  Define head array for tabs of paypal tools setup pages
 *
 *  @return			Array of head
 */
function llxHeaderLyraadmin_prepare_head()
{
	global $langs, $conf;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/lyra/admin/lyra.php",1);
	$head[$h][1] = $langs->trans("Account");
	$head[$h][2] = 'lyraaccount';
	$h++;

	$object=new stdClass();

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname);   												to remove a tab
	complete_head_from_modules($conf,$langs,$object,$head,$h,'lyraadmin');

	complete_head_from_modules($conf,$langs,$object,$head,$h,'lyraadmin','remove');

    return $head;
}



/**
 * Return string with full Url
 *
 * @param   string	$type		Type of URL ('free', 'order', 'invoice', 'contractline', 'membersubscription' ...)
 * @param	string	$ref		Ref of object
 * @return	string				Url string
 */
function showLyraPaymentUrl($type,$ref)
{
	global $conf, $langs;

	$langs->load("paypal");
    $langs->load("paybox");
    $servicename='PayPal';
    $out='<br><br>';
    $out.=img_picto('','object_globe.png').' '.$langs->trans("ToOfferALinkForOnlinePayment",$servicename).'<br>';
    $url=getPaypalPaymentUrl(0,$type,$ref);
    $out.='<input type="text" id="paypalurl" value="'.$url.'" size="60"><br>';
    return $out;
}


/**
 * Return string with full Url
 *
 * @param   int		$mode		0=True url, 1=Url formated with colors
 * @param   string	$type		Type of URL ('free', 'order', 'invoice', 'contractline', 'membersubscription' ...)
 * @param	string	$ref		Ref of object
 * @param	int		$amount		Amount
 * @param	string	$freetag	Free tag
 * @return	string				Url string
 */
function getLyraPaymentUrl($mode,$type,$ref='',$amount='9.99',$freetag='your_free_tag')
{
	global $conf;

    if ($type == 'free')
    {
	    $out=dol_buildpath('/lyra/public/lyra/payment.php',2).'?amount='.($mode?'<font color="#666666">':'').$amount.($mode?'</font>':'').'&tag='.($mode?'<font color="#666666">':'').$freetag.($mode?'</font>':'');
	    if (! empty($conf->global->LYRA_SECURITY_TOKEN))
	    {
	    	if (empty($conf->global->LYRA_SECURITY_TOKEN_UNIQUE)) $out.='&securekey='.$conf->global->LYRA_SECURITY_TOKEN;
	    	else $out.='&securekey='.dol_hash($conf->global->LYRA_SECURITY_TOKEN, 2);
	    }
    }
    if ($type == 'order')
    {
        $out=dol_buildpath('/lyra/public/lyra/payment.php',2).'?source=order&ref='.($mode?'<font color="#666666">':'');
        if ($mode == 1) $out.='order_ref';
        if ($mode == 0) $out.=urlencode($ref);
	    $out.=($mode?'</font>':'');
        if (! empty($conf->global->LYRA_SECURITY_TOKEN))
        {
    	    if (empty($conf->global->LYRA_SECURITY_TOKEN_UNIQUE)) $out.='&securekey='.$conf->global->LYRA_SECURITY_TOKEN;
            else
            {
                $out.='&securekey='.($mode?'<font color="#666666">':'');
                if ($mode == 1) $out.="hash('".$conf->global->LYRA_SECURITY_TOKEN."' + 'order' + order_ref)";
                if ($mode == 0) $out.= dol_hash($conf->global->LYRA_SECURITY_TOKEN . 'order' . $ref, 2);
                $out.=($mode?'</font>':'');
            }
        }
    }
    if ($type == 'invoice')
    {
        $out=DOL_MAIN_URL_ROOT.'/public/paypal/newpayment.php?source=invoice&ref='.($mode?'<font color="#666666">':'');
        if ($mode == 1) $out.='invoice_ref';
        if ($mode == 0) $out.=urlencode($ref);
	    $out.=($mode?'</font>':'');
        if (! empty($conf->global->LYRA_SECURITY_TOKEN))
        {
    	    if (empty($conf->global->LYRA_SECURITY_TOKEN_UNIQUE)) $out.='&securekey='.$conf->global->LYRA_SECURITY_TOKEN;
            else
            {
                $out.='&securekey='.($mode?'<font color="#666666">':'');
                if ($mode == 1) $out.="hash('".$conf->global->LYRA_SECURITY_TOKEN."' + 'invoice' + invoice_ref)";
                if ($mode == 0) $out.= dol_hash($conf->global->LYRA_SECURITY_TOKEN . 'invoice' . $ref, 2);
                $out.=($mode?'</font>':'');
            }
        }
    }
    if ($type == 'contractline')
    {
        $out=DOL_MAIN_URL_ROOT.'/public/paypal/newpayment.php?source=contractline&ref='.($mode?'<font color="#666666">':'');
        if ($mode == 1) $out.='contractline_ref';
        if ($mode == 0) $out.=urlencode($ref);
	    $out.=($mode?'</font>':'');
        if (! empty($conf->global->LYRA_SECURITY_TOKEN))
        {
    	    if (empty($conf->global->LYRA_SECURITY_TOKEN_UNIQUE)) $out.='&securekey='.$conf->global->LYRA_SECURITY_TOKEN;
            else
            {
                $out.='&securekey='.($mode?'<font color="#666666">':'');
                if ($mode == 1) $out.="hash('".$conf->global->LYRA_SECURITY_TOKEN."' + 'contactline' + contractline_ref)";
                if ($mode == 0) $out.= dol_hash($conf->global->LYRA_SECURITY_TOKEN . 'contractline' . $ref, 2);
                $out.=($mode?'</font>':'');
            }
        }
    }
    if ($type == 'membersubscription')
    {
        $out=DOL_MAIN_URL_ROOT.'/public/paypal/newpayment.php?source=membersubscription&ref='.($mode?'<font color="#666666">':'');
        if ($mode == 1) $out.='member_ref';
        if ($mode == 0) $out.=urlencode($ref);
	    $out.=($mode?'</font>':'');
        if (! empty($conf->global->LYRA_SECURITY_TOKEN))
        {
    	    if (empty($conf->global->LYRA_SECURITY_TOKEN_UNIQUE)) $out.='&securekey='.$conf->global->LYRA_SECURITY_TOKEN;
            else
            {
                $out.='&securekey='.($mode?'<font color="#666666">':'');
                if ($mode == 1) $out.="hash('".$conf->global->LYRA_SECURITY_TOKEN."' + 'membersubscription' + member_ref)";
                if ($mode == 0) $out.= dol_hash($conf->global->LYRA_SECURITY_TOKEN . 'membersubscription' . $ref, 2);
                $out.=($mode?'</font>':'');
            }
        }
    }

    // For multicompany
    //$out.="&entity=".$conf->entity; // This should not be into link. Link contains already a ref of an object that allow to retreive entity

    return $out;
}
