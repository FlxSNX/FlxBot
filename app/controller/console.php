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
        $groups = $this->loadGroups();
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

    public function groupSetingsSave(){
        $group = post('group_id');
        $change = post('change');
        if($group && $change){
            $groupsConf = json_decode(file_get_contents(APPDIR.'groups.json'),true);
            if($change == 'true' && $groupsConf){
                $groupsConf[$group] = true;
            }else{
                $groupsConf[$group] = false;
            }
            $save = file_put_contents(APPDIR.'groups.json',json_encode($groupsConf));
            if($save){
                return '{"status":200,"msg":"群聊设置保存成功"}';
            }else{
                return '{"status":-400,"msg":"群聊设置保存失败"}';
            }
        }else{
            return '{"status":-400,"msg":"参数错误"}';
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

    // 加载插件信息
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

    // 加载群聊配置&信息
    private function loadGroups(){
        $groupsConf = json_decode(file_get_contents(APPDIR.'groups.json'),true);
        $result = json_decode(cqhttp("get_group_list",[]));
        $groupsObj = $result->data;
        $groups = [];
        if($groupsObj){
            foreach($groupsObj as $group){
                $groups[$group->group_id] = [
                    'info' => $group,
                    'enable' => $groupsConf[$group->group_id] ?: false
                ];
            }
        }

        return $groups;
    }
}