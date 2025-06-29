# 💰 FinPlan – Sistema de Controle Financeiro Pessoal

Projeto desenvolvido na disciplina de **Engenharia de Software** na **Universidade Federal do Tocantins (UFT)**.  
O **FinPlan** é um sistema web open-source para controle financeiro pessoal, com funcionalidades como:

- Cadastro de transações (entradas e saídas)
- Criação de metas financeiras (goals)
- Visualização gráfica de saldo, metas e categorias
- Painel exclusivo para administradores
- Notificações automáticas
- Login tradicional e via **Google OAuth 2.0**

---

## ✅ Requisitos do Sistema

Antes de rodar o projeto, instale em sua máquina:

- PHP 8.1 ou superior (com suporte a SQLite3 e OpenSSL)
- Composer
- SQLite3 (incluso na maioria das instalações PHP)
- Navegador moderno (Chrome, Firefox, Edge...)

Opcional:
- Um servidor local como [XAMPP](https://www.apachefriends.org/), [Laragon](https://laragon.org/) ou usar `php -S`

---

## 📦 Instalação do Projeto

### 1. Clone o repositório

```
git clone https://github.com/SidyDev-AI/finplan

cd finplan
```

### 2. Instale as dependências do Composer

```
composer install
```

### 3. Crie o banco de dados SQLite

O banco será criado automaticamente na primeira execução, mas você pode garantir a existência dele:
```
mkdir -p Database

touch Database/database.sqlite
```

### 4. Configure as variáveis de ambiente

Crie o arquivo .env na raiz do projeto com as seguintes chaves:
```
GOOGLE_CLIENT_ID=coloque_aqui_sua_client_id
GOOGLE_CLIENT_SECRET=coloque_aqui_sua_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/src/backend/api/auth/google-callback.php
```

⚠️ Obtenha essas informações no [Google Cloud Console](https://console.cloud.google.com/), criando um OAuth 2.0 Client ID.

## 🚀 Rodando o Projeto

### 1. Com servidor embutido PHP

```
php -S localhost:8000
```

Acesse no navegador:
```
http://localhost:8000
```

## 🔐 Acesso Padrão de Administrador

Na primeira execução, um usuário administrador padrão será criado:

    Email: admin@gmail.com

    Senha: admin123

    Permissão: admin

## 🧪 Testes Automatizados (PHPUnit)

Executar os testes:

```
vendor/bin/phpunit
```

Certifique-se que a extensão sqlite3 do PHP está habilitada e que o arquivo phpunit.xml está presente na raiz.

## 📁 Estrutura do Projeto

```
finplan/
├── Database/
│   └── conn.php               # Conexão e criação automática do banco
├── src/
│   ├── api/
│   │   └── auth/              # Endpoints REST (login, register, google)
│   ├── backend/ 
|   |     └── api/             # Scripts de autenticação e lógica
|   |     └── helpers/         # Script de envio de e-mail
│   ├── css/                   # Estilos (index.css, dashboard.css, etc)
│   ├── img/                   # Logos e ícones
│   ├── js/
│   ├── pages/                 # dashboard.php, perfil.php, painel_admin.php etc
├── tests/                     # Testes unitários com SQLite em memória
├── .env                       # Variáveis de ambiente
├── composer.json              # Dependências PHP
├── phpunit.xml                # Configuração de testes
└── index.php                  # Tela de login inicial
└── REAME.md
└── Configuracao_instalacao.md          

```

## 👨‍💻 Desenvolvedores
Lucas Moura – [Github](https://github.com/luc4sm0ur4)

José Borges - [Github](https://github.com/SidyDev-AI)

Projeto acadêmico – UFT – Engenharia de Software
