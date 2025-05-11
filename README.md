
# Engenharia de Software 2025.1 - Universidade Federal do Tocantins
**Bacharelado em Ciência da Computação - 4º semestre**  
**Professor:** Edeilson Milhomem da Silva  
**Grupo:** José Borges, Lucas Carvalho, Kawan de Sá, Ruan Carlos, Anna Laura, Thales Marques

---

## Projeto: FinPlan
### Descrição Geral

O **FinPlan** é uma plataforma online de gestão financeira pessoal desenvolvida para auxiliar usuários no controle das suas finanças de forma simples e eficiente. O sistema permite o cadastro de receitas e despesas, categorização de transações, visualização de relatórios e gráficos interativos gerados automaticamente baseado nas inserções do usuário. A ferramenta foi pensada para quem deseja organizar o orçamento mensal, acompanhar investimentos e alcançar metas financeiras com clareza e praticidade.

---

# ✅ 1ª Iteração: Autenticação e Onboarding Seguro

## User Stories

### US001 – Cadastro
**Como** usuário, **quero** me cadastrar no sistema com e-mail e senha **para** acessar minhas finanças.  
**Critérios de Aceite:**
- E-mail validado com link de ativação.
- Senha com no mínimo 8 caracteres, 1 letra maiúscula, 1 número e 1 caractere especial.
- Cadastro com autenticação social (Google, Apple).

📸 ![Criação de conta](/prototipos/criarconta.jpg)  
📸 ![Confirmação de conta](/prototipos/confirmacaoconta.jpg)

---

### US002 – Login
**Como** usuário, **quero** fazer login via e-mail/senha ou autenticação social **para** acessar minha conta.  
**Critérios de Aceite:**
- Login via e-mail/senha ou Google/Apple.
- Mensagem de erro em caso de credenciais inválidas.

📸 ![Login](/prototipos/login.jpg)

---

### US003 – Recuperação de Senha
**Como** usuário, **quero** redefinir minha senha caso a esqueça.  
**Critérios de Aceite:**
- Envio de link para redefinição via e-mail.
- Link expira em 30 minutos.

📸 ![Recuperação de Senha](/prototipos/Recuperação%20de%20senha.jpg)  
📸 ![Nova Senha](/prototipos/Nova%20Senha.jpg)

---

### US004 – Perfil do Investidor
**Como** usuário, **quero** definir meu perfil de investidor **para** personalizar as sugestões financeiras.  
**Critérios de Aceite:**
- Escolha entre perfis como conservador, moderado, agressivo.

📸 ![Perfil](/prototipos/perfil.jpg)  
📸 ![Notificações](/prototipos/notificacoes.jpg)  
📸 ![Notificações](/prototipos/notificacoes2.jpg)

---

## Requisitos Funcionais (RF)

### Autenticação e Perfil do Usuário

- **RF001:** Cadastro com e-mail/senha ou autenticação social.
- **RF002:** Confirmação de e-mail para ativação.
- **RF003:** Recuperação de senha via e-mail.
- **RF004:** Edição de perfil com nome, foto e preferências.

---

## ✅ Valor Entregue - 1ª Iteração

### Resumo:
Entrega de base sólida para o sistema:  
✅ Cadastro e login seguro  
✅ Recuperação de senha  
✅ Personalização inicial do perfil

---

### Detalhamento de Valor

| User Story | Valor para o Usuário | Valor para o Negócio |
|-----------|----------------------|-----------------------|
| US001     | Cadastro seguro      | Base de usuários qualificados |
| US002     | Login flexível       | Menos atrito no acesso |
| US003     | Recuperação autônoma | Redução de suporte técnico |
| US004     | Perfil personalizado | Dados para recomendações futuras |

---

### Protótipos Validados

- Fluxo completo: Cadastro → Confirmação → Login → Recuperação → Perfil
- Feedback visual: Mensagens de erro e confirmação


---

## 🚀 Impacto

**Para o usuário:** Segurança e personalização desde o primeiro acesso  
**Para o negócio:** Redução de churn e base para segmentações futuras  
**Para a equipe:** Infraestrutura sólida para novas funcionalidades

---
[Relatório do Projeto com a 1° Interação](Relatorio.md)

---

# ✅ 2ª Iteração: Dashboard e Gestão de Transações

## User Stories

### US005 – Dashboard Financeiro
**Como** usuário, **quero** ver um resumo gráfico das minhas finanças **para** entender meu saldo mensal.  
**Critérios de Aceite:**
- Saldo atual com base em receitas e despesas
- Gráficos em pizza e barra
- Filtros por dia/mês/ano

📸 ![Dashboard](/prototipos/dashboard.jpg)

---

### US006 – Metas Financeiras
**Como** usuário, **quero** visualizar minhas metas no dashboard **para** acompanhar meu progresso.  
**Critérios de Aceite:**
- Exibição de metas
- Progresso em percentual

---

### US007 – Sugestões de Investimento
**Como** usuário, **quero** sugestões baseadas no meu perfil **para** investir melhor.  
**Critérios de Aceite:**
- Sugestões alinhadas ao perfil
- Filtros por tipo de investimento

---

### US008 – Adicionar Transação
**Como** usuário, **quero** registrar despesas e receitas com categoria e valor.  
**Critérios de Aceite:**
- Campos obrigatórios: valor, data, categoria, descrição
- Valor maior que zero

---

### US009 – Editar/Excluir Transação
**Como** usuário, **quero** corrigir ou remover transações.  
**Critérios de Aceite:**
- Edição de qualquer campo
- Confirmação antes da exclusão

---

## Requisitos Funcionais (RF)

### Gestão de Transações

- **RF005:** Cadastro de transações com valor, data, categoria, descrição e comprovante
- **RF006:** Edição e exclusão de transações

### Dashboard e Visualização

- **RF007:** Exibição de saldo, gráficos e metas
- **RF008:** Filtros por período

---

## ✅ Valor Entregue - 2ª Iteração

### Resumo:
Entrega de funcionalidades visuais e operacionais essenciais:  
✅ Dashboard gráfico e filtros  
✅ Gestão de receitas e despesas  
✅ Projeções de metas e investimentos

---

### Detalhamento de Valor

| User Story | Valor para o Usuário | Valor para o Negócio |
|-----------|----------------------|-----------------------|
| US005     | Visualização clara do saldo | Engajamento com uso contínuo |
| US006     | Acompanhamento de metas     | Retenção por metas alcançadas |
| US007     | Dicas personalizadas        | Upselling ou parcerias futuras |
| US008     | Registro completo de finanças | Dados detalhados para análises |
| US009     | Correção de erros            | Confiabilidade no sistema |


---

## 📊 Protótipos Validados

- Dashboard funcional
- Registro de transações com anexo
- Filtros temporais aplicados

---

[Relatório do Projeto com a 2° Interação](Relatorio.md)

--- 

# ✅ 3ª Iteração: Orçamento, Relatórios e Integrações

## User Stories

### US010 – Definição de Orçamentos
**Como** usuário, **quero** definir orçamentos mensais por categoria **para** controlar meus gastos.  
**Critérios de Aceite:**
- Permitir definir limite por categoria
- Alertas ao atingir 80%, 90% e 100% do limite

📸 ![Orçamentos](/prototipos/orcamentos.jpg)

---

### US011 – Categorias Personalizadas
**Como** usuário, **quero** criar categorias personalizadas **para** organizar melhor minhas finanças.  
**Critérios de Aceite:**
- Adicionar/editar/excluir categorias
- Associar transações a múltiplas categorias

---

### US012 – Relatórios Exportáveis
**Como** usuário, **quero** gerar relatórios PDF/Excel **para** análise fiscal.  
**Critérios de Aceite:**
- Exportar dados por período personalizado
- Formatar relatórios com logo da plataforma

📸 ![Relatórios](/prototipos/relatorios.jpg)

---

### US014 – Integração Bancária
**Como** usuário, **quero** sincronizar minha conta bancária **para** importar transações automaticamente.  
**Critérios de Aceite:**
- Conexão via API com bancos autorizados
- Sincronização diária automática

---

### US018 – Gerenciar Assinaturas
**Como** usuário, **quero** cadastrar assinaturas recorrentes **para** evitar gastos não planejados.  
**Critérios de Aceite:**
- Lembretes 3 dias antes do vencimento
- Visualização consolidada de gastos

---

## Requisitos Funcionais (RF)

### Gestão de Orçamentos
- **RF009:** Definição de limites orçamentários por categoria
- **RF010:** Alertas progressivos de consumo (80%, 90%, 100%)

### Relatórios Avançados
- **RF013:** Geração de relatórios PDF/Excel com filtros temporais
- **RF015:** Filtros multicritério (categoria, tags, valor)

### Integrações
- **RF017:** Conexão com APIs bancárias (Santander, Itaú, Bradesco)
- **RF018:** Sincronização automática de transações

---

## ✅ Valor Entregue - 3ª Iteração

### Resumo:
Entrega de mecanismos avançados de controle e análise:  
✅ Planejamento orçamentário  
✅ Relatórios fiscais exportáveis  
✅ Integração com bancos nacionais  
✅ Gestão de assinaturas recorrentes

---

### Detalhamento de Valor

| User Story | Valor para o Usuário          | Valor para o Negócio           |
|------------|-------------------------------|---------------------------------|
| US010      | Controle preciso de gastos    | Dados para sugestões proativas |
| US012      | Compatibilidade com declarações fiscais | Atração de usuários corporativos |
| US014      | Atualização automática de dados | Redução de entrada manual     |
| US018      | Prevenção de gastos ocultos   | Aumento de valor percebido     |

---

## 📊 Protótipos Validados
- Dashboard de orçamentos com alertas visuais
- Módulo de relatórios com pré-visualização
- Wizard de conexão com bancos

---

## 🚀 Impacto
**Para o usuário:** Controle financeiro profissionalizado  
**Para o negócio:** Diferencial competitivo em integrações  
**Para a equipe:** Arquitetura preparada para escalar  

---

[Relatório do Projeto com a 3° Interação](Relatorio.md)

---

# ✅ 4ª Iteração: Notificações, Investimentos e Multiusuário

## User Stories

### US015 – Alertas de Saldo
**Como** usuário, **quero** receber alertas de saldo baixo **para** evitar descoberto.  
**Critérios de Aceite:**
- Configurar limite mínimo personalizado
- Notificações via e-mail/app

📸 ![Alertas](/prototipos/alertas.jpg)

---

### US016 – Gestão de Usuários (Admin)
**Como** admin, **quero** gerenciar contas de usuários **para** manter segurança do sistema.  
**Critérios de Aceite:**
- Ativar/desativar contas
- Visualizar logs de atividades

---

### US019 – Personalização Visual
**Como** usuário, **quero** escolher tema claro/escuro **para** melhor experiência.  
**Critérios de Aceite:**
- Alternância imediata entre temas
- Preferência salva automaticamente

---

### US020 – Controle de Investimentos
**Como** usuário, **quero** registrar meus investimentos **para** acompanhar rentabilidade.  
**Critérios de Aceite:**
- Cadastro de tipo, valor e data
- Gráfico de evolução histórica

---

### US021 – Conta Compartilhada
**Como** usuário, **quero** compartilhar acesso com familiares **para** gestão conjunta.  
**Critérios de Aceite:**
- Convite por e-mail
- Níveis de permissão diferenciados

---

## Requisitos Funcionais (RF)

### Notificações
- **RF016:** Sistema de alertas configurável para saldo e vencimentos

### Administração
- **RF019:** Painel de gestão de usuários e logs
- **RF021:** Controle granular de permissões

### Investimentos
- **RF024:** Registro detalhado de ativos financeiros
- **RF025:** Cálculo automático de rentabilidade

---

## ✅ Valor Entregue - 4ª Iteração

### Resumo:
Entrega de funcionalidades avançadas de colaboração e análise:  
✅ Sistema de alertas inteligentes  
✅ Gestão corporativa de usuários  
✅ Acompanhamento de investimentos  
✅ Contas compartilhadas com segurança

---

### Detalhamento de Valor

| User Story | Valor para o Usuário          | Valor para o Negócio           |
|------------|-------------------------------|---------------------------------|
| US015      | Prevenção de problemas bancários | Redução de chargebacks        |
| US020      | Visão completa do patrimônio  | Base para consultoria premium  |
| US021      | Gestão familiar de finanças   | Aumento de contas familiares  |
| US019      | Experiência personalizada     | Maior satisfação do usuário    |

---

## 🛡️ Requisitos Não Funcionais (RNF)

### Segurança
- **RNF004:** Implementação de 2FA para acesso compartilhado
- **RNF003:** Auditoria trimestral de segurança de dados

### Performance
- **RNF006:** Suporte a 50k transações simultâneas

### Usabilidade
- **RNF008:** Contrastes adequados para daltonismo nos temas

---

## 📈 Protótipos Validados
- Painel de investimentos com simulação de cenários
- Interface de convite para conta compartilhada
- Seletor de temas com pré-visualização

---

[Relatório do Projeto com a 4° Interação](Relatorio.md)