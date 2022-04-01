<?php
namespace app\controller;
class cqhttp{
    private $plugins = [];

    // 初始化
    public function __init(){
        $this->loadPlugins();
    }

    // 获取上报消息
    private function request(){
        $request = file_get_contents('php://input');
        $request_json = json_decode($request);
        if($request_json)return $request_json;
    }

    // 加载插件
    private function loadPlugins(){
        $plugins = json_decode(file_get_contents(APPDIR.'plugins.json'),true);
        if($plugins){
            foreach($plugins as $plugin=>$enable){
                if($enable == true){
                    $class = '\plugins\\'.$plugin;
                    include_once APPDIR.'plugins/'.$plugin;
                    $this->plugins[] = $class;
                }
            }
        }
    }

    // 调用插件方法
    public function callPlugin($method,$request){
        if($this->plugins){
            foreach($this->plugins as $plugin){
                if(method_exists($plugin,$method)){
                    $plugin::$method($request);
                }
            }
        }
    }

    public function test(){
        var_dump(\plugins\Test::private_msg($request));
    }

    // 处理机器人上报消息
    public function index(){
        $request = $this->request();
        if($request->meta_event_type == 'heartbeat')return file_put_contents(ROOTDIR.'log/heartbeat.log','['.date("Y-m-d H:i:s").']heartbeat'.PHP_EOL,FILE_APPEND | LOCK_EX);
        if($request && $request->message_type){
            switch($request->message_type){
                case 'private':
                    $this->callPlugin("private_message",$request);
                    file_put_contents(ROOTDIR.'log/'.date("Y-m-d H").'-msg.log','['.date("Y-m-d H:i:s").'][私聊消息]('.$request->user_id.')'.$request->sender->nickname.':'.$request->message.PHP_EOL,FILE_APPEND | LOCK_EX);
                break;

                case 'group':
                    if($request->sub_type == "normal"){
                        $this->callPlugin("group_message",$request);
                        file_put_contents(ROOTDIR.'log/'.date("Y-m-d H").'-msg.log','['.date("Y-m-d H:i:s").'][群聊消息]['.$request->group_id.']('.$request->user_id.')'.$request->sender->nickname.':'.$request->message.PHP_EOL,FILE_APPEND | LOCK_EX);
                    }
                break;
            }
        }
    }
}