<?php
    require_once 'config.php';
    require_once 'router.php';
    require_once './src/controllers/controller.php';
    require_once './src/controllers/user.php';

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

    // Routes pour gerer les amis ici
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

    post('/api/create-objectif/create', function() {
        Controller::createObjectif();
    });

    get('/api/get-objectifs/$id', function($id) {
        Controller::getObjectifs($id);
    });

    put('/api/update-objectif/$id', function($id) {
        Controller::updateObjectif($id);
    });

    delete('/api/delete-objectif/$id', function($id) {
        Controller::deleteObjectif($id);
    });

    //Routes pour gerer l'utilisateur
    get('/api/get-user/$id', function($id) {
        User::getUser($id);
    });

    post('/api/creer-tache', function(){
        Controller::addTache();
    });

    get('/api/get-taches/$id', function($id){
        Controller::getTaches($id);
    });