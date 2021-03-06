<?php
/**
 * FlxPHP
 * [应用错误提示页模板]
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title><?=$error['title']?></title>
    <style>
        *{
            margin: 0; 
            padding: 0;
        }

        html, body{
            width: 100%;
            height: 100%;
        }

        body{
            font-family:微软雅黑;
            background-color: #f8f8f8;
            color: #666;
        }

        .error{
            position:relative;
            width: 100%;
            height: 300px;
            top: 50%;
            margin-top: -150px; 
        }

        p.errorMsg{
            font-size: 16px;
        }

        p{
            text-align: center;
            font-size: 14px;
            font-weight: 100;
            padding: 5px;
        }

        p.errorcode{
            font-size: 36px;
            color: #333;
        }

        .reback{
            text-decoration: none;
            font-size: 12px;
            color: initial;
        }
    </style>
</head>
<body>
    <div class="error">
        <p class="errorcode"><?=$error['code']?></p>
        <p class="errorMsg"><?=$error['error']?></p>
        <?php if($error['info']){ ?>
        <p class="info"><?=$error['info']?></p>
        <?php } ?>
        <p><a class="reback" href="javascript:history.back(-1)">返回上一页</a></p>
    </div>
</body>
</html>