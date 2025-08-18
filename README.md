# Sistema de Locadora de Carros

Sistema completo de gestão de locadora de carros desenvolvido em PHP 8.1+ com SQLite.

## 🚀 Deploy Gratuito

### Opção 1: Railway (Recomendado)
1. Faça fork deste repositório
2. Acesse [Railway.app](https://railway.app)
3. Conecte sua conta GitHub
4. Selecione "Deploy from GitHub repo"
5. Escolha seu fork do projeto
6. Railway detectará automaticamente o PHP e fará o deploy

### Opção 2: Render
1. Acesse [Render.com](https://render.com)
2. Conecte sua conta GitHub
3. Crie um novo "Web Service"
4. Selecione seu repositório
5. Configure:
   - Environment: `PHP`
   - Build Command: `composer install`
   - Start Command: `php -S 0.0.0.0:$PORT -t public`

### Opção 3: Heroku
1. Acesse [Heroku.com](https://heroku.com)
2. Crie uma nova app
3. Conecte ao GitHub
4. Faça deploy automático

## 🏗️ Estrutura do Projeto

```
sistema_de_locadora_de_carros/
├── config/                 # Configurações
├── core/                   # Classes principais
├── database/              # Banco de dados SQLite
├── public/                # Arquivos públicos
├── src/                   # Código fonte
│   ├── controllers/       # Controladores
│   ├── models/           # Modelos
│   ├── views/            # Views
│   ├── routes/           # Rotas API
│   ├── services/         # Serviços
│   └── middlewares/      # Middlewares
└── tests/                # Testes (futuro)
```

## 🔧 Configuração Local

1. Clone o repositório
2. Execute: `php -S localhost:8000 -t public`
3. Acesse: `http://localhost:8000`

## 📱 Funcionalidades

- ✅ **Autenticação**: Login/logout seguro
- ✅ **Gestão de Usuários**: CRUD completo (admin)
- ✅ **Gestão de Carros**: CRUD completo
- ✅ **Sistema de Aluguéis**: CRUD com validações
- ✅ **Perfil do Usuário**: Edição de dados pessoais
- ✅ **Controle de Permissões**: Admin vs Usuário comum
- ✅ **Interface Responsiva**: Design moderno

## 🔒 Usuários Padrão

- **Admin**: admin@admin.com / admin
- **Usuário**: user@test.com / user

## 🛠️ Tecnologias

- PHP 8.1+
- SQLite 3
- HTML5/CSS3/JavaScript
- Design System baseado em shadcn/ui

## 🌐 URLs de Deploy Gratuito

- **Railway**: Até 500 horas/mês grátis
- **Render**: 750 horas/mês grátis  
- **Heroku**: 550 horas/mês grátis
- **Vercel**: Limitado para PHP, mas possível com serverless

## 📋 Checklist de Deploy

- [x] Configurações de produção criadas
- [x] Arquivos de deploy configurados
- [x] README com instruções completas
- [x] Sistema testado e funcionando
- [x] Erro de data no aluguel corrigido

## 🐛 Correções Recentes

- ✅ **Data de Aluguel**: Corrigido problema de "1 dia antes"
- ✅ **Timezone**: Configuração correta de fuso horário
- ✅ **Cálculo de Dias**: Inclusão correta do dia de início