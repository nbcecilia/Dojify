<?php
// model/dao/UsuarioDAO.php

require_once __DIR__ . '/Conexao.php';
require_once __DIR__ . '/../dto/UsuarioDTO.php';

class UsuarioDAO {
    private PDO $conexao;

    public function __construct() {
        $this->conexao = Conexao::getConexao();
    }

    /* Cadastra um novo usuário e insere sua credencial na tabela 'login' dentro de uma transação */
    public function cadastrar(UsuarioDTO $u, string $senha): bool {
        try {
            $this->conexao->beginTransaction();

            $sql = "INSERT INTO usuario (
                        id_academia, perfil_id, nome, cpf, data_nascimento, telefone, email, 
                        especialidade, data_admissao, responsavel, observacao, data_matricula, status
                    ) VALUES (
                        :id_academia, :perfil_id, :nome, :cpf, :data_nascimento, :telefone, :email, 
                        :especialidade, :data_admissao, :responsavel, :observacao, :data_matricula, :status
                    )";

            $stmt = $this->conexao->prepare($sql);
            
            $stmt->bindValue(':id_academia', $u->getIdAcademia(), $u->getIdAcademia() ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':perfil_id', $u->getPerfilId(), PDO::PARAM_INT);
            $stmt->bindValue(':nome', $u->getNome());
            $stmt->bindValue(':cpf', $u->getCpf());
            $stmt->bindValue(':data_nascimento', $u->getDataNascimento());
            $stmt->bindValue(':telefone', $u->getTelefone());
            $stmt->bindValue(':email', $u->getEmail());
            
            $stmt->bindValue(':especialidade', $u->getEspecialidade() ?: null);
            $stmt->bindValue(':data_admissao', $u->getDataAdmissao() ?: null);
            $stmt->bindValue(':responsavel', $u->getResponsavel() ?: null);
            $stmt->bindValue(':observacao', $u->getObservacao() ?: null);
            $stmt->bindValue(':data_matricula', $u->getDataMatricula() ?: null);
            $stmt->bindValue(':status', $u->getStatus() ?? 'ATIVO');
            
            $stmt->execute();

            $idUsuario = (int) $this->conexao->lastInsertId();

            $sqlLogin = "INSERT INTO login (id_usuario, senha_hash) VALUES (:id_usuario, :senha_hash)";
            $stmtLogin = $this->conexao->prepare($sqlLogin);
            $stmtLogin->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmtLogin->bindValue(':senha_hash', password_hash($senha, PASSWORD_DEFAULT));
            $stmtLogin->execute();

            $this->conexao->commit();
            return true;
        } catch (Exception $e) {
            $this->conexao->rollBack();
            return false;
        }
    }

    /* Lista todos os usuários (Visão Admin) */
    public function listarTodos(): array {
        $sql = "SELECT u.*, p.nome AS perfil_nome 
                FROM usuario u 
                JOIN perfil p ON u.perfil_id = p.id_perfil 
                ORDER BY u.id_usuario DESC";
        return $this->conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Lista apenas os usuários vinculados à academia logada (Visão Gerente) */
    public function listarPorAcademia(int $idAcademia): array {
        $sql = "SELECT u.*, p.nome AS perfil_nome 
                FROM usuario u 
                JOIN perfil p ON u.perfil_id = p.id_perfil 
                WHERE u.id_academia = :id_academia 
                ORDER BY u.nome ASC";
                
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Busca um usuário pelo seu ID */
    public function buscarPorId(int $id): ?array {
        $sql = "SELECT * FROM usuario WHERE id_usuario = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado : null;
    }

    /* Atualiza os dados cadastrais de um usuário existente */
    public function atualizar(UsuarioDTO $u): bool {
        try {
            $sql = "UPDATE usuario SET 
                        nome = :nome, 
                        cpf = :cpf, 
                        data_nascimento = :data_nascimento, 
                        telefone = :telefone, 
                        email = :email, 
                        status = :status 
                    WHERE id_usuario = :id_usuario";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':nome', $u->getNome());
            $stmt->bindValue(':cpf', $u->getCpf());
            $stmt->bindValue(':data_nascimento', $u->getDataNascimento());
            $stmt->bindValue(':telefone', $u->getTelefone());
            $stmt->bindValue(':email', $u->getEmail());
            $stmt->bindValue(':status', $u->getStatus());
            $stmt->bindValue(':id_usuario', $u->getIdUsuario(), PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /* Exclui o usuário e seu registro de login associado de forma segura */
    public function excluir(int $id): bool {
        try {
            $this->conexao->beginTransaction();

            // Remove primeiro da tabela login por conta da chave estrangeira
            $stmtLogin = $this->conexao->prepare("DELETE FROM login WHERE id_usuario = :id");
            $stmtLogin->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtLogin->execute();

            // Depois remove da tabela usuario
            $stmtUsuario = $this->conexao->prepare("DELETE FROM usuario WHERE id_usuario = :id");
            $stmtUsuario->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtUsuario->execute();

            $this->conexao->commit();
            return true;
        } catch (Exception $e) {
            $this->conexao->rollBack();
            return false;
        }
    }
}