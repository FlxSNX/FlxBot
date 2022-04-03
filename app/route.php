<?php
use FlxPHP\Route;

Route::get('/',function(){
    return view("index/index");
});

// 处理机器人上报消息
Route::post('/','cqhttp@index');

Route::get('console','console@index');
Route::post('console/pluginSetings/save','console@pluginSetingsSave');
Route::post('console/groupSetings/save','console@groupSetingsSave');
Route::get('console/getMsg','console@getMsg');