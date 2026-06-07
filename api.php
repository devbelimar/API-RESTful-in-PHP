<?php

//DEFINIR O CABEÇALHO PARA JSON
header('Content-Type: application/json; charset=utf-8');

//PERMITIR CORS (PARA O NAVEGADOR ACEITAR A COMUNICAÇÃO)
header('Access-Control-Allow-Origin: *');
//MÉTODOS PERMITIDOS PELO SERVIDOR
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
//Libera os cabeçalhos que o frontend pode enviar
header('Access-Control-Allow-Headers: Content-Type');

//Intercepta o Preflight do navegador (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') :
    http_response_code(200);
    exit();
endif;

//OBTER MÉTODO HTTP (GET, POST, PUT, ETC...)
$method = $_SERVER['REQUEST_METHOD'];

//OBTER OS DADOS ENVIADOS (para POST/PUT)
$input = json_decode(file_get_contents('php://input'), true);

//BD EM MEMÓRIA (SIMULADA)
$users = [
    ['id' => 1, 'nome' => 'Belimar', 'email' => 'belimar@gmail.com'],
    ['id' => 2, 'nome' => 'Ana', 'email' => 'ana@gmail.com'],
    ['id' => 3, 'nome' => 'Abel', 'email' => 'abel@gmail.com']
];

//RESPOSTA PADRÃO
$response = [
    'status' => 'success',
    'message' => '',
    'data' => null
];

//VERIFICAR SE A REQUISIÇÃO ESTÁ VAZIA OU SE O JSON ESTÁ MAL FORMADO
/*if(json_last_error() !== JSON_ERROR_NONE || empty($input)) :
    http_response_code(400); // Bad Request
    $response['status'] = "Error";
    $response['message'] = "Formato JSON inválido ou vazio";
    echo json_encode($response);
    exit();
endif;*/

//VERIFICANDO QUAL O MÉTODO UTILIZADO
switch($method):
    case 'GET':
        //EXEMPLO: DEVOLVE UMA LISTA DE UTILIZADORES;
            $id = $_GET['id'] ?? null;

            if($id):
                //BUSCAR USER POR ID
                $encontrado = null;
                foreach ($users as $user) :
                    if($user['id'] == $id):
                        $encontrado = $user;
                    endif;
                endforeach;
                
                if($encontrado) :
                    $response['message'] = 'Usuário encontrado';
                    $response['data'] = $encontrado;
                else :
                    $response['status'] = 'error';
                    $response['message'] = 'Usuário não encontrado';
                endif;
            else :
                //LISTAR TODOS
                $response['message'] = 'Lista de Usuário';
                $response['data'] = $users;
            endif;    
        break;
    
    case 'POST':
        //EXEMPLO: RECEBER DADOS DO FRONTEND
        $nome = $input['nome'] ?? '';        
        $email = $input['email'] ?? '';      
        
        if(empty($nome) || empty($email)):
            $response['status'] = 'error';
            $response['message'] = 'Nome e Email são obrigatórios';
        else:
            $novoId = end($users)['id'] + 1;
            $novoUser = [
                'id' => $novoId,
                'nome' => $nome,
                'email' => $email
            ];

            $response['message'] = 'Usuário criado com sucesso!';
            $response['data'] = $novoUser;
        endif;
        break;

    case 'PUT':
        //EXEMPLO:ATUALIZAR USER
        $id = $input['id'] ?? null;
        $nome = $input['nome'] ?? '';
        $email = $input['email'] ?? '';

        if(!$id):
            $response['status'] = 'error';
            $response['message'] = 'ID é obrigatório para atualizar';
        elseif(empty($nome) || empty($email)):
            $response['status'] = 'error';
            $response['message'] = 'Forneça pelo menos nome ou email para atualizar';
        else:
            // SIMULANDO ATUALIZAÇÃO
            $atualizado = false;
            foreach($users as $user):
                if($user['id'] == $id):
                    if(!empty($nome)) $user['nome'] = $nome;
                    if(!empty($email)) $user['email'] = $email; 

                    $atualizado = true;
                    $response['data'] = $user;
                    break;
                endif;
            endforeach;

            if($atualizado):
                $response['message'] = 'Usuário atualizado com sucesso';
            else:
                $response['status'] = 'error';
                $response['message'] = 'Usuário não encontrado';
            endif;
        endif;
        break;

    case 'DELETE':
        $id = $input['id'] ?? null;
        
        if(!$id):
            $response['status'] = 'error';
            $response['message'] = 'ID é obrigatório para apagar';
        else:
            //SIMULAR REMOÇÃO
            $apagando = false;

            foreach($users as $key => $user):
                if($user['id'] == $id) :
                    unset($users[$key]);
                    $apagando = true;
                    break;
                endif;
            endforeach;

            if ($apagando):
                $response['message'] = 'Usuário apagado com sucesso';
                $response['data'] = ['id' => $id];
            else:
                $response['status'] = 'error';
                $response['message'] = 'Usuário não encontrado';
            endif;
        endif;
        break;

    default:
        $response['status'] = 'error';
        $response['message'] = 'Método não suportado';

endswitch;

//DEVOLVE RESPOSTA EM JSON
//JSON_UNESCAPED_UNICODE: PARA NÃO CONVERTER OS CARACTERES ACENTUADOS
//JSON_PRETTY_PRINT: FORMATA O JSON COM QUEBRAS DE LINHA E RECUOS(ESPAÇOS)
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

?>