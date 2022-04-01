<?php
namespace app\controller;
class cqhttpHelper{
    private static $CFG;
    private static $API;

    public static function __init(){
        global $Flx;
        self::$CFG = $Flx->_CFG['cq'];
        self::$API = self::$CFG['API'];
    }

    public static function send_msg($user_id,$message,$auto_escape=false){
        self::__init();

        $action = '/send_private_msg?';
        $param = http_build_query([
            'user_id' => $user_id,
            'message' => $message,
            'auto_escape' => $auto_escape
        ]);
        $result = curl(self::$API.$action.$param);
        var_dump($result);
        return $result;
    }

}