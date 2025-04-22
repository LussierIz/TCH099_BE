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
    require_once './src/controllers/note.php';
    require_once './src/controllers/devoirs.php';
    require_once './src/controllers/boutique.php';
    require_once './src/controllers/citations.php';
    require_once './src/controllers/leaderboard.php';

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

    get('/api/convo/search/$id', function($id){
        $query = $_GET['q'] ?? '';
        convo::searchConvo($id, $query);
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
        taches::getTaches($id);
    });

    put('/api/set-statut', function() {
        taches::setStatut();
    });

    post('/api/notes/save', function() {
        note::saveNote();
    });

    get('/api/notes/$id_utilisateur', function($id_utilisateur) {
        note::getNotes($id_utilisateur);
    });

    put('/api/notes/update/$id', function($id_note) {
        note::updateNote($id_note);
    });

    DELETE('/api/notes/delete/$id', function($id_note) {
        note::deleteNote($id_note);
    });

    post('/api/create-devoir/create', function() {
        devoirs::createDevoir();
    });

    get('/api/get-devoirs/$id', function($id) {
        devoirs::getDevoirs($id);
    });

    put('/api/update-devoir/$id', function($id) {
        devoirs::updateDevoir($id);
    });

    delete('/api/delete-devoir/$id', function($id) {
        devoirs::deleteDevoir($id);
    });

    get('/api/get-devoirs-envoyes/$id', function($id) {
        devoirs::getDevoirsEnvoyes($id);
    });

    post('/api/share-devoir/$id', function($id) {
        devoirs::shareDevoir($id);
    });

    get('/api/get-devoirs-recus/$id', function($id) {
        devoirs::getDevoirsRecus($id);
    });

    // routes pour gerer la boutique
    get('/api/shop/$userId', function($userId) {
        Boutique::getBoutiqueItems($userId);
    });

    post('/api/shop/buy/$userId/$prodId', function($userId, $prodId) {
        Boutique::buyItem((int)$userId, (int)$prodId);
    });

    get('/api/shop/bought/$userId', function($userId) {
        Boutique::getBoughtItems($userId);
    });

    // route quote
    get('/api/get-random-quote', function() {
        citations::getRandomQuote();
    });

    //route leaderboard
    get('/api/friends/weekly-hours/$userId', function($userId){
        Leaderboard::getWeeklyHours($userId);
    });
    
    
    
