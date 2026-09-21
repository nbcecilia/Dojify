// assets/js/usuario.js

function alternarCampos(valor) {
    const camposProfessor = document.getElementById('campos-professor');
    const camposAluno = document.getElementById('campos-aluno');

    // Oculta ambos inicialmente para evitar conflitos
    if (camposProfessor) camposProfessor.style.display = 'none';
    if (camposAluno) camposAluno.style.display = 'none';

    // Converte o valor para string e minúsculas para garantir compatibilidade
    const valorPerfil = String(valor).toLowerCase();

    // Mostra se for Professor (ID 3 ou texto 'professor')
    if (valorPerfil === '3' || valorPerfil === 'professor') {
        if (camposProfessor) camposProfessor.style.display = 'block';
    } 
    // Mostra se for Aluno (ID 4 ou texto 'aluno')
    else if (valorPerfil === '4' || valorPerfil === 'aluno') {
        if (camposAluno) camposAluno.style.display = 'block';
    }
}