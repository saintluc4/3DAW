# 🌟 Gold Touch — Estética & Beleza
## Site completo: JS → PHP → MySQL (JSON)

---

## 📁 Estrutura do projeto

```
goldtouch/
├── index.php              ← SPA principal (HTML + chamadas ao JS)
├── database.sql           ← Schema + dados iniciais do banco
│
├── api/
│   ├── auth.php           ← Login, registro, logout, sessão
│   ├── servicos.php       ← Listar serviços, horários disponíveis
│   ├── agendamentos.php   ← Criar, listar, cancelar agendamentos
│   └── extras.php         ← Avaliações, cupons, planos
│
├── includes/
│   └── config.php         ← Conexão PDO, helpers JSON, sessão
│
├── css/
│   └── style.css          ← Design completo (variáveis, componentes)
│
└── js/
    └── app.js             ← Toda a camada de requisições e UI
```

---

## 🚀 Instalação

### 1. Requisitos
- PHP 8.0+
- MySQL 5.7+ ou MariaDB 10.3+
- Apache/Nginx com mod_rewrite (ou servidor PHP embutido)

### 2. Banco de dados

```bash
# Acesse o MySQL e importe o schema
mysql -u root -p < database.sql
```

Ou pelo phpMyAdmin: importe o arquivo `database.sql`.

### 3. Configuração

Edite `includes/config.php` com suas credenciais:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // seu usuário MySQL
define('DB_PASS', 'sua_senha');  // sua senha MySQL
define('DB_NAME', 'goldtouch');
```

### 4. Servidor local

**Opção A — PHP embutido:**
```bash
cd goldtouch/
php -S localhost:8080
# Acesse: http://localhost:8080
```

**Opção B — XAMPP/WAMP:**
- Copie a pasta para `htdocs/goldtouch`
- Acesse: `http://localhost/goldtouch`

**Opção C — Apache vhost:**
```apache
<VirtualHost *:80>
    ServerName goldtouch.local
    DocumentRoot /var/www/goldtouch
    <Directory /var/www/goldtouch>
        AllowOverride All
    </Directory>
</VirtualHost>
```

---

## 🔄 Fluxo de dados (JS → PHP → SQL → JSON)

```
Usuário clica "Agendar"
    │
    ▼
app.js: API.agendamentos.criar(payload)
    │
    ▼
fetch('api/agendamentos.php?action=criar', { method: 'POST', body: JSON })
    │
    ▼
agendamentos.php:
    ├── Valida dados recebidos
    ├── Consulta SQL (verifica conflito de horário)
    ├── INSERT INTO agendamentos (...)
    ├── UPDATE clientes SET pontos = pontos + 50
    └── json_response(['sucesso' => true, 'agendamento_id' => $id, ...])
    │
    ▼
app.js: recebe JSON → atualiza UI → mostra toast + modal de confirmação
```

---

## 📡 Endpoints da API

### Auth (`api/auth.php`)
| Método | ?action=    | Body                          | Retorno                        |
|--------|-------------|-------------------------------|--------------------------------|
| POST   | registro    | {nome, email, telefone, senha}| {sucesso, cliente}             |
| POST   | login       | {email, senha}                | {sucesso, cliente}             |
| POST   | logout      | —                             | {sucesso}                      |
| GET    | sessao      | —                             | {logado, cliente?}             |

### Serviços (`api/servicos.php`)
| Método | ?action=  | Query params          | Retorno                |
|--------|-----------|-----------------------|------------------------|
| GET    | listar    | &categoria=cabelo     | {servicos: [...]}      |
| GET    | horarios  | &servico_id=1&data=.. | {slots: [...]}         |

### Agendamentos (`api/agendamentos.php`)
| Método | ?action=  | Body / Params             | Retorno                |
|--------|-----------|---------------------------|------------------------|
| POST   | criar     | {servico_id, data, hora, forma_pagamento, cupom} | {sucesso, agendamento_id, valor, ...} |
| GET    | meus      | —                         | {agendamentos: [...]}  |
| POST   | cancelar  | {agendamento_id}          | {sucesso}              |

### Extras (`api/extras.php`)
| Método | ?endpoint= | ?action=  | Retorno                |
|--------|------------|-----------|------------------------|
| POST   | avaliacoes | enviar    | {sucesso, mensagem}    |
| GET    | avaliacoes | listar    | {avaliacoes: [...]}    |
| GET    | cupons     | listar    | {cupons: [...]}        |
| POST   | cupons     | validar   | {valido, desconto}     |
| GET    | planos     | listar    | {planos: [...]}        |

---

## 🎨 Funcionalidades implementadas

- ✅ **SPA** — navegação sem recarregar página
- ✅ **Login / Registro** — com hash bcrypt, sessão PHP
- ✅ **Catálogo de serviços** — filtro por categoria via AJAX
- ✅ **Agendamento em 3 steps** — calendário, horários em tempo real, confirmação
- ✅ **Verificação de conflito** — horários já ocupados são bloqueados no banco
- ✅ **Cupons** — validação server-side, desconto automático
- ✅ **Sistema de pontos** — +50 pontos por serviço, cupons resgatáveis
- ✅ **Avaliações** — formulário + listagem pública
- ✅ **Planos mensais** — exibição dinâmica do banco
- ✅ **Minha conta** — histórico de agendamentos, cancelamento
- ✅ **Toast notifications** — feedback visual para todas as ações
- ✅ **Design responsivo** — mobile-first com CSS variables
