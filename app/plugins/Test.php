<?php
namespace plugins;
class Test{
    // 插件信息
    public static $pluginInfo = [
        'name' => '测试插件',
        'version' => '1.0.0',
        'author' => 'FlxSNX'
    ];

    // 加载插件事件
    public static function plugin(){
        return [
            'name' => '测试插件',
            'version' => '1.0.0',
            'author' => 'FlxSNX'
        ];
    }

    // 私聊消息事件
    public static function private_message($request){

        return cqhttp('send_private_msg',[
            'user_id' => $request->user_id,
            'message' => '[CQ:reply,id='.$request->message_id.']'.$request->message
        ]);
        
    }

    // 群聊消息事件
    public static function group_message($request){
        if($request->user_id == "211154860"){
            return cqhttp('send_group_msg',[
                'group_id' => $request->group_id,
                'message' => '[CQ:reply,id='.$request->message_id.']'.$request->message
            ]);
        }
    }
}