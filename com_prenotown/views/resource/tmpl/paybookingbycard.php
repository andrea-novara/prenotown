<?php
	/**
	 * @package Prenotown
 	 * @copyright XSec
 	 * @license GNU GPL v.2
	 */
	/** ensure a valid entry point */
	defined('_JEXEC') or die("Restricted Access");

	// get autouri
	$uri =& JURI::getInstance();

	// set the booking id
	$booking_id = JRequest::getInt('booking_id', 0);
	$this->superbooking->setId($booking_id);

	$begin = $this->superbooking->tables['superbooking']->begin;
	$end = $this->superbooking->tables['superbooking']->end;

	if (preg_match('/^(\d\d\d\d-\d\d-\d\d)\s+(\d\d):(\d\d)/', $begin, $matches)) {
		$booking_begin_date	= preg_replace("/^(\d\d\d\d)-(\d\d)-(\d\d)/", "$3-$2-$1", $matches[1]);
		$booking_begin_hour	= $matches[2];
		$booking_begin_minute	= $matches[3];
	}
	if (preg_match('/^(\d\d\d\d-\d\d-\d\d)\s+(\d\d):(\d\d)/', $end, $matches)) {
		$booking_end_date	= preg_replace("/^(\d\d\d\d)-(\d\d)-(\d\d)/", "$3-$2-$1", $matches[1]);
		$booking_end_hour	= $matches[2];
		$booking_end_minute	= $matches[3];
	}

	$cost		= $this->superbooking->tables['superbooking']->cost;

	$IMPORTO	= $cost * 100;
	$NUMORD		= pref("bpw_nome_negozio") . $this->superbooking->tables['superbooking']->payment_id;
	$IDNEGOZIO	= pref("bpw_idnegozio");
	$VALUTA		= pref("bpw_valuta");
	$TCONTAB	= pref("bpw_tcontab");
	$TAUTOR		= pref("bpw_tautor");
	$EMAILESERC	= pref("bpw_emaileserc");
	$LINGUA		= "ITA";

	$OPTIONS	= "";
	foreach (array('a', 'b', 'c', 'd', 'e', 'i') as $opt) {
		if (pref("bpw_options_$opt")) {
			$OPTIONS .= $opt;
		}
	}

	$URLBACK	= urlencode(JURI::base() . "?option=com_prenotown&view=resource&layout=paybookingbycard&booking_id=$booking_id&id=" . $this->id);
	$URLDONE	= urlencode(JURI::base() . "?option=com_prenotown&view=resource&layout=paybookingbycardconfirmation&booking_id=$booking_id&id=" . $this->id);
	$URLMS		= urlencode(JURI::base() . "?option=com_prenotown&view=resource&layout=blank&format=raw&task=bpw_confirm_booking&booking_id=$booking_id&id=" . $this->id);

	// MAC IN CHIARO
	$StringaPerMac	= "NUMORD=$NUMORD&IDNEGOZIO=$IDNEGOZIO&IMPORTO=$IMPORTO&VALUTA=$VALUTA&TCONTAB=$TCONTAB&TAUTOR=$TAUTOR";
	if (strlen($OPTIONS) > 0) {
		// $StringaPerMac .= "&OPTIONS=$OPTIONS";
		if (pref('bpw_options_b')) {
			// .... aggiungi nome e cognome
			$StringaPerMac .= "&NOME=" . $booking_user['first_name'] . "&COGNOME=" . $booking_user['last_name'];
		}
	}
	$StringaPerMac .= "&" . pref('bpw_start_key');
	
	// CALCOLO DEL MAC SHA1
	$MAC = strtoupper(sha1($StringaPerMac));
	
	// Generazione dell'URL su cui reindirizzare il browser del cliente
	// (le URL sono in formato url-encoded)
	$RedirectionURL = pref("bpw_url_pagamento");
	$RedirectionURL .= "&IMPORTO=$IMPORTO&VALUTA=$VALUTA&NUMORD=$NUMORD&IDNEGOZIO=$IDNEGOZIO";
	$RedirectionURL .= "&URLBACK=$URLBACK&URLDONE=$URLDONE&URLMS=$URLMS&TCONTAB=$TCONTAB&TAUTOR=$TAUTOR";
	$RedirectionURL .= "&LINGUA=$LINGUA&EMAILESERC=$EMAILESERC";
	$RedirectionURL .= "&MAC=$MAC";
	
	// REINDIRIZZAMENTO
	
	// Header("Location: $RedirectionURL");
?>
<h2><?php echo $this->name . ":<br/> " . JText::_("Booking payment") . " " . JText::_("by credit card") ?></h1>
<h2><?php echo JText::_("Your booking") ?></h2><br/><br/>
<?php format_booking_by_id($booking_id) ?>

<br/>
<b>Premento il pulsante "Pagamento on-line" ti collegherai al servizio BankPass per effettuare il pagamento.</b>
<br/><br/>
<div class="button-footer">
<button class="button" onClick="redirect('index.php?option=com_prenotown&view=resource&layout=paybookinglater&id=<?php echo $this->id ?>&booking_id=<?php echo $booking_id ?>')"><?php echo JText::_("Pay otherwise") ?></button>&nbsp;&nbsp;|&nbsp;&nbsp;
<button class="button" onClick="redirect('<?php echo $RedirectionURL ?>')"><?php echo JText::_("Pay on-line") ?></button>
</div>
