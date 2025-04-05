# Projeto Base USPdev - Laravel 11

## Sobre o Projeto

Este repositório serve como um **projeto base (starter kit)** para o desenvolvimento de aplicações web utilizando o framework Laravel 11, especificamente voltado para o contexto da Universidade de São Paulo (USP).

O objetivo principal é fornecer uma fundação padronizada e agilizar o início de novos projetos, já incluindo integrações e configurações comuns ao ambiente USP, como:

*   Autenticação via Senha Única USP.
*   Tema visual padrão da USP (`laravel-usp-theme`).
*   Estrutura básica de papéis e permissões.
*   Funcionalidades administrativas iniciais para gerenciamento de usuários.

Este projeto é destinado a desenvolvedores e equipes da USP que necessitam criar novas aplicações web de forma rápida e consistente.

## Principais Funcionalidades

Este projeto base inclui as seguintes funcionalidades prontas para uso ou extensão:

*   **Framework Laravel 11:** Baseado na versão mais recente do Laravel (no momento da criação).
*   **Autenticação Híbrida:**
    *   Login via **Senha Única USP** (`uspdev/senhaunica-socialite`).
    *   Login **Local** com Email/Senha (para usuários externos ou USP que definirem senha local).
    *   Registro de usuários (externos e USP).
    *   Fluxo para usuários USP definirem uma **senha local** opcional.
*   **Tema Visual USP:** Interface padronizada utilizando `uspdev/laravel-usp-theme`.
*   **Integração com Base Corporativa:** Utiliza o pacote `uspdev/replicado` para consulta de dados (ex: busca de informações de usuário USP pelo CodPes na área administrativa).
*   **Sistema de Papéis e Permissões:** Implementado com `spatie/laravel-permission`. Inclui papéis iniciais (`admin`, `usp_user`, `external_user`) e permissões básicas derivadas do `uspdev/senhaunica-socialite`.
*   **Gerenciamento Básico de Usuários (Admin):**
    *   Listagem de usuários cadastrados.
    *   Criação de usuários **USP** (busca dados no Replicado via CodPes).
    *   Criação **Manual** de usuários (externos ou USP).
*   **Verificação Obrigatória de Email:** Novos usuários registrados precisam verificar o email antes de acessar funcionalidades protegidas.
*   **Configuração de Testes Otimizada:** Ambiente de testes (`.env.testing`) pré-configurado para usar SQLite em memória, cache/sessão em array e fila síncrona, agilizando a execução dos testes.
*   **Estrutura de Frontend:** Utiliza Vite para compilação de assets, com Tailwind CSS e Alpine.js configurados.

## Pré-requisitos

Antes de começar, certifique-se de ter os seguintes requisitos instalados em seu ambiente de desenvolvimento:

*   PHP >= 8.2
*   Composer
*   Node.js e NPM
*   Um servidor de banco de dados suportado pelo Laravel (MySQL, PostgreSQL, SQLite, etc.), caso não utilize apenas SQLite.

## Instalação

Siga os passos abaixo para configurar o projeto em seu ambiente local:

1.  **Clonar o Repositório:**
    ```bash
    git clone <url-do-repositorio> nome-do-seu-projeto
    ```

2.  **Navegar para o Diretório:**
    ```bash
    cd nome-do-seu-projeto
    ```

3.  **Instalar Dependências PHP:**
    ```bash
    composer install
    ```

4.  **Instalar Dependências JavaScript:**
    ```bash
    npm install
    ```

5.  **Copiar Arquivo de Ambiente:**
    ```bash
    cp .env.example .env
    ```

6.  **Configurar Variáveis de Ambiente (`.env`):**
    *   Abra o arquivo `.env` e configure as variáveis essenciais, como conexão com o banco de dados (`DB_*`), URL da aplicação (`APP_URL`), e principalmente as credenciais da **Senha Única USP** (`SENHAUNICA_*`). Veja a seção "Configuração do Ambiente" abaixo.
    *   Configure as credenciais do Replicado (`REPLICADO_*`) se for utilizar a funcionalidade de busca de usuários USP.

7.  **Gerar Chave da Aplicação:**
    ```bash
    php artisan key:generate
    ```

8.  **Executar Migrações do Banco de Dados:**
    ```bash
    php artisan migrate
    ```

9.  **Executar Seeders:** (Cria papéis e permissões iniciais)
    ```bash
    php artisan db:seed
    ```

10. **Compilar Assets Frontend:**
    ```bash
    npm run build
    ```
    (Ou use `npm run dev` para desenvolvimento com hot-reloading)

11. **(Opcional) Criar Link Simbólico de Armazenamento:**
    ```bash
    php artisan storage:link
    ```

12. **Servir a Aplicação:**
    ```bash
    php artisan serve
    ```
    Acesse a aplicação no endereço fornecido (geralmente `http://127.0.0.1:8000`).

## Configuração do Ambiente (`.env`)

O arquivo `.env` é crucial para configurar a aplicação. As variáveis mais importantes a serem ajustadas são:

*   `APP_NAME`: Nome da sua aplicação.
*   `APP_URL`: A URL base da sua aplicação (ex: `http://meuprojeto.test`).
*   `APP_KEY`: Gerada automaticamente com `php artisan key:generate`.

*   **Banco de Dados:**
    *   `DB_CONNECTION`: Driver do banco (ex: `mysql`, `pgsql`, `sqlite`).
    *   `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Credenciais de acesso ao banco. Para SQLite, configure `DB_DATABASE` com o caminho absoluto para o arquivo (ex: `database_path('database.sqlite')`).

*   **Senha Única USP:** (Essenciais para o login USP)
    *   `SENHAUNICA_KEY`: Sua chave de consumidor (Client Key) obtida no portal de sistemas da USP.
    *   `SENHAUNICA_SECRET`: Seu segredo de consumidor (Client Secret) obtido no portal de sistemas da USP.
    *   `SENHAUNICA_CALLBACK_ID`: O ID da sua aplicação registrado no portal (necessário para o fluxo OAuth).
    *   `SENHAUNICA_CODIGO_UNIDADE`: Código da sua unidade USP (usado para identificar permissões de vínculo).
    *   `SENHAUNICA_ADMINS`, `SENHAUNICA_GERENTES`, `SENHAUNICA_USERS`: Listas de números USP (separados por vírgula) para atribuição automática de papéis/permissões via `uspdev/senhaunica-socialite` (opcional).
    *   `SENHAUNICA_DEV`: Configure para `no`, `local` ou `prod` conforme o ambiente Senha Única desejado.

*   **Replicado:** (Necessário para busca de usuários USP pelo admin)
    *   `REPLICADO_HOST`, `REPLICADO_PORT`, `REPLICADO_DATABASE`, `REPLICADO_USERNAME`, `REPLICADO_PASSWORD`, `REPLICADO_SYBASE`: Credenciais de acesso ao banco Replicado.

*   **Outras Configurações:** Verifique e ajuste configurações de `MAIL_*`, `CACHE_STORE`, `QUEUE_CONNECTION`, etc., conforme a necessidade do seu projeto.

*   **Ambiente de Teste (`.env.testing`):** Este arquivo sobreescreve as configurações do `.env` ao rodar `php artisan test`. Por padrão, está configurado para:
    *   Banco de dados SQLite em memória (`:memory:`).
    *   Cache e Sessão usando driver `array`.
    *   Fila usando driver `sync`.
    *   Hashing BCRYPT com rounds baixos (4) para performance.
    *   Credenciais dummy para Senha Única e Replicado (testes devem mockar interações externas).

## Autenticação

O sistema oferece múltiplas formas de autenticação:

1.  **Senha Única USP:**
    *   Principal método para membros da comunidade USP (alunos, docentes, servidores).
    *   Utiliza o fluxo OAuth1 padrão da USP.
    *   O botão "Login com Senha Única USP" na tela de login inicia o processo.
    *   Permissões baseadas em hierarquia e vínculo são automaticamente atribuídas (via `uspdev/senhaunica-socialite`).

2.  **Login Local (Email/Senha):**
    *   Usuários externos (registrados manualmente ou via formulário de registro).
    *   Usuários USP que optaram por definir uma senha local através do fluxo específico.
    *   Utiliza o formulário padrão de Email e Senha na tela de login.

3.  **Registro:**
    *   O formulário de registro (`/register`) permite criar novas contas.
    *   O usuário deve escolher entre "Externo" ou "Comunidade USP".
    *   Se "Comunidade USP" for selecionado, o email deve ser `@usp.br` e o Número USP (CodPes) é obrigatório.
    *   Contas criadas via registro **requerem verificação de email**.

4.  **Definir Senha Local USP:**
    *   Usuários USP podem solicitar um link por email (`/request-local-password`) para definir uma senha específica para este sistema, permitindo o login via formulário local sem usar a Senha Única.

5.  **Verificação de Email:**
    *   Implementada via interface `MustVerifyEmail` no modelo `User`.
    *   Novos usuários recebem um email com um link de verificação (válido por 60 minutos).
    *   O middleware `EnsureEmailIsVerifiedGlobally` força usuários não verificados a acessarem a tela de aviso de verificação antes de navegar em rotas protegidas.

## Área Administrativa

Usuários com o papel `admin` têm acesso a funcionalidades administrativas básicas, acessíveis pelo prefixo `/admin`.

*   **Acesso:** Requer login e o papel `admin`. O menu principal exibirá o item "Área Administrativa" para estes usuários.
*   **Dashboard Admin (`/admin/dashboard`):** Ponto de entrada da área administrativa com links rápidos.
*   **Gerenciamento de Usuários:**
    *   **Listar (`/admin/users`):** Exibe todos os usuários cadastrados, seus emails, Nº USP (se aplicável), papéis e status de verificação.
    *   **Criar Usuário USP (`/admin/users/create/usp`):** Permite criar um usuário buscando os dados (nome, email principal) diretamente do Replicado USP através do Número USP (CodPes). O usuário é criado com o papel `usp_user` e email pré-verificado. Uma senha inicial aleatória é gerada (o controlador informa a senha no flash message - **IMPORTANTE: modifique este comportamento para produção para enviar a senha de forma segura**).
    *   **Criar Usuário Manual (`/admin/users/create/manual`):** Permite criar usuários (externos ou USP) informando todos os dados manualmente.
        *   Se o Nº USP for fornecido, o usuário recebe o papel `usp_user` e email pré-verificado.
        *   Se o Nº USP não for fornecido, o usuário recebe o papel `external_user` e precisará verificar seu email.

## Testes

O projeto está configurado com PHPUnit e utiliza o ambiente definido em `.env.testing`.

*   **Executar Testes:**
    ```bash
    php artisan test
    ```
*   **Configuração:** Conforme mencionado, os testes rodam com SQLite em memória, cache/sessão em array e fila síncrona para maior velocidade e isolamento.
*   **Testes Incluídos:** O projeto base já inclui testes de Feature para Autenticação, Registro, Verificação de Email, Redefinição de Senha, Confirmação de Senha, Atualização de Perfil, Fluxo de Senha Local, Gerenciamento de Usuários Admin e Middleware de Verificação de Email. Inclui também testes de Unit para as Notifications customizadas e um teste para o Seeder de Papéis/Permissões.

## Contribuição

Instruções sobre como contribuir para o desenvolvimento ou melhoria deste projeto base (se aplicável). Siga as convenções de código e submeta Pull Requests.

## Licença

Este projeto é distribuído sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.