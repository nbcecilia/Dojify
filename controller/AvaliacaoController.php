<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/dao/AvaliacaoDAO.php';
require_once __DIR__ . '/../model/dto/AvaliacaoDTO.php';

class AvaliacaoController
{
    private AvaliacaoDAO $dao;

    public function __construct()
    {
        $this->dao = new AvaliacaoDAO();
    }

    public function cadastrar(
        int $idProfessor,
        int $idAluno,
        int $idModalidade,
        string $data,
        ?string $dataPrevista,
        string $habilidades,
        string $observacoes
    ): array
    {
        if ($idAluno <= 0 || $idModalidade <= 0 || $data === '' || trim($habilidades) === '') {
            return ['status' => false, 'mensagem' => 'Por favor, preencha todos os campos obrigatórios.'];
        }

        $dataAvaliacao = DateTime::createFromFormat('!Y-m-d', $data);
        $errosDataAvaliacao = DateTime::getLastErrors();
        $dataValida = $dataAvaliacao !== false
            && $dataAvaliacao->format('Y-m-d') === $data
            && ($errosDataAvaliacao === false
                || ($errosDataAvaliacao['warning_count'] === 0 && $errosDataAvaliacao['error_count'] === 0));
        if (!$dataValida) {
            return ['status' => false, 'mensagem' => 'Informe uma data de avaliação válida.'];
        }

        if ($dataPrevista !== null && $dataPrevista !== '') {
            $dataPrevistaObj = DateTime::createFromFormat('!Y-m-d', $dataPrevista);
            $errosDataPrevista = DateTime::getLastErrors();
            if (
                $dataPrevistaObj === false ||
                $dataPrevistaObj->format('Y-m-d') !== $dataPrevista ||
                ($errosDataPrevista !== false
                    && ($errosDataPrevista['warning_count'] > 0 || $errosDataPrevista['error_count'] > 0)) ||
                $dataPrevistaObj <= new DateTime('today')
            ) {
                return ['status' => false, 'mensagem' => 'A previsão de graduação deve ser uma data futura válida.'];
            }
        } else {
            $dataPrevista = null;
        }

        $avaliacao = new AvaliacaoDTO();
        $avaliacao->setIdUsuarioProfessor($idProfessor);
        $avaliacao->setIdUsuarioAluno($idAluno);
        $avaliacao->setDataAvaliacao($data);
        $avaliacao->setHabilidadesMelhorar(trim($habilidades));
        $avaliacao->setObservacoes(trim($observacoes));

        if (!$this->dao->cadastrar($avaliacao, $idModalidade, $dataPrevista)) {
            return ['status' => false, 'mensagem' => 'Não foi possível registar a avaliação.'];
        }

        return ['status' => true, 'mensagem' => 'Avaliação registada com sucesso!'];
    }

    public function atualizar(
        int $idProfessor,
        int $idAvaliacao,
        int $idAluno,
        int $idModalidade,
        string $data,
        ?string $dataPrevista,
        string $habilidades,
        string $observacoes
    ): array {
        if (
            $idProfessor <= 0 ||
            $idAvaliacao <= 0 ||
            $idAluno <= 0 ||
            $idModalidade <= 0 ||
            $data === '' ||
            trim($habilidades) === ''
        ) {
            return ['status' => false, 'mensagem' => 'Por favor, preencha todos os campos obrigatórios.'];
        }

        $dataAvaliacao = DateTime::createFromFormat('!Y-m-d', $data);
        $errosDataAvaliacao = DateTime::getLastErrors();
        if (
            $dataAvaliacao === false ||
            $dataAvaliacao->format('Y-m-d') !== $data ||
            ($errosDataAvaliacao !== false &&
                ($errosDataAvaliacao['warning_count'] > 0 || $errosDataAvaliacao['error_count'] > 0))
        ) {
            return ['status' => false, 'mensagem' => 'Informe uma data de avaliação válida.'];
        }

        if ($dataPrevista !== null && $dataPrevista !== '') {
            $dataPrevistaObj = DateTime::createFromFormat('!Y-m-d', $dataPrevista);
            $errosDataPrevista = DateTime::getLastErrors();
            if (
                $dataPrevistaObj === false ||
                $dataPrevistaObj->format('Y-m-d') !== $dataPrevista ||
                ($errosDataPrevista !== false &&
                    ($errosDataPrevista['warning_count'] > 0 || $errosDataPrevista['error_count'] > 0))
            ) {
                return ['status' => false, 'mensagem' => 'Informe uma previsão de graduação válida.'];
            }
        } else {
            $dataPrevista = null;
        }

        $avaliacao = new AvaliacaoDTO();
        $avaliacao->setIdAvaliacao($idAvaliacao);
        $avaliacao->setIdUsuarioProfessor($idProfessor);
        $avaliacao->setIdUsuarioAluno($idAluno);
        $avaliacao->setIdModalidade($idModalidade);
        $avaliacao->setDataAvaliacao($data);
        $avaliacao->setDataPrevista($dataPrevista);
        $avaliacao->setHabilidadesMelhorar(trim($habilidades));
        $avaliacao->setObservacoes(trim($observacoes));

        if (!$this->dao->atualizar($avaliacao)) {
            return ['status' => false, 'mensagem' => 'Não foi possível atualizar a avaliação.'];
        }

        return ['status' => true, 'mensagem' => 'Avaliação atualizada com sucesso!'];
    }

    public function listarPorAcademia(int $idAcademia): array
    {
        return $this->dao->listarPorAcademia($idAcademia);
    }

    public function listarAlunosPorAcademia(int $idAcademia): array
    {
        return $this->dao->listarAlunosPorAcademia($idAcademia);
    }

    public function listarPorProfessor(int $idProfessor): array
    {
        return $this->dao->listarPorProfessor($idProfessor);
    }

    public function listarAlunosPorProfessor(int $idProfessor): array
    {
        return $this->dao->listarAlunosPorProfessor($idProfessor);
    }

    // ==========================================
    // MÉTODOS SOLICITADOS PELAS VIEWS
    // ==========================================
    public function listarPorPerfilUsuario(int $perfilId, int $idAcademia, int $idUsuario): array
    {
        if ($perfilId == 2) {
            return $this->listarPorAcademia($idAcademia);
        } elseif ($perfilId == 3) {
            return $this->listarPorProfessor($idUsuario);
        }
        return [];
    }

    public function listarAlunosPorPerfilUsuario(int $perfilId, int $idAcademia, int $idUsuario): array
    {
        if ($perfilId == 2) {
            return $this->listarAlunosPorAcademia($idAcademia);
        } elseif ($perfilId == 3) {
            return $this->listarAlunosPorProfessor($idUsuario);
        }
        return [];
    }
}