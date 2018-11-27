<?php
//27-11-2018
//started on 27-02-2018
// La app di Heroku si puo richiamare da browser con
//			https://bedzie.herokuapp.com/


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
/on_on    -> Outlet2 ON  Outlet1 ON 
/2on_1off -> Outlet2 ON  Outlet1 OFF  
/2off_1on -> Outlet2 OFF Outlet1 ON
/off_off  -> Outlet2 OFF Outlet1 OFF
/letto  -> Lettura stazione1 ... su bus RS485
";

if(strpos($text, "/start") === 0 || $text=="ciao" || $text == "help"){
	$response = "Ciao $firstname, benvenuto   \n". $helptext; 
}

//<-- Comandi ai rele
elseif($text=="/on_on"){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/1/3");
}
elseif($text=="/2on_1off"){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/1/2");
}
elseif($text=="/2off_1on"){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/1/1");
}
elseif(strpos($text,"off_off")){
	$response = file_get_contents("http://dario95.ddns.net:8083/rele/1/0");
}
//<-- Lettura parametri slave5
elseif($text=="/letto"){
	$response = file_get_contents("http://dario95.ddns.net:8083/letto");
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
// imposto la keyboard
$parameters["reply_markup"] = '{ "keyboard": [["/on_on", "/2on_1off"],["/2off_1on", "/off_off \ud83d\udd35"],["/letto","/verbose"]], "one_time_keyboard": false}';
// converto e stampo l'array JSON sulla response
echo json_encode($parameters);
?>