<?php
namespace plugins;
class Setu{
    // 插件消息
    public static $pluginInfo = [
        'name' => '来份setu',
        'version' => '1.0.0',
        'author' => 'FlxSNX'
    ];

    public static $setuApi = "https://api.lolicon.app/setu/v2?";

    public static $defaultParam = [
        'proxy' => 'https://pixiv.oacg.workers.dev',
        'size' => 'regular'
    ];

    // 私聊消息事件
    public static function private_message($request){
        if($request->message == 'setu'){
            return cqhttp('send_private_msg',[
                'user_id' => $request->user_id,
                'message' => '[CQ:reply,id='.$request->message_id.']'.self::getSetu()
            ]);
        }
    }

    // 群聊消息事件
    public static function group_message($request){
        if($request->message == 'setu'){
            return cqhttp('send_group_msg',[
                'group_id' => $request->group_id,
                'message' => '[CQ:reply,id='.$request->message_id.']'.self::getSetu()
            ]);
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