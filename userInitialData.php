<?php

require_once 'config.php'; // Inclua o arquivo de configuração
session_start();

// Função para buscar as permissões do utilizador
function getPermissions($idUser, $pdo) {

    //$pdo = $conn;

    $query = "
        SELECT p.id, p.name, p.description, p.icon, p.isMenu 
        FROM PERMISSIONS_USER AS pu
        JOIN PERMISSIONS AS p ON pu.idPermission = p.id  
        WHERE pu.idUser = :idUser
    ";

    $permissions = [];
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':idUser', $idUser, PDO::PARAM_INT);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $permissions[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'icon' => $row['icon'],
            'isMenu' => (bool)$row['isMenu']
        ];
    }
    return $permissions;
}

function getClub($idClub, $pdo) {
    //$pdo = $conn;

    $query = "SELECT * FROM CLUB WHERE id = :idClub";
    $club = null;

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':idClub', $idClub, PDO::PARAM_INT);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $club = [
            'id' => (int)$row['id'],
            'idOwner' => (int)$row['idOwner'],
            'name' => $row['name'],
            'abbreviation' => $row['abbreviation']
        ];
    }
    return $club;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Receber os dados JSON da requisição
        //$jsonData = file_get_contents('php://input');
        //$data = json_decode($jsonData);

        if (isset($_SESSION['id'])) {
            $pdo = $conn;
            // Definir a query para buscar informações do usuário
            $query = "SELECT * FROM USER WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $_SESSION['id'], PDO::PARAM_INT);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Criar objeto usuário com os dados obtidos
                $userObj = [
                    'id' => (int)$row['id'],
                    'idClub' => (int)$row['idClub'],
                    'username' => $row['username'],
                    'name' => $row['name'],
                    'surname' => $row['surname'],
                    'birthdate' => $row['birthdate'],
                    'email' => $row['email'],
                    'phone' => $row['phone'],
                    'role' => (int)$row['role'],
                    'photo' => $row['photo'],
                    'permissions' => [],
                    'clubInfo' => null
                ];

                // Buscar as permissões do utilizador
                $getUserPermissions = getPermissions($_SESSION['id'], $pdo);
                if (!empty($getUserPermissions)) {
                    $userObj['permissions'] = $getUserPermissions;

                    // Se o utilizador pertence a um clube, buscar as informações do clube
                    if ($_SESSION['idClub'] != null) {
                        $getUserClub = getClub($_SESSION['idClub'], $pdo);
                        if (!empty($getUserClub)) {
                            $userObj['clubInfo'] = $getUserClub;
                        } else {
                            echo json_encode(['error' => 'Erro ao acessar as informações do clube do utilizador.']);
                            http_response_code(500);
                            exit;
                        }
                    }

                    // Responder com os dados do utilizador
                    echo json_encode(['user' => $userObj]);
                    http_response_code(200);
                } else {
                    echo json_encode(['error' => 'Erro ao acessar as permissões do utilizador.']);
                    http_response_code(500);
                }
            } else {
                echo json_encode(['error' => 'Utilizador não encontrado.']);
                http_response_code(404); // 404 Not Found
            }
        } else {
            echo json_encode(['error' => 'Dados inválidos.']);
            http_response_code(400); // 400 Bad Request
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        http_response_code(500);
    }
}
?>