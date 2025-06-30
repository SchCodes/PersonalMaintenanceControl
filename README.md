# personal-maintenance-control

Sistema web para controle pessoal de manutenção industrial, desenvolvido utilizando PHP procedural, MySQL e Bootstrap.

## Descrição Resumida

Este projeto tem como objetivo facilitar o registro, organização e consulta de atividades de manutenção industrial realizadas por técnicos e administradores, promovendo a documentação dos procedimentos, a segurança operacional e o compartilhamento de conhecimento técnico.

## Funcionalidades do MVP

- **Login e controle de sessão:** Acesso com níveis diferenciados para técnicos e administradores.
- **Gestão de usuários:** Cadastro, edição e exclusão de usuários (apenas para administradores).
- **Gestão de equipamentos:** Cadastro e gerenciamento dos ativos industriais.
- **Cadastro e consulta de materiais:** Registro dos materiais/peças usados nas manutenções.
- **Atividades de manutenção:** Registro detalhado das intervenções, incluindo:
  - Equipamento, técnico responsável, datas e tipo de manutenção
  - Descrição da atividade e método de execução
  - Campos para Permissão de Trabalho (PT), Análise de Segurança do Trabalho (AST) e Gerenciamento de Risco Operacional (GRO)
  - Upload de imagens antes e depois da intervenção
  - Lição aprendida
  - Materiais utilizados (com vínculo entre atividade e item de estoque)
- **Histórico e consulta:** Filtros simples por técnico, equipamento ou período, permitindo análise rápida das manutenções realizadas.

## Público-alvo

Técnicos de manutenção industrial, gestores e administradores de plantas industriais que desejam aprimorar o controle e a rastreabilidade das atividades de manutenção e segurança.

## Diferenciais

- Estrutura simples, porém robusta, facilitando tanto o uso quanto a customização para cenários reais.
- Priorização de campos voltados à segurança do trabalho (PT, AST, GRO).
- Possibilidade de anexar evidências visuais (imagens) e registrar lições aprendidas para aprimoramento contínuo.
- Permite fácil expansão futura para dashboards, badges de reconhecimento, favoritos e outros recursos.

## Tecnologias utilizadas

- PHP procedural (backend)
- MySQL (banco de dados relacional)
- Bootstrap (template administrativo responsivo)
- HTML5 e JavaScript (validações e interatividade básica)

## Observações

Este sistema é voltado para fins acadêmicos e demonstração de boas práticas de modelagem e desenvolvimento web estruturado. Pode ser facilmente expandido para contextos industriais reais ou como base para projetos mais complexos.
