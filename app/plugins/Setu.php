<?php
namespace plugins;
class Setu{
    // 插件信息
    public static $pluginInfo = [
        'name' => '来份setu',
        'version' => '1.0.0',
        'author' => 'FlxSNX'
    ];

    public static $conf = APPDIR.'pluginConf/Setu.json';

    public static $setuApi = "https://api.lolicon.app/setu/v2?";

    public static $defaultParam = [
        'proxy' => 'https://pixiv.oacg.workers.dev',
        'size' => 'regular'
    ];

    public static $request;

    // 私聊消息事件
    public static function private_message($request){
        if($request->message == 'setu' or (strpos($request->message,' ') and explode(' ',$request->message)[0] == "setu")){
            self::$request = $request;
            self::sendSetu();
        }
    }

    // 群聊消息事件
    public static function group_message($request){  
        if($request->message == 'setu' or (strpos($request->message,' ') and explode(' ',$request->message)[0] == "setu")){
            self::$request = $request;
            self::sendSetu('group');
        }
    }

    // 前置操作
    public static function prefix($action,$cqparam){
        $conf = json_decode(file_get_contents(self::$conf),true);

        $time = date("Hi");
        if(isset($conf[$time])){
            $conf[$time]++;
            if($conf[$time] > 6){
                $cqparam['message'] = '[CQ:reply,id='.self::$request->message_id.']'.'不要瑟瑟啦！先休息一会吧~';
                cqhttp($action,$cqparam);
                exit();
            }
        }else{
            $conf = [$time => 1];
        }

        file_put_contents(self::$conf,json_encode($conf));
    }

    private static function sendSetu($type="private"){
        if($type == "private"){
            $action = "send_private_msg";
            $cqparam = [
                'user_id' => self::$request->user_id
            ];
        }else if($type == "group"){
            $action = "send_group_msg";
            $cqparam = [
                'group_id' => self::$request->group_id
            ];
        }else{
            return false;
        }

        self::prefix($action,$cqparam);

        if(strpos(self::$request->message,' ')){
            $t = explode(' ',self::$request->message);
            if(count($t) == 2 && $t[0] == 'setu'){
                self::$defaultParam['tag'] = $t[1];
            }
        }

        self::$request->_cqparam = $cqparam;
        self::$request->_action = $action;
        $result2 = cqret(self::$request,function($request){
            $request->_cqparam['message'] = '[CQ:reply,id='.$request->message_id.']'.'[CQ:image,file=95bfc3d45e0582264c5906b61eb576d0.image,url=https://gchat.qpic.cn/gchatpic_new/1393528900/4199655691-2298073790-95BFC3D45E0582264C5906B61EB576D0/0?term=3,subType=1]';
            return cqhttp($request->_action,$request->_cqparam);
        });

        $cqparam['message'] = '[CQ:reply,id='.self::$request->message_id.']'.self::getSetu();
        $result = cqhttp($action,$cqparam);

        if($result || $result2){
            // 发送后15秒撤回
            $result = json_decode($result);
            $result2 = json_decode($result2);
            sleep(15);
            cqhttp('delete_msg',[
                'message_id' => $result->data->message_id
            ]);
            cqhttp('delete_msg',[
                'message_id' => $result2->data->message_id
            ]);
            if($type == "group"){
                cqhttp('delete_msg',[
                    'message_id' => self::$request->message_id
                ]);
            } 
        }
    }

    private static function getSetu(){
        $request = curl(self::$setuApi.http_build_query(self::$defaultParam));
        if($request){
            $message = $request;
            $request = json_decode($request);
            $setu = $request->data[0]->urls->regular;
            $setId = $request->data[0]->pid;
            $setTitle = $request->data[0]->title;
            if(!$setu){
                $message = "[setu]出错啦~";
            }else{
                $message = 'PixivID:.'.$setId.' - '.$setTitle.'.[CQ:image,file='.$setu.']';
            }
            return $message;
        }else{
            return '[setu]出错啦~';
        }
    }
}