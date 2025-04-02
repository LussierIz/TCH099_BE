<?php
    require_once 'config.php';
    require_once 'router.php';
    require_once './src/controllers/controller.php';

    use Firebase\JWT\JWT;

    post('/api/login',function(){
        Controller:: login();
    });
    
    post('/api/register', function() {
        Controller:: register();
    });
    
    get('/api/convo/$id', function($id){
        Controller:: getConvo($id);
    });

    post('/api/convo/new', function(){
        Controller:: newConvo();
    });

    get('/api/convo/messages/$id', function($convoID){
        Controller:: getMessage($convoID);
    });

    post('/api/convo/messages/new', function(){
        Controller:: newMessage();
    });

    post('/api/friend-request/send', function() {
        Controller::sendFriendRequest();
    });