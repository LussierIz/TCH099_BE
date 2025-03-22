<?php
    require_once 'config.php';
    require_once 'router.php';
    require_once './src/controllers/controller.php';

    post('/api/login',function(){
        Controller:: login();
    });

    post('/api/register', function() {
        Controller:: register();
    });