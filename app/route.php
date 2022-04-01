<?php
use FlxPHP\Route;

Route::get('/',function(){
    return view("index/index");
});

// Route::get('/','cqhttp@test');

// 处理机器人上报消息
Route::post('/','cqhttp@index');

Route::get('console','console@index');
Route::post('console/pluginSetings/save','console@pluginSetingsSave');
Route::get('console/getMsg','console@getMsg');