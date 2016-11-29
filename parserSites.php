<?php
class parserSites{


// ------------------------CURL-------------------------------------//

	// for curl
	private $url, $proxy, $proxyAuth, $browser, $referer, $postDataStr, $httpHeader, $pathCookies, $cookies;


	public function setUrl($url){
		$url = trim( $url );

		if( $this->valid( $url, "/^https*:\/\//", 105 ) ){
			$this->url = $url;
		}

	}

	// установка proxy
	public function setProxy( $proxy ){

		$proxy = trim( $proxy );

		if( $this->valid( $proxy, "/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+:[0-9]+$/", 100 ) ){
			$this->proxy = $proxy;
		}

	}

	public function setProxyAuth( $proxyAuth ){

		$proxyAuth = trim( $proxyAuth );

		if( $this->valid( $proxyAuth, "/^[\S]+:[\S]+$/", 101 ) ){
			$this->$proxyAuth = $proxyAuth;
		}

	}

	public function setReferer( $referer ){
		$referer = trim( $referer );

		if( $this->valid( $referer, "/^https*:\/\//", 102 ) ){
			$this->$referer = $referer;
		}

	}

	public function setPostData( $postData ){

		if( is_array( $postData ) && ! empty( $postData ) ){
			$this->$postData = $postData;

			$postDataStr = '';

			//create name value pairs seperated by &
			foreach($postData as $k => $v) 
			{ 
			   $postDataStr .= $k . '='.$v.'&'; 
			}
			$this->postDataStr = trim($postDataStr, '&');

		} else {
			$this->getErrorText( 103 );

		}


	}

	public function setBrowser($browser){

		if( is_string($browser) ){
			$browser = trim( $browser );
			$this->browser = $browser;
		} else {
			$this->getErrorText( 104 );

		}
	}

	public function setHttpHeader($httpHeader){

		if( is_array( $httpHeader ) && ! empty( $httpHeader ) ){
			$this->httpHeader = $httpHeader;
		} else {
			$this->getErrorText( 107 );

		}

	}

	public function setPathCookies($pathCookies){
		if( is_string($pathCookies) ){
			$pathCookies = trim( $pathCookies );
			$this->pathCookies = $pathCookies;
		} else {
			$this->getErrorText( 108 );

		}
		

	}

	public function setCookies($cookies){

		$this->cookies = $cookies;

	}

	private function valid($value, $reg, $errorId){

		$check = preg_match( $reg, $value );
		if( $check ){
			return true;
		} else {
			$this->getErrorText( $errorId );
		}

	}




	public function curl(){

		if( ! $this->url ) {
			$this->getErrorText( 106 );
		}

		if( ! $this->browser ){
			$this->browser = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36';
		}
		if( ! $this->httpHeader ){
			$this->httpHeader = array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8','Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3','Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7');
		}

		$ch = curl_init();
		// параметры подключения
		curl_setopt($ch, CURLOPT_URL, $this->url);

		if( $this->proxy ){
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
			if( $this->proxyAuth ){
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyAuth);
			}
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER,$this->httpHeader); 
		curl_setopt ($ch, CURLOPT_USERAGENT, $this->browser);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		if( $this->pathCookies ) {
			curl_setopt($ch, CURLOPT_COOKIEFILE,$this->pathCookies);
			curl_setopt($ch, CURLOPT_COOKIEJAR,$this->pathCookies);

		}

		if( $this->cookies ) {
			curl_setopt($ch, CURLOPT_COOKIEJAR,$this->cookies);

		}

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($this->referer){
			curl_setopt($ch, CURLOPT_REFERER, "http://www.sovsportizdat.ru");
		}
		if($this->postDataStr){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postDataStr);
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$content = curl_exec($ch);

		return $content;

	}



// ------------------------CURL-------------------------------------//

	private $html;
	
	public function getData($environ){
		$environ = preg_replace("/'/",'"',$environ);
		$environ = preg_replace("/([\S]){1}\s*=\s*\"/",'$1="',$environ);

		$this->html = $environ;

	}

	// функция для сбора php кода поиска
	public function lego($a,$b=0,$c=0,$n=0){

		if( ! $this->html || empty($this->html) ) {
			$this->getErrorText( 200 );

		}

		$d=$this->html;
		if(!preg_match("/#[0-9]{1}#/",$a))$e=0; // выбор где искать $e => $r в функциях // ищем во всем html
		if(preg_match("/#1#/",$a))$e=$b; // Ищем в div array
		if(preg_match("/#2#/",$a))$e=$c; // Ищем в div string

		$a = preg_replace("/#[0-9]{1}#/","",$a);

		$s = explode("|#|",$a);

		for( $i = 0 ; $i < count($s) ; $i++ ) {
			$a=explode("(#)",$s[$i]);
			switch($a[0]){
				case "":// просто выполняем код
				$r = '';
				break;
				case "1":// просто выполняем код
				$r = $b[$a[1]];
				break;
				case "2":// simple_tags парсим теги без аналогичых тегов
				$r = $this->simple_tags($e,$a[1]);
				break;			
				case "3":// hard_tags парсим теги с аналогичными
				$r = $this->hard_tags($e,$a[1]);
				break;
				case "4":// find_attr поиск атрибута 
				$r = $this->find_attr($e,$a[1],$a[2]);
				break;			
				case "5":// values следующий тег по значению внутри тега
				$r = $this->values($e,$a[1],$a[2]);
				break;			
				case "6":// after_tags
				$r = $this->after_tags($e,$a[1],$a[2]);
				break;			
				case "7":// after_tags_html
				$r = $this->after_tags_html($e,$a[1],$a[2],$a[3]);
				break;
				case "8":// по регулярному выражению поиск
				$r = $this->regul($e,$a[1],$a[2],$n);
				break;				
				case "9": // по регулярному выражению замена
				$r = $this->replace($e,$a[1],$a[2]);
				break;				
				case "10":// values текущий тег по значению внутри тега
				$r = $this->current_val($e,$a[1],$a[2]);
				break;				
				case "11":
				$r = $this->trunc_tag($e);
				break;
				case "12":// если можно скачать изображения по прямой ссылке http://files.atticus-group.ru/covers/978-5-389-08029-4.jpg
				$r=$a[1].trim($n).$a[2];
				break;
				case "13": // математические функции $a[1] - число, $a[2] - знак
				$r=$this->math($e,$a[1],$a[2]);
				break;
				case "14": // проверяет наличие тега в искомом в случае неудачи обнуляет результат
				$r=$this->true_tag($e,$a[1]);
				break;
				case "15": // разбивает div на массив со значениями между тегов
				$r=$this->div_conv_array($e);
				break;
				case "16": // если значение равно 'val' то 'val2'
				$r=$this->if_else($e,$a[1],$a[2]);
				break;
				case "17": // удаляет теги
				$r=$this->strip_tag($e);
				break;				
				case "18": // удаляет теги
				$r=$this->strip_tag($e);
				break;
				case "19": // нормализует атрибуты - добавяет ковычки
				$r=$this->add_atrtags($e);
				break;
				case "20": // если нужно несколько тегов объединий в одно значение - $a[1] -тип операции, $a[2] - разделитель при join
				$r=$this->tag_plus_tag($e,$b,$c,$a[1],$a[2]);
				break;
				case "21": // удаляем комментарии
				$r=$this->del_comment($e); // удаляет html комментарии
				break;				
				case "22": // подчитывает количество уникальных блоков
				$r=$this->count_unique($e,$a[1]); // 
				break;
				case "pub";// img
				$r = $a[1];
				break;
			}
			if(isset($s[$i+1]))$e=$r;
		}
		$r = $this->data_revice($r);
		
		return $r;
		
	}
	

	// simple_tags поиск тега внутри которого нет себе подобных
	private function simple_tags($r,$a){
		$res=$r=='0'?$this->html:$r;
		if(preg_match("/[\[\]]+/",$a)){// если есть атрибуты
			$b=explode("[",$a);
			for($i=1;$i<count($b);$i++){
				$c.="[^>]*".str_replace(']','',$b[$i]);
			}
			preg_match("/<{$b[0]}{$c}[^>]*>(.+)<\/{$b[0]}>/sU",$res,$s);
			$this->tags=$s[0];
			return $this->tags;
		}else{
			preg_match("/<{$a}>.+<\/{$a}>/sU",$res,$s);
			$this->tags=$s[0];
			return $this->tags;
		}
	}
	
	// hard_tags поиск тега внутри которого есть себе пободные количество атрибутов неограничено
	private function hard_tags($r,$a,$dop='',$itteration=0){
		$res=$r=='0'?$this->html:$r;
		if($res!='' && $itteration<200){
			$b=explode("[",$a);

			for($i=1;$i<count($b);$i++){
				$c.="[^>]+".str_replace(']','',$b[$i]);
			}
			preg_match("/<{$b[0]}{$c}[^>]{0,}>(.+){$dop}(<\/{$b[0]}>|<{$b[0]})/sU",$res,$s); $c='';
			preg_match_all("/<{$b[0]}/",$s[0],$c_t1);
			preg_match_all("/<\/{$b[0]}>/",$s[0],$c_t2);
			if(count($c_t1[0])!=count($c_t2[0])){
				$dop=$dop."<\/{$b[0]}>(.*)";
				$itteration++;
				$this->hard_tags($res,$a,$dop,$itteration);
			}else{
				$this->tags=$s[0];
			}
		}else{
			return;
		}
		return $this->tags;
	}
	
	//поиск значения атрибута
	// умеет искать много
	private function find_attr($r,$a,$dop){
		$j=$a;
		$a=preg_replace("/&.+&/U","",$a);
		$res=$r=='0'?$this->html:$r;
		$attr=array(); $all=array();
		$b=explode("[",$a);

		for($i=1;$i<count($b);$i++){
			$c.="[^>]*".str_replace(']','',$b[$i]);
		}
		preg_match_all("/<{$b[0]}{$c}[^>]*>/sU",$res,$s);

		// дополнительный атрибут $q дает возможность выбирать сколько и какие изображения следует брать
		if(preg_match("/&([0-9]+)&/",$j,$h)){
			$q[0]=$h[1];
			$q[1]=$h[1]+1;
		}
		elseif(preg_match("/&([0-9]+,[0-9]+)&/",$j,$h)){
			$h=explode(",",$h[1]);
			$q[0]=$h[0];
			$q[1]=$h[1];			
		}elseif(preg_match("/&([0-9]+),&/",$j,$h)){
			$q[0]=$h[1];
			$q[1]=count($b);
		}elseif(preg_match("/,&([0-9]+)&/",$j,$h)){
			$q[0]=0;
			$q[1]=$h[1];
		}else{
			$q[0]=0;
			$q[1]=1;
		}
		
		for($i=$q[0];$i<$q[1];$i++){
			preg_match("/<[^>]*>/U",$s[0][$i],$e);
			$d=preg_split("/(\"|')[\s]+/",$e[0]);
			$d[0]=preg_replace("/^<[a-z]+ /","",$d[0]);// маленький костыль удаляет в начале строки <tag 
			
			for($i2=0;$i2<count($d);$i2++){
				$e=preg_split("/=(\"|')/",$d[$i2]);
				$attr[$i][$e[0]]=preg_replace("/[\"'>]+/","",$e[1]);
			}
			if($attr[$i][$dop]!='')array_push($all,$attr[$i][$dop]);
		}

		$all=join(',',$all);
		
		$this->attr=$all;

		return $this->attr;
	}
	
	private function values($a,$value,$i=1){
		if(!$i)$i=1;
		if(preg_grep($value,$a)){
			$type=preg_grep($value,$a);
			$type_keys=array_keys($type);
			$type=addslashes($a[$type_keys[0]+$i]);
			$type=preg_replace("/\s{2,}/i","",$type);
			$type=preg_replace("/\'/i","",$type);
			return $type;
		}else{
			return '';
		}
	}

	private function current_val($a,$search,$b=0){
		if(preg_grep($search,$a)){
			$var=preg_grep($search,$a);
			$var=array_values($var);
			
			if($b)return join("<br />", $var);
			
			$var[0]=preg_replace("/\s{2,}/i","",$var[0]);
			$var[0]=preg_replace("/\'/i","",$var[0]);
			if(strlen($var[0])<150)$var[0]=preg_replace($search,"",$var[0]);
			$var[0]=trim($var[0]);
			return $var[0];
		}else{
			return '';
		}
	}
	
	// тег который после тега содержащего значение $val
	private function after_tags($r,$tag,$str){
		$res=$r=='0'?$this->html:$r;
		$bool=preg_match("/>{$str}.+(<{$tag}.+<\/{$tag}>)/sU",$res,$res);
		if($bool){
			$res=preg_replace("/[\s ]{2,}/sU"," ",$res[1]);
			return strip_tags($res);
		}else{
			return '';
		}
	}
	
	// тег который после тега <>
	private function after_tags_html($r,$tag1,$tag2,$tag3){
		$res=$r=='0'?$this->html:$r;
		if(preg_match("/[\[\]]+/",$tag1)){
			$b=explode("[",$tag1);
			for($i=1;$i<count($b);$i++){
				$c.="[^>]+".str_replace(']','',$b[$i]);
			}
			$bool=preg_match("/<{$b[0]}{$c}.+<\/{$b[0]}>.*(<{$tag2}.+<\/{$tag3}>)/sU",$res,$res);
			if($bool){
				$res=preg_replace("/[\s ]{2,}/sU"," ",$res[1]);
				return strip_tags($res);
			}else{
				return '';
			}
		}else{
			$bool=preg_match("/<{$tag1}>[^<>]+<\/{$tag1}>.*(<{$tag2}.+<\/{$tag3}>)/sU",$res,$res);
			if($bool){
				$res=preg_replace("/[\s ]{2,}/sU"," ",$res[1]);
				return strip_tags($res);
			}else{
				return '';
			}
		}
	}

	private function regul($r,$reg,$b,$n=''){
		if(preg_match("/\+=\+/",$reg)){
			$reg=explode("+=+",$reg);
			$reg=$reg[0].$n.$reg[1];
			$reg=str_replace('+=+','',$reg);
		}
		$res=$r=='0'?$this->html:$r;
		preg_match($reg,$res,$a);
		return $b.$a[0];
	}
	
	private function replace($res,$a,$b=""){
		return preg_replace("$a","$b",$res);
	}
	// функция очищающая теги от атрибутов 	// Для замены адреса сайта на Книга ру
	private function trunc_tag($e){
		$e = preg_replace("/[A-Za-z0-9]+\.ru/",'KNIGA.RU',$e);
		return preg_replace("/ [^<>]+>/s",">",$e);
	}
	// функия для математических операций
	private function math($a,$b,$type){
		$a=strip_tags($a);
		switch($type){
			case '*':
			$c=$a*$b;
			break;
		}
		return $c;
	}
	
	// проверяет наличие тега в искомом в случае неудачи обнуляет результат
	private function true_tag($a,$b){
		if($this->find_attr($a,$b))return $a;
		else return '';
	}
	
	// разбивает div на массив со значениями между тегов
	private function div_conv_array($a){
		preg_match_all("/(?<=>)[^<>]+(?=<)/i",$a,$a);
		$a=array_filter($a[0],"filter");
		$a=array_values($a);
		return $a;
	}
	
	// если значение равно 'val' то 'val2'
	private function if_else($a,$b,$c){
		return $a==$b?$c:$a;
	}
	
	private function strip_tag($a){
		return strip_tags($a);
	}
	
	private function add_atrtags($a){
		if(!$a)$a = $this->html;

		$reg = "[ ]*[a-zA-Z0-9\/\._\:#%\'\`-]+";
		$a = preg_replace("/(<[^>]+=)(".$reg.")/s",'\1"\2"',$a);
		if(preg_match("/<[^>]+=[ ]*['\"]{0}".$reg."/s",$a))return $this->add_atrtags($a);
		else return $a;

	}

	private function tag_plus_tag($a,$b,$c,$type, $seporat){
		/*
		1-ый параметр $type - определяет с какой частью работать
			1 - загружает значения уже имеющегося результата в $tag_plus
			2 - выбирает массив данных для работы с ним
			3 - выбирает строчные данные для работы с ними
			4 - возвращает итоговый склееный результат
		Пример: 20(#)1(загружаем данные)|#|20(#)2(выбираем область)|#|5(#)/Длинна/(Производим операции)|#|(выводим склееный результат)20(#)1|#|20(#)2|#|20(#)4(#)*
		*/
		switch($type){
			case 1:
			array_push($this->tag_plus, $a);
			break;
			case 2:
			return $b;
			break;
			case 3:
			return $c;
			break;
			case 4:
			return join("$seporat",$this->tag_plus);
			break;
			
		}
		
	}

	private function del_comment($a){
		return preg_replace("/<\!--.+-->/Us",'',$a);
	}
	

	// function for revice of data
	private function data_revice($a){
		
		$a = $this->del_comment($a);
		//$a = $this->trunc_tag($a);
		
		return $a;
	}
	
	private function count_unique($a,$d){
		
		preg_match_all("$d",$this->html,$b);
		return ($this->html);
	}	

	// функция для определения кодировки
	private function decode($a,$d){
		switch($d){
			case 1:
				return $a;
				break;
			case 2:
				$a=iconv("windows-1251","utf-8",$a);
				break;
		}
		return $a;
	}



	private function getErrorText($errorId){

		$errorId = (int)$errorId;

		switch( $errorId ){
			case 100:
				$errorText = 'Incorrect format proxy. Example: 195.170.223.227:8080';
			break;
			case 101:
				$errorText = 'Incorrect data for proxy authentication. Example: RUS210427:5Ae2SzDJX9';
			break;
			case 102:
				$errorText = 'Incorrect data refer. Example: https://www.google.com';
			break;
			case 103:
				$errorText = 'Variable must be an array. Example: array("key"=>"value")';
			break;
			case 104:
				$errorText = 'Variable setBrowser() must be a string. Example: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36';
			break;
			case 105:
				$errorText = 'Incorrect url in setUrl. Example: https://www.google.com';
			break;
			case 106:
				$errorText = 'You must specify the url to parse. Example: setUrl( "http://www.google.com/" )';
			break;
			case 107:
				$errorText = 'Variable $httpHeader must be an array. Example: array("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8","Accept-Language: ru-ru,ru;q=0.8,en-us;q=0.5,en;q=0.3","Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7")';
			break;
			case 108:
				$errorText = 'Variable setPathCookies() must be a string. Example: /usr/local/www/cookies.txt';
			break;

			case 200:
				$errorText = 'The data for the parser are empty or do not exist. Example: $parserSites->getData("< p >Example< /p >"); $parserSites->lego("2(#)p");';
			break;



		}


		print($errorText);
	}


}