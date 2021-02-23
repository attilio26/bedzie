<?php
//23-02-2021
//started on 27-02-2018
// La app di Heroku si puo richiamare da browser con
//			https://bedzie.herokuapp.com/
// Account Heroku:  dariomelucci@gmail.com   pwd:  Bg_142666
// Account GitHub:	attiliomelucci@gmail.com pwd:  bg142666    name: attilio26
// @bedziebot

/*API key = 554186515:AAG2DMXJOJlzPEuCN99F6WlSfd7qUY72YSE

da browser request ->   https://bedzie.herokuapp.com/register.php
           answer  <-   {"ok":true,"result":true,"description":"Webhook is already set"}
In questo modo invocheremo lo script register.php che ha lo scopo di comunicare a Telegram
l’indirizzo dell’applicazione web che risponderà alle richieste del bot.

da browser request ->   https://api.telegram.org/bot554186515:AAG2DMXJOJlzPEuCN99F6WlSfd7qUY72YSE/getMe
           answer  <-   {"ok":true,"result":{"id":554186515,"is_bot":true,"first_name":"bedzie","username":"bedzie_bot"}}

riferimenti:
https://gist.github.com/salvatorecordiano/2fd5f4ece35e75ab29b49316e6b6a273
https://www.salvatorecordiano.it/creare-un-bot-telegram-guida-passo-passo/
*/
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(!$update)
{
  exit;
}

function clean_html_page($str_in){
	$startch = strpos($str_in," <a href='?a=2'/> ") + 1 ;							//primo carattere utile da estrarre
	$endch = strpos($str_in,"ter>powerd") -4;									//ultimo carattere utile da estrarre
	$str_in = substr($str_in,$startch,$endch - $startch);				// substr(string,start,length)
	//$str_in = str_replace("<a href='?a="," ",$str_in);
	//$str_in = str_replace("r><h2>"," ",$str_in);
	//$str_in = str_replace(" </a></h2><h2>"," ",$str_in);
	//$str_in = str_replace("1'/>"," ",$str_in);
	//$str_in = str_replace("2'/>"," ",$str_in);
	//$str_in = str_replace("3'/>"," ",$str_in);
	//$str_in = str_replace("4'/>"," ",$str_in);
	//$str_in = str_replace("5'/>"," ",$str_in);
	//$str_in = str_replace("6'/>"," ",$str_in);
	//$str_in = str_replace("7'/>"," ",$str_in);	
	//$str_in = str_replace("8'/>"," ",$str_in);
	//$str_in = str_replace("9'/>"," ",$str_in);		
	return $str_in;
}

$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$text = isset($message['text']) ? $message['text'] : "";

// pulisco il messaggio ricevuto togliendo eventuali spazi prima e dopo il testo
$text = trim($text);
// converto tutti i caratteri alfanumerici del messaggio in minuscolo
$text = strtolower($text);

header("Content-Type: application/json");

//ATTENZIONE!... Tutti i testi e i COMANDI contengono SOLO lettere minuscole
$response = '';
$helptext = "List of commands : 
/Presa_BED ON			-> Outlet2 ON   
/Presa_BED OFF		-> Outlet2 OFF   
/Presa_LIVING ON	-> Outlet1 ON   
/Presa_LIVING OFF	-> Outlet1 OFF 
/letto  -> Lettura stazione1 ... su bus RS485
";

if(strpos($text, "/start") === 0 || $text=="ciao" || $text == "help"){
	$response = "Ciao $firstname, benvenuto   \n". $helptext; 
}

//<-- Comandi ai rele
elseif(strpos($text,"bedon")){
	$resp = file_get_contents("http://dario95.ddns.net:8083/?a=o");
	$response = clean_html_page($resp);
}
elseif(strpos($text,"bedoff")){
	$resp = file_get_contents("http://dario95.ddns.net:8083/?a=p");
	$response = clean_html_page($resp);
}
elseif(strpos($text,"livon")){
	$resp = file_get_contents("http://dario95.ddns.net:8083/?a=q");
	$response = clean_html_page($resp);
}
elseif(strpos($text,"livoff")){
	$resp = file_get_contents("http://dario95.ddns.net:8083/?a=r");
	$response = clean_html_page($resp);
}
//<-- Lettura parametri slave5
elseif (strpos($text,"letto")){
	$resp = file_get_contents("http://dario95.ddns.net:8083");
	$response = clean_html_page($resp);
}

//<-- Manda a video la risposta completa
elseif($text=="/verbose"){
	$response = "chatId ".$chatId. "   messId ".$messageId. "  user ".$username. "   lastname ".$lastname. "   firstname ".$firstname. "\n". $helptext ;	
	$response = $response. "\n\n Heroku + dropbox gmail.com";
}


else
{
	$response = "Unknown command!";			//<---Capita quando i comandi contengono lettere maiuscole
}

// la mia risposta è un array JSON composto da chat_id, text, method
// chat_id mi consente di rispondere allo specifico utente che ha scritto al bot
// text è il testo della risposta
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
// Gli EMOTICON sono a:     http://www.charbase.com/block/miscellaneous-symbols-and-pictographs
//													https://unicode.org/emoji/charts/full-emoji-list.html
//													https://apps.timwhitlock.info/emoji/tables/unicode
// imposto la keyboard
$parameters["reply_markup"] = '{ "keyboard": [["/bedon \ud83d\udd34", "/bedoff \ud83d\udd35"],["/livon \ud83d\udd34", "/livoff \ud83d\udd35"],["/letto \u2753"]], "one_time_keyboard": false, "resize_keyboard": true}';
// converto e stampo l'array JSON sulla response
echo json_encode($parameters);
?>