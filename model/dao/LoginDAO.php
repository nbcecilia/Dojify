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
     * @return array|false Retorna os dados do utilizador ou false se falhar.
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

            // 2. Verificar se o status é 'ATIVO'
            if ($usuario['status'] !== 'ATIVO') {
                return false; // Utilizador existe mas não está ativo
            }

            // 3. Verificar a senha com a hash armazenada
            if (password_verify($senha, $usuario['senha_hash'])) {
                // Senha correta! Remove a hash da senha por segurança antes de retornar
                unset($usuario['senha_hash']);
                // Remove também o status, pois não é necessário na sessão
                unset($usuario['status']);
                return $usuario; // Retorna os dados do utilizador para a sessão
            }

            return false; // Senha incorreta

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