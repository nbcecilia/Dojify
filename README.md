# Dojify
O **Dojify** é o projeto final do Curso Técnico em Informática desenvolvido para a gestão completa de escolas, academias e dojôs de artes marciais. O sistema automatiza a rotina operacional, técnica e financeira dessas instituições de forma simples e integrada.

#Alunos:
Caroline Lima de Menezes
Cecília Nunes de Brito
Guilherme Araújo Silva
Pedro Francisco Madureira Dourado

## Funcionalidades do Sistema
### Gestão de Pessoas, Turmas e Frequência* **Cadastros Gerais:** Registro completo de alunos, professores e modalidades de artes marciais.* **Turmas e Horários:** Definição de grades horárias, limites de vagas e associação de professores.* **Frequência e Agendamento:** Controle de presença dos alunos e agendamento de aulas.
### Evolução Técnica e Graduações* **Histórico de Faixas:** Acompanhamento cronológico da evolução do aluno (troca de faixas e graus).* **Desempenho:** Registro do progresso técnico e prontidão para exames de graduação.
### Controle Financeiro* **Mensalidades:** *Controle de status (pago, pendente e atrasado).* **Relatórios:** Identificação rápida de fluxo de caixa e alertas de inadimplência.
---
## Tecnologias Utilizadas
O projeto foi construído utilizando as seguintes tecnologias exigidas pela grade técnica:
* **Frontend:** HTML5 e CSS3 (Interface responsiva para administração e usuários)* **Backend:** PHP (Lógica de negócios, sessões e validações)* **Banco de Dados:** SQL / MySQL (Modelagem relacional para persistência de dados das tabelas de alunos, finanças e estoque)
---
## Estrutura Simplificada do Banco de Dados (SQL)
O banco de dados do sistema foi modelado para garantir a integridade referencial dos dados, contendo tabelas principais como:* `usuários` (Dados pessoais) * `modalidades` e `turmas` (Grades de horários e professores)* Histórico de graduações` (Registro de evolução de faixas)* `pagamento` (Controle financeiro)
---
## Como Executar o Projeto Localmente
### Pré-requisitos: Para rodar a aplicação, você precisará de um ambiente de servidor local que suporte PHP e MySQL. Recomendamos o uso de:* [XAMPP](https://apachefriends.org) ou [WampServer](https://wampserver.com)
### Passo a Passo
```bash# 1. Clone este repositório na pasta correspondente do seu servidor (ex: xampp/htdocs/)\$ git clone https://github.com
# 2. Inicie os serviços do Apache e MySQL através do painel de controle do XAMPP
# 3. Configure o Banco de Dados# - Abra o phpMyAdmin (http://localhost/phpmyadmin)# - Crie um novo banco de dados chamado `dojify`# - Importe o arquivo SQL estruturado do projeto (ex: database/db_dojify.sql)
# 4. Configure a conexão de banco de dados se necessário no arquivo correspondente (ex: conexao.php)
# 5. Acesse o sistema no seu navegador através da URL:http://localhost/dojify```
---
## Licença e Fins Acadêmicos
Este projeto foi desenvolvido exclusivamente para fins acadêmicos como requisito para conclusão do **Curso Técnico em Informática**.

