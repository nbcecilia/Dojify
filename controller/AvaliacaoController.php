<?php
<<<<<<< HEAD
/**
 * Processa comentários dos alunos, previsões de graduação cadastradas pela
 * equipe e a leitura das notificações. A criação/edição da avaliação técnica
 * pela equipe permanece como uma ação própria a ser integrada posteriormente.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/dao/AvaliacaoDAO.php';

class AvaliacaoController
{
    private AvaliacaoDAO $dao;

    public function __construct()
    {
        $this->dao = new AvaliacaoDAO();
    }

    public function processar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar('erro=metodo_invalido');
        }

        if (!isset($_SESSION['usuario'])) {
            header('Location: ../view/login.php?erro=acesso_negado');
            exit;
        }

        $tokenSessao = $_SESSION['csrf_token'] ?? '';
        $tokenFormulario = $_POST['csrf_token'] ?? '';
        if (
            !is_string($tokenSessao) ||
            !is_string($tokenFormulario) ||
            $tokenSessao === '' ||
            !hash_equals($tokenSessao, $tokenFormulario)
        ) {
            $this->redirecionar('erro=token_invalido');
        }

        $acao = (string)($_GET['acao'] ?? '');
        $perfilId = (int)($_SESSION['usuario']['perfil_id'] ?? 0);
        $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);

        try {
            if ($acao === 'comentar' && $perfilId === 4) {
                $this->adicionarComentario($idUsuario);
            } elseif ($acao === 'salvar_previsao' && in_array($perfilId, [2, 3], true)) {
                $this->salvarPrevisao($idUsuario, $perfilId);
            } elseif ($acao === 'marcar_lida' && in_array($perfilId, [2, 3], true)) {
                $idNotificacao = filter_input(INPUT_POST, 'id_notificacao', FILTER_VALIDATE_INT);
                if (!$idNotificacao || $idNotificacao <= 0) {
                    $this->redirecionar('erro=dados_invalidos');
                }
                $this->dao->marcarNotificacaoLida($idNotificacao, $idUsuario);
                $this->redirecionar('sucesso=notificacao_lida');
            } else {
                $this->redirecionar('erro=acao_invalida');
            }
        } catch (DomainException $e) {
            $this->redirecionar('erro=dados_invalidos');
        } catch (Throwable $e) {
            error_log('Erro ao processar avaliação, comentário ou previsão de graduação: ' . $e->getMessage());
            $this->redirecionar('erro=interno');
        }
    }

    private function adicionarComentario(int $idAluno): void
    {
        $idAvaliacao = filter_input(INPUT_POST, 'id_avaliacao', FILTER_VALIDATE_INT);
        $comentario = trim((string)($_POST['comentario'] ?? ''));
        $tamanho = function_exists('mb_strlen') ? mb_strlen($comentario, 'UTF-8') : strlen($comentario);

        if (!$idAvaliacao || $idAvaliacao <= 0 || $comentario === '' || $tamanho > 3000) {
            $this->redirecionarAluno('erro=dados_invalidos');
        }

        $this->dao->adicionarComentario($idAvaliacao, $idAluno, $comentario);
        $this->redirecionarAluno('sucesso=comentario_enviado');
    }

    private function salvarPrevisao(int $idStaff, int $perfilId): void
    {
        $idAcademia = (int)($_SESSION['id_academia'] ?? $_SESSION['usuario']['id_academia'] ?? 0);
        $idAluno = filter_input(INPUT_POST, 'id_aluno', FILTER_VALIDATE_INT);
        $idModalidade = filter_input(INPUT_POST, 'id_modalidade', FILTER_VALIDATE_INT);
        $dataPrevista = trim((string)($_POST['data_prevista'] ?? ''));
        $data = DateTime::createFromFormat('!Y-m-d', $dataPrevista);
        $errosData = DateTime::getLastErrors();

        if (
            $idAcademia <= 0 ||
            !$idAluno ||
            !$idModalidade ||
            $data === false ||
            $data->format('Y-m-d') !== $dataPrevista ||
            ($errosData !== false && ($errosData['warning_count'] > 0 || $errosData['error_count'] > 0)) ||
            $data <= new DateTime('today')
        ) {
            $this->redirecionarEquipe('erro=dados_invalidos', $perfilId);
        }

        $this->dao->salvarGraduacaoPrevista(
            $idStaff,
            $idAcademia,
            $idAluno,
            $idModalidade,
            $dataPrevista
        );
        $this->redirecionarEquipe('sucesso=previsao_salva', $perfilId);
    }

    private function redirecionarAluno(string $query): void
    {
        header('Location: ../view/aluno/historico_graduacao.php?' . $query);
        exit;
    }

    private function redirecionarEquipe(string $query, int $perfilId): void
    {
        $this->redirecionar($query, $perfilId);
    }

    private function redirecionar(string $query, ?int $perfilId = null): void
    {
        $perfilId = $perfilId ?? (int)($_SESSION['usuario']['perfil_id'] ?? 0);
        $destino = in_array($perfilId, [2, 3], true)
            ? '../view/professor/avaliacoes.php'
            : '../view/aluno/historico_graduacao.php';
        header('Location: ' . $destino . '?' . $query);
        exit;
    }
}

(new AvaliacaoController())->processar();
=======
// controller/AvaliacaoController.php

require_once __DIR__ . '/../model/dao/AvaliacaoDAO.php';
require_once __DIR__ . '/../model/dto/AvaliacaoDTO.php';

class AvaliacaoController {
    private $avaliacaoDAO;

    public function __construct() {
        $this->avaliacaoDAO = new AvaliacaoDAO();
    }

    public function cadastrar($id_professor, $id_aluno, $data, $habilidades, $observacoes) {
        if (empty($id_aluno) || empty($data) || empty($habilidades)) {
            return ['status' => false, 'mensagem' => 'Por favor, preencha todos os campos obrigatórios.'];
        }

        $avaliacao = new AvaliacaoDTO();
        $avaliacao->setIdUsuarioProfessor($id_professor);
        $avaliacao->setIdUsuarioAluno($id_aluno);
        $avaliacao->setDataAvaliacao($data);
        $avaliacao->setHabilidadesMelhorar(trim($habilidades));
        $avaliacao->setObservacoes(trim($observacoes));

        $resultado = $this->avaliacaoDAO->cadastrar($avaliacao);

        if ($resultado) {
            return ['status' => true, 'mensagem' => 'Avaliação registada com sucesso!'];
        } else {
            return ['status' => false, 'mensagem' => 'Erro ao registar a avaliação no banco de dados.'];
        }
    }

    public function listarPorAcademia($id_academia) {
        return $this->avaliacaoDAO->listarPorAcademia($id_academia);
    }

    public function listarAlunosPorAcademia($id_academia) {
        return $this->avaliacaoDAO->listarAlunosPorAcademia($id_academia);
    }
}
>>>>>>> d02eb76914f0c532e28f1b30bc91c65006bf1ac4
