<?php
/**
 *  titolo: Bibliosardbot
 *  autore: Matteo Enna
 *  licenza GPL3
 **/
 
define('BOT_TOKEN', '[your-token]');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

// read incoming info and grab the chatID
$content = file_get_contents("php://input");
$update = json_decode($content, true);
$chatID = $update["message"]["chat"]["id"];
		
$benvenuto="Benvenuto su Biblioteche della Sardegna, \ndigitando il nome di un comune visualizzerai l'elenco di tutte le biblioteche presenti. \n \nIl bot è stato realizzato utilizzando gli Opendata messi a disposizione della Regione Sardegna. \n \nRealizzato da Matteo Enna, \nRilasciato sotto licenza GPL3, potete trovare il progetto su GitHub: https://github.com/Ellusu/https://github.com/Ellusu/sardegnabiblioteche_bot";
		
if($update["message"]["text"]=="/start"){
	$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($benvenuto);
	file_get_contents($sendto);
	die();
}

$help ="Digita il nome del comune e invia. \n\nPer qualsiasi dubbio, informazione o chiarimento puoi scrivermi su telegram @matteoenna oppure mandarmi una mail: matteo.enna89@gmail.com";

if($update["message"]["text"]=="/help"){
	$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($help);
	file_get_contents($sendto);
	die();
}
	
if (!array_key_exists('text', $update["message"]) ) {
	$type_ns="Formato non supportato, digita il nome di un comune Sardo";
    $sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($type_ns);
	file_get_contents($sendto);
	die();
}

// compose reply
$reply =  sendMessage($update["message"]["text"],$chatID);
		
// send reply
$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".$reply;
file_get_contents($sendto);

function sendMessage($bidda, $chatID){
	$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=Cerco ".$bidda;
	file_get_contents($sendto);

	if(strlen($bidda)<4 && !is_array($bidda)){
		$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode("Zona non trovata");
		file_get_contents($sendto);
		die();
	}
    
	$risultati =array();
	
	$simple = file_get_contents("data/biblioteche.csv");
	
	$righe=explode(chr(10),$simple);
    
    foreach($righe as $s){
        $contact = array();
        $response =array();
        $col = explode('|',$s);
                
        if($col[6]==$bidda || strripos($col[6],$bidda)){            
            if($col[10]!="--" && $col[10]!="") $contact[]="Telefono: ".$col[10];
            if($col[11]!="--" && $col[11]!="") $contact[]="Fax: ".$col[11];
            if($col[12]!="--" && $col[12]!="") $contact[]="E-mail: ".$col[12];
            if($col[13]!="--" && $col[13]!="") $contact[]="Sito web: ".$col[13];
            if($col[14]!="--" && $col[14]!="") $contact[]="Pagine web: ".$col[14];
            
            $response = array (
                'id'=>  str_replace('"', '', $col[0]),
                'nome'=>  str_replace('"', '', $col[1]),
                'indirizzo'=> str_replace('"', '', $col[9]),
                'contatti'=> str_replace('"', '', implode("\n",$contact))
            );
            $risultati[]=$response;
            
        }
        
    }
    $tot = count($risultati);
	if($tot!=1) $tot = $tot;
	if($tot<0) $tot=0;
	$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=Risultati trovati ".$tot;
	file_get_contents($sendto);
	
	if($tot >200){
		$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode("Consigliamo di usare una parola chiave più precisa");
		file_get_contents($sendto);
		die();
	}
	
	$testo = '';
	$acapo="\n";
	
	foreach ($risultati as $k => $res){
	
		if($res['id']!="--") $testo .= $res['id'].$acapo.$res['nome'];
        else $testo .= $res['nome'];
		$testo .= $acapo;
		$testo .= $res['indirizzo'];
		$testo .= $acapo;
		$testo .= $res['contatti'];
		$testo .= $acapo;
        if($tot != 1) {
            $testo .= ($k+1).'/'.$tot;
            $testo .= $acapo;
        }
		$testo .= $acapo;
		
		if($k % 200){
			$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($testo);
			file_get_contents($sendto);
			$testo='';
		}
	}
	$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=".urlencode($testo);
	file_get_contents($sendto);
	
	$sendto =API_URL."sendmessage?chat_id=".$chatID."&text=Fine";
	file_get_contents($sendto);
	
	$message = urlencode($test);
	return $message;
}

?>
