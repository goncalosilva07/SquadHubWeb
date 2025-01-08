<?php

class Notification{
    
    private $pdo;
    private $table_name = "NOTIFICATIONS";

    public function __construct($db){
        $this->pdo = $db;
    }

    function getNotifications($data){
        try {
            if (isset($_SESSION['id'])) {

                $notificationList = [];
    
                $query = "SELECT * FROM NOTIFICATIONS WHERE idUser = ? and isActive = ?
                        ORDER BY id DESC;";
    
                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($query);
    
                    // Atribuir valores aos parâmetros
                    $stmt->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
                    $stmt->bindValue(2, true, PDO::PARAM_BOOL);
    
                    // Executar a query
                    $stmt->execute();
    
                    // Buscar os resultados
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $notification = [
                            'id' => (int)$row['id'],
                            'idClub' => (int)$row['idClub'],
                            'title' => $row['title'],
                            'description' => $row['description'],
                            'isInvite' => (bool)$row['isInvite']
                        ];
    
                        // Adicionar o jogo à lista
                        $notificationList[] = $notification;
                    }
    
                    return ["code" => 200, "notificationList" => $notificationList];
    
                } catch (PDOException $e) {
                    return ["code" => 500, "message" => $e->getMessage()];
                }
    
            } else {
                return ["code" => 400, "message" => "Dados inválidos fornecidos."];
            }
    
        } catch (Exception $e) {
            return ["code" => 500, "message" => $e->getMessage()];
        }
    }

    function acceptInvite($data){
        try {
            if (isset($_SESSION['id']) && isset($data->id)) {
    
                $query = "SELECT * FROM NOTIFICATIONS WHERE id = ? and idUser = ?;";
    
                //Query par averificar se o administrador é dono de um clube
                $checkUserClubOwnerSql = "SELECT * FROM CLUB WHERE idOwner = ?;";

                //id da notificacao
                $id = filter_var($data->id, FILTER_VALIDATE_INT);
    
                try {

                    //Verifica se o utilizador é dono de um clube. Se for não é possível aceitar o convite.
                    $stmt = $this->pdo->prepare($checkUserClubOwnerSql);
                    $stmt->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
                    $stmt->execute();

                    $row = $stmt->fetch();

                    if ($row){
                        return ["code" => 400, "message" => "Impossível aceitar o convite pois é dono de um clube."];
                    }
          
                    // Preparar a query
                    $stmt = $this->pdo->prepare($query);
    
                    // Atribuir valores aos parâmetros
                    /*
                    $stmt->bindParam(1, $id, PDO::PARAM_INT);
                    $stmt->bindValue(2, true, PDO::PARAM_BOOL);
                    $stmt->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
                    */
                    $stmt->bindParam(1, $id, PDO::PARAM_INT);
                    $stmt->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
    
    
                    // Executar a query
                    $stmt->execute();
    
                    //if ($stmt->fetchColumn() > 0){
                    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    
                        //$row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $idClub = $row['idClub'];
    
                        $updateUserClubSql = "UPDATE USER SET idClub = ? WHERE id = ?;";
    
                        $stmt = $this->pdo->prepare($updateUserClubSql);
    
                        // Atribuir valores aos parâmetros
                        $stmt->bindParam(1, $idClub, PDO::PARAM_INT);
                        $stmt->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
    
                        // Executar a query
                        $stmt->execute();
    
                        if ($stmt->rowCount() > 0) {
                            $_SESSION['idClub'] = $idClub;
    
                            $updateUserNotificationSql = "UPDATE NOTIFICATIONS SET isActive = ? WHERE id = ? AND idUser = ?;";
    
                            $stmt = $this->pdo->prepare($updateUserNotificationSql);
    
                            $stmt->bindValue(1, false, PDO::PARAM_BOOL);
                            $stmt->bindParam(2, $id, PDO::PARAM_INT);
                            $stmt->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
    
                            $stmt->execute();
    
                            return ["code" => 200, "message" => "Convite aceite com sucesso."];
                        } else {
                            return ["code" => 400, "message" => "Erro ao processar o pedido. Dados inválidos."];
                        }
                    }else{
                        return ["code" => 400, "message" => "Notificação inválida."];
                    }
                } catch (PDOException $e) {
                    return ["code" => 500, "message" => $e->getMessage()];
                }
    
            } else {
                return ["code" => 400, "message" => "Dados inválidos fornecidos."];
            }
    
        } catch (Exception $e) {
            return ["code" => 500, "message" => $e->getMessage()];
        }
    }

    function deleteInvite($data){
        try {
            if (isset($_SESSION['id']) && isset($data->id)) {
    
                $query = "UPDATE NOTIFICATIONS SET isActive = ? WHERE id = ? AND idUser = ?;";
    
                //id da notificacao
                $id = filter_var($data->id, FILTER_VALIDATE_INT);
    
                try {
                    // Preparar a query
                    $stmt = $this->pdo->prepare($query);
    
                    $stmt->bindValue(1, false, PDO::PARAM_BOOL);
                    $stmt->bindParam(2, $id, PDO::PARAM_INT);
                    $stmt->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
    
                    // Executar a query
                    $stmt->execute();

                    return ["code" => 200, "message" => "Convite removido com sucesso."];
    
                } catch (PDOException $e) {
                    return ["code" => 500, "message" => $e->getMessage()];
                }
    
            } else {
                return ["code" => 400, "message" => "Dados inválidos fornecidos."];
            }
    
        } catch (Exception $e) {
            return ["code" => 500, "message" => $e->getMessage()];
        }
    }
}
?>