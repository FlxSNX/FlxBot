<?php
function curl($url,$post=[],$cookie=false,$header=false,$split=false,$referer=false){
	$ch = curl_init();
	if($header){
		curl_setopt($ch,CURLOPT_HEADER, 1);
	}else{
		curl_setopt($ch,CURLOPT_HEADER, 0);
	}
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.62 Safari/537.36');
	if($post){
		curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post));
    }
    if($cookie){
		curl_setopt($ch, CURLOPT_COOKIE,$cookie);
    }
    if($referer){
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    }
	$result = curl_exec($ch);
	if($split){
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($result, 0, $headerSize);
		$body = substr($result, $headerSize);
		$result=array();
		$result['header']=$header;
		$result['body']=$body;
	}
	curl_close($ch);
	return $result;
}

function cqhttp($action,$param){
	global $Flx;
	$cfg = $Flx->_CFG['cq'];
	$result = curl($cfg['API'].'/'.$action.'?'.http_build_query($param));
	return $result;
}

/** 
 *  先回应机器人的消息上报 再执行后续代码 防止超时造成多次请求
 *  仅在Windows Apchea 上测试 Linux以及Nginx兼容性未知 -- 后续测试
 * */ 
function cqret($request,\Closure $callback){
	ob_end_clean();
	header("Connection: close");
	header("HTTP/1.1 200 OK");
	ob_start();
	return $callback($request);
	$size=ob_get_length();
	header("Content-Length: $size");
	ob_end_flush();
	ob_flush();
	flush();
	set_time_limit(0);
}

// Cookie加密解密
function cookie_crypt($data,$secret,$action='encode'){
	if($action == 'encode'){
		$cookie = [
			'data' => $data,
			'sign' => base64_encode(hash_hmac('sha256',json_encode($data,JSON_UNESCAPED_UNICODE),$secret,true))
		];
		return base64_encode(json_encode($cookie,JSON_UNESCAPED_UNICODE));
	}else if($action == 'decode'){
		$data = base64_decode($data);
		if(!$data)return false;
		$data = json_decode($data,true);
		$sign = base64_encode(hash_hmac('sha256',json_encode($data['data'],JSON_UNESCAPED_UNICODE),$secret,true));
		if($sign === $data['sign']){
			return $data;
		}else{
			return false;
		}
	}else{
		return false;
	}
}

/* 随机排序二维数组 */
function shuffle_assoc($list) {  
	if (!is_array($list)) return $list;  
	$keys = array_keys($list);  
	shuffle($keys);  
	$random = array();  
	foreach ($keys as $key)  
	$random[$key] = shuffle_assoc($list[$key]);  
	return $random;  
 }