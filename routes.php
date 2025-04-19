<?php
    require_once 'config.php';
    require_once 'router.php';
    require_once './src/controllers/controller.php';
    require_once './src/controllers/user.php';
    require_once './src/controllers/convo.php';
    require_once './src/controllers/friend.php';
    require_once './src/controllers/message.php';
    require_once './src/controllers/objectif.php';
    require_once './src/controllers/session.php';
    require_once './src/controllers/stats.php';
    require_once './src/controllers/taches.php';


    use Firebase\JWT\JWT;

    post('/api/login',function(){
        Controller:: login();
    });
    
    post('/api/register', function() {
        Controller:: register();
    });
    
    get('/api/convo/$id', function($id){
        convo:: getConvo($id);
    });

    post('/api/convo/new', function(){
        convo:: newConvo();
    });

    get('/api/convo/messages/$id', function($convoID){
        message:: getMessage($convoID);
    });

    post('/api/convo/messages/new', function(){
        message:: newMessage();
    });

    // Routes pour gerer les amis ici
    post('/api/friend-request/send', function() {
        friend::sendFriendRequest();
    });

    get('/api/friend-requests/$userId', function($userId){
        friend::getFriendRequests($userId);
    });

    put('/api/friend-request/$requestId', function($requestId){
        friend::updateFriendRequest($requestId);
    });

    get('/api/friend-list/$userId', function($userId){
        friend::getFriendList($userId);
    });

    get('/api/stats/$id', function($id){
        stats::getStatistics($id);
    });

    post('/api/session/enregistrer', function(){
        session::addSession();
    });

    get('/api/session/$id', function($id){
        session::getNombreSession($id);
    });

    post('/api/create-objectif/create', function() {
        objectif::createObjectif();
    });

    get('/api/get-objectifs/$id', function($id) {
        objectif::getObjectifs($id);
    });

    put('/api/update-objectif/$id', function($id) {
        objectif::updateObjectif($id);
    });

    delete('/api/delete-objectif/$id', function($id) {
        objectif::deleteObjectif($id);
    });

    //Routes pour gerer l'utilisateur
    get('/api/get-user/$id', function($id) {
        User::getUser($id);
    });

    post('/api/creer-tache', function(){
        taches::addTache();
    });

    get('/api/get-taches/$id', function($id){
        Controller::getTaches($id);
    });

    post('/api/notes/save', function() {
        Controller::saveNote();
    });