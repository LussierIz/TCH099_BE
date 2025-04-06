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

    get('/api/friend-requests/$userId', function($userId){
        Controller::getFriendRequests($userId);
    });

    put('/api/friend-request/$requestId', function($requestId){
        Controller::updateFriendRequest($requestId);
    });

    get('/api/friend-list/$userId', function($userId){
        Controller::getFriendList($userId);
    });

    get('/api/stats/$id', function($id){
        Controller::getStatistics($id);
    });

    post('/api/session/enregistrer', function(){
        Controller::addSession();
    });

    get('/api/session/$id', function($id){
        Controller::getNombreSession($id);
    });