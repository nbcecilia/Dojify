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

    /* Atualiza os dados cadastrais de um usuário existente*/
    public function atualizar(UsuarioDTO $u): bool {
        try {
            $sql = "UPDATE usuario SET 
                        nome = :nome, 
                        cpf = :cpf, 
                        data_nascimento = :data_nascimento, 
                        telefone = :telefone, 
                        email = :email, 
                        especialidade = :especialidade,
                        status = :status 
                    WHERE id_usuario = :id_usuario";
            
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':nome', $u->getNome());
            $stmt->bindValue(':cpf', $u->getCpf());
            $stmt->bindValue(':data_nascimento', $u->getDataNascimento());
            $stmt->bindValue(':telefone', $u->getTelefone());
            $stmt->bindValue(':email', $u->getEmail());
            $stmt->bindValue(':especialidade', $u->getEspecialidade() ?: null);
            $stmt->bindValue(':status', $u->getStatus());
            $stmt->bindValue(':id_usuario', $u->getIdUsuario(), PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    // DELETE físico por desativação lógica (Soft Delete) */
    public function desativar(int $id): bool {
        try {
            $sql = "UPDATE usuario SET status = 'INATIVO' WHERE id_usuario = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /* Reativa um usuário alterando o seu status de volta para 'ATIVO' */
    public function reativar(int $id): bool {
        try {
            $sql = "UPDATE usuario SET status = 'ATIVO' WHERE id_usuario = :id";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /* Lista apenas os gerentes (perfil_id = 2) que estão inativos */
    public function listarGerentesInativos(): array {
        $sql = "SELECT u.*, a.nome AS academia_nome 
                FROM usuario u 
                LEFT JOIN academia a ON u.id_academia = a.id_academia
                WHERE u.perfil_id = 2 AND u.status = 'INATIVO'
                ORDER BY u.nome ASC";
        return $this->conexao->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Verifica se já existe algum gerente (perfil_id = 2) ATIVO na mesma academia */
    public function existeGerenteAtivoNaAcademia(int $idAcademia): bool {
        $sql = "SELECT COUNT(*) FROM usuario 
                WHERE id_academia = :id_academia 
                  AND perfil_id = 2 
                  AND status = 'ATIVO'";
        
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id_academia', $idAcademia, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /* Retorna os indicadores (KPIs) para o PAINEL GERENTE */
    public function buscarIndicadoresGerente(int $idAcademia): array {
        $indicadores = [
            'alunos_ativos' => 0,
            'professores' => 0,
            'pagamentos_pendentes' => 0,
            'turmas' => 0
        ];

        try {
            // 1. Alunos ativos (perfil_id = 4)
            $sqlAlunos = "SELECT COUNT(*) FROM usuario WHERE id_academia = ? AND perfil_id = 4 AND status = 'ATIVO'";
            $stmt = $this->conexao->prepare($sqlAlunos);
            $stmt->execute([$idAcademia]);
            $indicadores['alunos_ativos'] = (int)$stmt->fetchColumn();

            // 2. Professores (perfil_id = 3)
            $stmt = $this->conexao->prepare("SELECT COUNT(*) FROM usuario WHERE id_academia = ? AND perfil_id = 3 AND status = 'ATIVO'");
            $stmt->execute([$idAcademia]);
            $indicadores['professores'] = (int)$stmt->fetchColumn();

            // 3. Pagamentos pendentes
            $sqlPagamento = "SELECT COUNT(p.id_pagamento) 
                             FROM pagamento p
                             JOIN plano pl ON p.id_plano_matricula = pl.id_plano
                             JOIN usuario u ON pl.id_usuario_aluno = u.id_usuario
                             WHERE u.id_academia = ? AND p.status = 'PENDENTE'";
            $stmt = $this->conexao->prepare($sqlPagamento);
            $stmt->execute([$idAcademia]);
            $indicadores['pagamentos_pendentes'] = (int)$stmt->fetchColumn();

            // 4. Turmas ativas
            $sqlTurma = "SELECT COUNT(t.id_turma) 
                         FROM turma t
                         JOIN modalidade m ON t.id_modalidade = m.id_modalidade
                         WHERE m.id_academia = ? AND t.status = 'ATIVA'";
            $stmt = $this->conexao->prepare($sqlTurma);
            $stmt->execute([$idAcademia]);
            $indicadores['turmas'] = (int)$stmt->fetchColumn();

        } catch (Exception $e) {
            // Em caso de erro, retorna os valores zerados
        }

        return $indicadores;
    }
}