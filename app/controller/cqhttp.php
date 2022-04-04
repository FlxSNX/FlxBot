<?php
namespace app\controller;
class cqhttp{
    private $plugins = [];
    private $groupsConf = [];

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
                    if(method_exists($plugin,'plugin')){
                        $plugin::plugin();
                    }
                    $this->plugins[] = $class;
                }
            }
        }
    }

    // 加载群聊配置
    private function loadGroupConf(){
        $groupsConf = json_decode(file_get_contents(APPDIR.'groups.json'),true);
        $this->groupsConf = $groupsConf;
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

    // 处理机器人上报消息
    public function index(){
        $request = $this->request();
        if($request->meta_event_type == 'heartbeat'){
            ob_end_clean();
            header("Connection: close");
            header("HTTP/1.1 200 OK");
            ob_start();
            $this->callPlugin("cron",$request);
            return file_put_contents(ROOTDIR.'log/heartbeat.log','['.date("Y-m-d H:i:s").']heartbeat'.PHP_EOL,FILE_APPEND | LOCK_EX);
            $size=ob_get_length();
            header("Content-Length: $size");
            ob_end_flush();
            ob_flush();
            flush();
            set_time_limit(0);
        }
        if($request && $request->message_type){
            switch($request->message_type){
                case 'private':
                    $this->callPlugin("private_message",$request);
                    file_put_contents(ROOTDIR.'log/'.date("Y-m-d H").'-msg.log','['.date("Y-m-d H:i:s").'][私聊消息]('.$request->user_id.')'.$request->sender->nickname.':'.$request->message.PHP_EOL,FILE_APPEND | LOCK_EX);
                break;

                case 'group':
                    if($request->sub_type == "normal"){
                        $this->loadGroupConf();
                        if($this->groupsConf[$request->group_id] == true){
                            $this->callPlugin("group_message",$request);
                            file_put_contents(ROOTDIR.'log/'.date("Y-m-d H").'-msg.log','['.date("Y-m-d H:i:s").'][群聊消息]['.$request->group_id.']('.$request->user_id.')'.$request->sender->nickname.':'.$request->message.PHP_EOL,FILE_APPEND | LOCK_EX);
                        }else{
                            // cqhttp('send_group_msg',[
                            //     'group_id' => $request->group_id,
                            //     'message' => '[CQ:reply,id='.$request->message_id.']'."Bot未对此群开放"
                            // ]);
                            file_put_contents(ROOTDIR.'log/'.date("Y-m-d H").'-msg.log','['.date("Y-m-d H:i:s").'][未授权的群聊消息]['.$request->group_id.']('.$request->user_id.')'.$request->sender->nickname.':'.$request->message.PHP_EOL,FILE_APPEND | LOCK_EX);
                        } 
                    }
                break;
            }
        }
    }
}