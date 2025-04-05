# Engenharia de Software 2025.1 - Universidade Federal do Tocantins
 Bacharelado em Ciência da Computação, 4º semestre   
 Professor: Edeilson Milhomem da Silva   
 Grupo: José Borges, Lucas Carvalho, Kawan de Sá, Ruan Carlos, Anna Laura, Thales Marques

## FinPlan
### Descrição : 
O FinPlan é uma plataforma online de gestão financeira pessoal desenvolvida para auxiliar usuários no controle das suas finanças de forma simples e eficiente. O sistema permite o cadastro de receitas e despesas, categorização de transações, visualização de relatórios e gráficos interativos gerados automaticamente baseado nas inserções do usuario.  A ferramenta foi pensada para quem deseja organizar o orçamento mensal, acompanhar investimentos e alcançar metas financeiras com clareza e praticidade.

![Tela de Inicial]()

----
# 1° Interação : 

## User Stories e Requisitos Funcionais
## User Stories

### 1. Cadastro e Autenticação

#### US001 – Como usuário, quero me cadastrar no sistema com e-mail e senha para acessar minhas finanças.
**Critérios de Aceite:**
- O e-mail deve ser validado com link de ativação.
- A senha deve ter no mínimo 8 caracteres, incluindo uma letra maiúscula, um número e um caractere especial.
- O cadastro deve permitir autenticação social (Google, Apple).

#### US002 – Como usuário, quero fazer login via e-mail/senha ou autenticação social para acessar minha conta.
**Critérios de Aceite:**
- Deve permitir login via e-mail/senha ou Google/Apple.
- Exibir mensagem de erro em caso de credenciais inválidas.

#### US003 – Como usuário, quero poder redefinir minha senha caso a esqueça.
**Critérios de Aceite:**
- O sistema deve enviar um link para redefinição via e-mail.
- O link deve expirar após 30 minutos.

#### US004 – Como usuário, quero poder definir meu perfil de investidor.
**Critérios de Aceite:**
- O usuário pode escolher entre vários perfis (conservador, agressivo, etc.).

---

## Requisitos Funcionais (RF)

### 1.1. Autenticação e Perfil do Usuário (1° Interação)

- **RF001** – O sistema deve permitir cadastro de usuários via e-mail/senha ou autenticação social (Google, Apple).
- **RF002** – O sistema deve enviar e-mail de confirmação para ativação de conta.
- **RF003** – O sistema deve permitir recuperação de senha via e-mail.
- **RF004** – O usuário deve poder editar seu perfil (nome, foto, preferências, notificação). 

---------------------------------------------------------------------------------------------

# 2° Interação :

## 2. Dashboard Financeiro

### US005 – Como usuário, quero ver um resumo gráfico das minhas finanças para entender meu saldo mensal.
**Critérios de Aceite:**
- Exibir saldo atual baseado em receitas e despesas.
- Apresentar gráficos (pizza, barras) com comparativo mensal/anual.
- Permitir filtros por período (dia, mês, ano).

### US006 – Como usuário, quero visualizar minhas metas financeiras no dashboard para acompanhar meu progresso.
**Critérios de Aceite:**
- Exibir metas criadas pelo usuário.
- Mostrar progresso percentual de cada meta.

### US007 – Como usuário, quero visualizar sugestões de investimento de acordo com o meu perfil de investidor.
**Critérios de Aceite:**
- Exibir investimentos adequados ao perfil do usuário.
- Permitir filtros por tipo de rendimento (CDB/LCI/LCA/Ações/Opções, etc.).

---

## 3. Gestão de Transações

### US008 – Como usuário, quero adicionar uma despesa/receita com categoria, data e valor para registrar meus gastos.
**Critérios de Aceite:**
- Permitir cadastro com os campos: valor, data, categoria, descrição e anexo (comprovante opcional).
- Validar que o valor deve ser maior que zero.

### US009 – Como usuário, quero editar ou excluir uma transação cadastrada incorretamente.
**Critérios de Aceite:**
- O sistema deve permitir edição de qualquer campo da transação.
- Deve haver confirmação antes da exclusão.

---

## 1.2. Gestão de Transações (2° Interação)

- **RF005** – O usuário deve poder cadastrar e visualizar transações (receitas e despesas) com:
  - Valor, data, categoria, descrição e anexo (comprovante). 
- **RF006** – O sistema deve permitir edição e exclusão de transações. 

---

## 1.3. Dashboard e Visualização Financeira (2° Interação)

- **RF007** – O sistema deve exibir um dashboard com:
  - Saldo atual (receitas - despesas).
  - Gráficos de receitas vs. despesas (mensal/anual).
  - Metas financeiras e progresso (curto/médio/longo prazo).
- **RF008** – O usuário deve poder filtrar dados por período (dia, mês, ano).
