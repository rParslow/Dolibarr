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
 *     	\file       htdocs/public/lyra/confirm.php
 *		\ingroup    lyra
 */

define("NOLOGIN",1);		// This means this output page does not require to be logged.
define("NOCSRFCHECK",1);	// We accept to go on this page from external web site.

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res && file_exists("../../../../main.inc.php")) $res=@include("../../../../main.inc.php");



require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");
require_once(DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php");
require_once(DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php');

dol_include_once("/lyra/class/lyra.class.php");
dol_include_once("/lyra/lib/lyra.lib.php");


// Security check
if (empty($conf->lyra->enabled))
    accessforbidden('',1,1,1);

$langs->setDefaultLang('fr_FR');

$langs->load("main");
$langs->load("other");
$langs->load("dict");
$langs->load("bills");
$langs->load("companies");
$langs->load("lyra@lyra");

// Get parameters
$params = $conf->global;

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


if ($err){
    exit;
}

$urlServer = $params->LYRA_PLATFORM_URL;

$language = strtoupper($langs->getDefaultLang(true));


/*
 * Initialisation des variables
*/
$lyra_resp = new LyraResponse(
		$_REQUEST,
		$params->LYRA_CTX_MODE,
		$params->LYRA_KEY_TEST,
		$params->LYRA_KEY_PROD
);

$from_server = $lyra_resp->get('hash');

session_start();

// Urls
$url_success = ( ! empty($params->LYRA_REDIRECT_URL) ? $params->LYRA_REDIRECT_URL : dol_buildpath('/lyra/public/lyra/success.php',1));
$url_error = dol_buildpath('/lyra/public/lyra/error.php',2);

/*
 * Vérification de l'authenticité de la requête
*/
if(! $lyra_resp->isAuthentified()) {
	if($from_server) {
		die($lyra_resp->getOutputForGateway('auth_fail'));
	} else {
		header("Location:" . $url_error);
		die();
	}
}
/*
 * Requête authentique
 */ 
 // $refDolibarr = $lyra_resp->get('order_id');

if($lyra_resp->isAcceptedPayment()) {
	
	// Message de confirmation
	if($from_server) {
		
		// Une réponse courte pour la plateforme de paiement
		die($lyra_resp->getOutputForGateway('payment_ok'));
	} else {
		
		// Get on url call
		$fulltag            = $lyra_resp->get('order_id');
		$order_info			= $lyra_resp->get('order_info');
		
		// Set by newpayment.php
		$paymentType        = $lyra_resp->get('card_brand');
		$currencyCodeType   = $lyra_resp->get('currency');
		
		// Format amount
		$cents = substr($lyra_resp->get('amount'), strlen($lyra_resp->get('amount'))-2);
		$FinalPaymentAmt = substr_replace($lyra_resp->get('amount'), ','.$cents, strlen($lyra_resp->get('amount'))-2);
		
		$referenceTransaction = $lyra_resp->get('trans_id');

		// From env
		$ipaddress          = $_SERVER['REMOTE_HOST'];
		
		
		// Send an email
		//if (! empty($conf->global->MEMBER_PAYONLINE_SENDEMAIL) && preg_match('/MEM=/',$fulltag))
		if (! empty($conf->global->LYRA_PAYONLINE_SENDEMAIL))
		{
			
			 $message_report = $langs->transnoentities("NewLyraPaymentReceivedText")."\n\ntag=".$fulltag."\npaymentType=".$paymentType."\ncurrencycodeType=".$currencyCodeType." \npayerId=".$payerID." \nipaddress=".$ipaddress." \nFinalPaymentAmt=".$FinalPaymentAmt;
			 
			$sendto=$conf->global->LYRA_PAYONLINE_SENDEMAIL;
			$from=$conf->global->MAILING_EMAIL_FROM;
			require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';
			$mailfile = new CMailFile(
					'['.$conf->global->MAIN_APPLICATION_TITLE.'] '.$langs->transnoentities("NewLyraPaymentReceived"),
					$sendto,
					$from,
					$message_report
			);
		
			$result=$mailfile->sendfile();
			if ($result)
			{
				dol_syslog("EMail sent to ".$sendto, LOG_DEBUG, 0, '_paypal');
			}
			else
			{
				dol_syslog("Failed to send EMail to ".$sendto, LOG_ERR, 0, '_paypal');
			}
		}
		
		
		$user = new User($db);
		$user->fetch($params->LYRA_BANK_USER_LOGIN);
		
		/*
		 * Payment done on an order :
		* 	create invoice from order
		* 	create payment
		* 	if payment = total facture mark as paid
		*/
		// Search object ref for order
		if( preg_match('#ORD=(.*)\..*#', $order_info,$matches))
		{
			$referenceDolibarr = $matches[1];
		
			$item = new Facture($db);
		
			if( ! empty($referenceDolibarr))
			{
				$order = new Commande($db);
				$result = $order->fetch('', $referenceDolibarr);
		
				if ($result < 0)
				{
					$err = true;
					dol_syslog('LYRA: Order with specified reference does not exist');
				}
				else
				{
					$result = $order->fetch_thirdparty();
					// Set transaction reference on order
					$item->setValueFrom('ref_int', $referenceTransaction, $order->table_element, $order->id, $format='text', $id_field='rowid');
		
		
					/*
					 * Créer une facture depuis la commande
					*
					*/
					$result = $item->createFromOrder($order);
					if ($result < 0)
					{
						$err = true;
						dol_syslog('LYRA: Failed to create invoice from order');
					}
					else
					{
						// Passe la commande au statut 'facturée'
						$order->classifyBilled();
		
						$result = $item->fetch_thirdparty();
						// Set transaction reference on invoice
						$item->setValueFrom('ref_int', $referenceTransaction, $item->table_element, $item->id, $format='text', $id_field='rowid');
						
						// on verifie si l'objet est en numerotation provisoire
						$objectref = substr($item->ref, 1, 4);
						if ($objectref == 'PROV')
						{
							$savdate=$item->date;
							if (! empty($conf->global->FAC_FORCE_DATE_VALIDATION))
							{
								$item->date=dol_now();
								$item->date_lim_reglement=$item->calculate_date_lim_reglement();
							}
							$numref = $item->getNextNumRef($soc);
							
							// Set ref on invoice
							$item->setValueFrom('facnumber', $numref, $item->table_element, $item->id, $format='text', $id_field='rowid');
							//$object->date=$savdate;
						}
						
						// Ajoute le paiement
						$db->begin();
						
						// Creation of payment line
						$payment = new Paiement($db);
						$payment->datepaye     = dol_now();
						$payment->amounts      = array($item->id => price2num($FinalPaymentAmt));
						$payment->paiementid   = dol_getIdFromCode($db, 'CB', 'c_paiement');
						$payment->num_paiement = $referenceTransaction;
						$payment->note         = '';
						
						$paymentId = $payment->create($user, $params->LYRA_UPDATE_INVOICE_STATUT);
						
						if ($paymentId < 0)
						{
							dol_syslog('LYRA: Payment has not been created in the database');
						}
						else
						{
							if (!empty($params->LYRA_BANK_ACCOUNT_ID))
							{
								$payment->addPaymentToBank($user, 'payment', '(CustomerInvoicePayment)', $params->LYRA_BANK_ACCOUNT_ID, $item->client->name, $clientBankName);
								
								/*
								 * TODO : validate invoice (be careful !!) 
								 if ( $params->LYRA_UPDATE_INVOICE_STATUT > 0)
								{
									$item->validate($user);
								}
								*/
							}
						}
						$db->commit();
					}
				}
			}
		}

		/*
		 * Payment done on an invoice :
		* 	create payment
		* 	if payment = total facture mark as paid
		*/
		// Search object ref for order
		if( preg_match('#INV=(.*)\..*#', $order_info,$matches))
		{
			$referenceDolibarr = $matches[1];
		
			$item = new Facture($db);
		
			if( ! empty($referenceDolibarr))
			{
				$invoice = new Facture($db);
				$result = $invoice->fetch('', $referenceDolibarr);
		
				if ($result < 0)
				{
					$err = true;
					dol_syslog('LYRA: Invoice with specified reference does not exist');
				}
				else
				{
					$result = $invoice->fetch_thirdparty();
					// Set transaction reference on order
					$item->setValueFrom('ref_int', $referenceTransaction, $invoice->table_element, $invoice->id, $format='text', $id_field='rowid');
		
                    $result = $item->fetch_thirdparty();
                    
                    // Set transaction reference on invoice
                    $item->setValueFrom('ref_int', $referenceTransaction, $item->table_element, $item->id, $format='text', $id_field='rowid');
						
                    // on verifie si l'objet est en numerotation provisoire
                    $objectref = substr($item->ref, 1, 4);
                    if ($objectref == 'PROV')
						{
							$savdate=$item->date;
							if (! empty($conf->global->FAC_FORCE_DATE_VALIDATION))
                                {
                                    $item->date=dol_now();
                                    $item->date_lim_reglement=$item->calculate_date_lim_reglement();
                                }
							$numref = $item->getNextNumRef($soc);
							
							// Set ref on invoice
							$item->setValueFrom('facnumber', $numref, $item->table_element, $item->id, $format='text', $id_field='rowid');
							//$object->date=$savdate;
						}
                    
						// Ajoute le paiement
						$db->begin();
						
						// Creation of payment line
						$payment = new Paiement($db);
						$payment->datepaye     = dol_now();
						$payment->amounts      = array($item->id => price2num($FinalPaymentAmt));
						$payment->paiementid   = dol_getIdFromCode($db, 'CB', 'c_paiement');
						$payment->num_paiement = $referenceTransaction;
						$payment->note         = '';
						
						$paymentId = $payment->create($user, $params->LYRA_UPDATE_INVOICE_STATUT);
						
						if ($paymentId < 0)
						{
							dol_syslog('LYRA: Payment has not been created in the database');
						}
						else
						{
							if (!empty($params->LYRA_BANK_ACCOUNT_ID))
							{
								$payment->addPaymentToBank($user, 'payment', '(CustomerInvoicePayment)', $params->LYRA_BANK_ACCOUNT_ID, $item->client->name, $clientBankName);
								
								/*
								 * TODO : validate invoice (be careful !!) 
								 if ( $params->LYRA_UPDATE_INVOICE_STATUT > 0)
								{
									$item->validate($user);
								}
								*/
							}
						}
						$db->commit();
					}
				}
			}
		}
        
    // Une belle page pour le client
    if($lyra_resp->get('ctx_mode') == 'TEST') {
        echo '<html>';
        echo '<head><meta http-equiv="content-type" content="text/html; charset=UTF-8" /></head>';
        echo '<body>';
        echo "Avertissement mode TEST : la commande a bien été enregistrée, mais la validation automatique n'a pas fonctionné.";
        echo "<br/>Vérifiez que vous avez correctement configuré l'url serveur (".$params->LYRA_URL_RETURN.") ";
        echo "dans l'outil de gestion de caisse Lyra et qu'elle est accessible depuis internet";
        echo '<br/>En mode production, vous serez redirigé automatiquement vers <a href="'.$url_success.'">la page de succès</a>';
        echo '</body></html>';
        exit();
    } else {
        header("Location:" . $url_success);
        exit();
    }
}
else {
	// Message de confirmation
	if($from_server) {
	// Une réponse courte pour la plateforme de paiement
		die($lyra_resp->getOutputForGateway('payment_ko'));
	} else {
		// Retour à la liste des moyens de paiement pour le client
		header("Location:" . $url_error);
		exit();
	}
}



$db->close();
?>
