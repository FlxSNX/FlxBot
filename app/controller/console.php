<?php
namespace app\controller;
use extend\DB;
class console{
    private $plugins = [];
    public function __init(){
        $this->loadPlugins();
    }

    public function index(){
        $result = json_decode(cqhttp("get_login_info",[]));
        $bot = $result;
        $result = json_decode(cqhttp("get_group_list",[]));
        $groups = $result->data;
        assign([
            'bot' => $bot->data,
            'plugins' => $this->plugins,
            'groups' => $groups
        ]);
        view();
    }

    public function pluginSetingsSave(){
        $save = file_put_contents(APPDIR.'plugins.json',json_encode(authstr($_POST)));
        if($save){
            return '{"status":200,"msg":"插件设置保存成功"}';
        }else{
            return '{"status":-400,"msg":"插件设置保存失败"}';
        }
    }

    public function getMsg(){  
        $msgfile = file_get_contents(ROOTDIR.'log/'.date("Y-m-d H").'-msg.log');
        $msg = explode(PHP_EOL,$msgfile);
        if($msg){
            $msg = array_reverse(array_slice($msg,-21,-1));
            return json_encode($msg);
        }else{
            return '[]';
        }
    }

    private function loadPlugins(){
        $pluginsConf = json_decode(file_get_contents(APPDIR.'plugins.json'),true);
        $plugins = glob(APPDIR.'plugins/*.php');
        if($plugins){
            foreach($plugins as $file){
                $filename = end(explode('/',$file));
                $class = '\plugins\\'.explode('.',$filename)[0];
                $this->plugins[explode('.',$filename)[0]] = ['info' => $class::$pluginInfo,'enable' => $pluginsConf[explode('.',$filename)[0]] ?? false];
            }
        }
    }
}