<?php
// model/dao/LoginDAO.php

require_once __DIR__ . '/Conexao.php'; 

class LoginDAO {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Conexao::getConexao();
        } catch (Exception $e) {
            die("Erro de Conexão: " . $e->getMessage());
        }
    }

    /**
     * Autentica o utilizador verificando email e senha_hash.
     * 
     * @param string $email
     * @param string $senha
     * @return array|false Dados do utilizador, código de bloqueio ou false para credenciais inválidas.
     */
    public function autenticar($email, $senha) {
        try {
            // Seleciona os dados do utilizador e a hash do login
            $sql = "SELECT u.id_usuario, u.nome, u.perfil_id, u.id_academia, u.status, l.senha_hash
                    FROM usuario u
                    JOIN login l ON u.id_usuario = l.id_usuario
                    WHERE u.email = :email";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // 1. Verificar se o utilizador existe
            if (!$usuario) {
                return false; // Email não encontrado
            }

            // 2. Verificar a senha antes de informar o status da conta.
            if (!password_verify($senha, $usuario['senha_hash'])) {
                return false;
            }

            if ($usuario['status'] === 'INATIVO') {
                return ['erro_login' => 'usuario_inativo'];
            }
            if ($usuario['status'] === 'SUSPENSO') {
                return ['erro_login' => 'usuario_suspenso'];
            }
            if ($usuario['status'] !== 'ATIVO') {
                return false;
            }

            unset($usuario['senha_hash'], $usuario['status']);
            return $usuario;

        } catch (PDOException $e) {
            error_log("Erro no LoginDAO::autenticar - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o campo ultimo_login na tabela login.
     * 
     * @param int $idUsuario
     */
    public function atualizarUltimoLogin($idUsuario) {
        try {
            $sql = "UPDATE login SET ultimo_login = NOW() WHERE id_usuario = :id_usuario";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario);
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Erro ao atualizar ultimo_login: " . $e->getMessage());
        }
    }
}
?>