<?php
require_once 'config.php';
session_start();

function searchById($array, $id) {
    foreach ($array as $key => $item) {
        if (isset($item['id']) && $item['id'] == $id) {
            return true;  // Retorna o índice do item
        }
    }
    return false; // ID não encontrado
}

if (isset($_SESSION['id'])) {
    try {
        // Recebe os dados JSON do corpo da requisição
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData);

        $route = $data->route;
        $pdo = $conn;

        /* ********************************** Routes ********************************** */

         /* *********************** User *********************** */
         if ($route === 'getUserData') {
   
            require_once './objects/User.php';
            $user = new User($pdo);

            $response = $user->getUserData($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['userData']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'updateUserData') {

            require_once './objects/User.php';
            $user = new User($pdo);

            $response = $user->updateUserData($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'logout') {

            require_once './objects/User.php';
            $user = new User($pdo);

            $response = $user->logout($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        /* *********************** Club *********************** */
        if ($route === 'createClub') {

            if (!searchById($_SESSION['permissions'], 1)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Club.php';
            $club = new Club($pdo);

            $response = $club->create($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;
        }

        /* *********************** Game *********************** */
        if ($route === 'getGames') {

            if (!searchById($_SESSION['permissions'], 2)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Game.php';
            $game = new Game($pdo);

            $response = $game->getGames($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['gamesList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'addGame') {

            if (!searchById($_SESSION['permissions'], 4)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Game.php';
            $game = new Game($pdo);

            $response = $game->addGame($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;
        }

        if ($route === 'deleteGame') {

            if (!searchById($_SESSION['permissions'], 5)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Game.php';
            $game = new Game($pdo);

            $response = $game->deleteGame($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;
        }

        if ($route === 'submitGameCall') {

            if (!searchById($_SESSION['permissions'], 23)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Game.php';
            $game = new Game($pdo);

            $response = $game->submitGameCall($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;
        }

        if ($route === 'getGameCallData') {

            require_once './objects/Game.php';
            $game = new Game($pdo);

            $response = $game->getGameCallData($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                if ($response['isCreated'] == true){
                    echo json_encode(array("playersList" => $response['playersList'], "isCreated" => $response['isCreated']), JSON_PRETTY_PRINT);
                }else{
                    echo json_encode(array("message" => $response['message'], "isCreated" => $response['isCreated']), JSON_PRETTY_PRINT);
                }
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'getGameStatistics') {

            if (!searchById($_SESSION['permissions'], 26)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Game.php';
            $game = new Game($pdo);

            $response = $game->getGameStatistics($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['gameStatistics']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        
        if ($route === 'submitGameStatistics') {

            if (!searchById($_SESSION['permissions'], 27)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Game.php';
            $game = new Game($pdo);

            $response = $game->submitGameStatistics($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;
        }

        if ($route === 'deleteScorer') {

            if (!searchById($_SESSION['permissions'], 27)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Game.php';
            $game = new Game($pdo);

            $response = $game->deleteScorer($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;
        }

        /* *********************** Player *********************** */
        if ($route === 'getPlayers') {

            if (!searchById($_SESSION['permissions'], 6)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->getPlayers($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['playersList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'invitePlayer') {

            if (!searchById($_SESSION['permissions'], 13)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->invitePlayer($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'deletePlayerFromClub') {

            if (!searchById($_SESSION['permissions'], 14)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->deletePlayerFromClub($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'getPlayerInjuries') {

            if (!searchById($_SESSION['permissions'], 19)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->getPlayerInjuries($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['injuryList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'addPlayerInjury') {

            if (!searchById($_SESSION['permissions'], 20)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->addPlayerInjury($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'deletePlayerInjury') {

            if (!searchById($_SESSION['permissions'], 21)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->deletePlayerInjury($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'getPlayerInjuryInfo') {

            if (!searchById($_SESSION['permissions'], 22)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->getPlayerInjuryInfo($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['injuryInfo']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'editPlayerInjury') {

            if (!searchById($_SESSION['permissions'], 22)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->editPlayerInjury($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'getPlayersWithoutInjury') {

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->getPlayersWithoutInjury($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['playersList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'getPlayerPerformance') {

            require_once './objects/Player.php';
            $player = new Player($pdo);

            $response = $player->getPlayerPerformance($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['performanceList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        /* *********************** Coach *********************** */
        if ($route === 'getCoach') {

            if (!searchById($_SESSION['permissions'], 7)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Coach.php';
            $coach = new Coach($pdo);

            $response = $coach->getCoach($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['coach']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'deleteCoachFromClub') {

            if (!searchById($_SESSION['permissions'], 16)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Coach.php';
            $coach = new Coach($pdo);

            $response = $coach->deleteCoachFromClub($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'inviteCoach') {

            if (!searchById($_SESSION['permissions'], 15)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Coach.php';
            $coach = new Coach($pdo);

            $response = $coach->inviteCoach($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        /* *********************** Notifications *********************** */
        if ($route === 'getNotifications') {

            if (!searchById($_SESSION['permissions'], 9)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Notification.php';
            $notification = new Notification($pdo);

            $response = $notification->getNotifications($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['notificationList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'acceptInvite') {

            require_once './objects/Notification.php';
            $notification = new Notification($pdo);

            $response = $notification->acceptInvite($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'deleteInvite') {

            require_once './objects/Notification.php';
            $notification = new Notification($pdo);

            $response = $notification->deleteInvite($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        /* *********************** Staff *********************** */
        if ($route === 'getStaff') {

            if (!searchById($_SESSION['permissions'], 8)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Staff.php';
            $staff = new Staff($pdo);

            $response = $staff->getStaff($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['staffList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'deleteStaffFromClub') {

            if (!searchById($_SESSION['permissions'], 18)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Staff.php';
            $staff = new Staff($pdo);

            $response = $staff->deleteStaffFromClub($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'inviteStaff') {

            if (!searchById($_SESSION['permissions'], 17)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Staff.php';
            $staff = new Staff($pdo);

            $response = $staff->inviteStaff($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        /* *********************** Training Sessions *********************** */
        if ($route === 'getTrainingSessions') {

            if (!searchById($_SESSION['permissions'], 10)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Training.php';
            $training = new Training($pdo);

            $response = $training->getTrainingSessions($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['trainingList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'addTrainingSession') {

            if (!searchById($_SESSION['permissions'], 11)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Training.php';
            $training = new Training($pdo);

            $response = $training->addTrainingSession($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'deleteTrainingSession') {

            if (!searchById($_SESSION['permissions'], 12)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Training.php';
            $training = new Training($pdo);

            $response = $training->deleteTrainingSession($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'getPlayersTrainingsRatings') {

            if (!searchById($_SESSION['permissions'], 24)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Training.php';
            $training = new Training($pdo);

            $response = $training->getPlayersTrainingsRatings($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['playerRatingList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'submitPlayersTrainingsRatings') {

            if (!searchById($_SESSION['permissions'], 25)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Training.php';
            $training = new Training($pdo);

            $response = $training->submitPlayersTrainingsRatings($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        /* *********************** Dashboard *********************** */
        if ($route === 'getDashboardInitialData') {

            require_once './objects/Dashboard.php';
            $dashboard = new Dashboard($pdo);

            $response = $dashboard->getDashboardInitialData($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['dashboardData']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        /* *********************** Injury *********************** */
        if ($route === 'getInjuries') {

            if (!searchById($_SESSION['permissions'], 19)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Injury.php';
            $injury = new Injury($pdo);

            $response = $injury->getInjuries($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['injuryList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        /* *********************** Admins *********************** */

        if ($route === 'getAdmins') {

            if (!searchById($_SESSION['permissions'], 30)){
                http_response_code(403);
                echo json_encode(array("message" => "Sem as permissões necessárias para executar o pedido."), JSON_PRETTY_PRINT);
                exit;
            }

            require_once './objects/Admin.php';
            $admin = new Admin($pdo);

            $response = $admin->getAdmins($data);

            if($response['code'] == 200){
                http_response_code($response['code']);
                echo json_encode($response['adminList']);
            }else{
                http_response_code($response['code']);
                echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            }
            exit;    
        }

        if ($route === 'deleteAdminFromClub') {

            require_once './objects/Admin.php';
            $admin = new Admin($pdo);

            $response = $admin->deleteAdminFromClub($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

        if ($route === 'inviteAdmin') {

            require_once './objects/Admin.php';
            $admin = new Admin($pdo);

            $response = $admin->inviteAdmin($data);

            header('Content-Type: application/json');
            http_response_code($response['code']);
            echo json_encode(array("message" => $response['message']), JSON_PRETTY_PRINT);
            exit;    
        }

    } catch (Exception $e) {
        // Erro geral na requisição
        http_response_code(500);
        echo json_encode(['message' => $e->getMessage()]);
    }

}else{
    http_response_code(400);
    echo json_encode(['message' => "Falha na autenticação."]);
}
?>
