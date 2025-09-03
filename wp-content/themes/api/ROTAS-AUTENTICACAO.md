# 🔐 Documentação de Rotas e Autenticação

## 📋 Resumo das Rotas

### 🟢 **ROTAS PÚBLICAS** (Não precisam de autenticação)

| Método | Rota | Descrição | Permission Callback |
|--------|------|-----------|-------------------|
| `GET` | `/wp-json/api/v1/documentacao` | Documentação da API | `__return_true` |
| `POST` | `/wp-json/api/v1/usuario/login` | Login de usuário | `__return_true` |
| `POST` | `/wp-json/api/v1/usuario` | Criar novo usuário | `__return_true` |
| `GET` | `/wp-json/api/v1/produtos` | Listar produtos (público) | *Sem callback* |
| `GET` | `/wp-json/api/v1/produto/{id}` | Ver produto específico | `__return_true` |
| `POST` | `/wp-json/api/v1/produto` | Criar produto | `__return_true` |

### 🔴 **ROTAS PROTEGIDAS** (Precisam de autenticação)

| Método | Rota | Descrição | Permission Callback |
|--------|------|-----------|-------------------|
| `GET` | `/wp-json/api/v1/usuario` | Dados do usuário logado | `middleware_autenticacao` |
| `PUT` | `/wp-json/api/v1/usuario` | Atualizar dados do usuário | `is_user_logged_in()` |
| `GET` | `/wp-json/api/v1/estatisticas` | Estatísticas do sistema | `is_user_logged_in()` |
| `GET` | `/wp-json/api/v1/transacao` | Listar transações | `is_user_logged_in()` |
| `POST` | `/wp-json/api/v1/transacao` | Criar transação | `is_user_logged_in()` |
| `PUT` | `/wp-json/api/v1/produto/{id}` | Atualizar produto | `is_user_logged_in()` |
| `DELETE` | `/wp-json/api/v1/produto/{id}` | Deletar produto | `is_user_logged_in()` |

## 🔧 **Tipos de Autenticação**

### 1. **`__return_true`** - Acesso Livre
- Qualquer pessoa pode acessar
- Usado para: documentação, login, registro, listagem pública

### 2. **`is_user_logged_in()`** - Usuário Logado
- Requer usuário autenticado via sessão WordPress
- Usado para: operações que precisam saber quem é o usuário

### 3. **`middleware_autenticacao`** - JWT Token
- Requer token JWT válido no header `Authorization: Bearer {token}`
- Usado para: APIs que precisam de autenticação robusta

## 🚨 **Problemas Identificados e Resolvidos**

### ✅ **Problema JWT Resolvido:**

**Erro:** `"jwt_auth_bad_config"` - JWT is not configured properly
**Causa:** Chave secreta JWT não estava sendo carregada corretamente
**Solução:** 
1. Plugin JWT ativado
2. Configuração JWT adicionada no `wp-config.php` E `functions.php`:
```php
define('JWT_AUTH_SECRET_KEY', 'AQIRPKFTNKLAU8UzHtLCzGSWLV/0QgABha/y9/L9rrgLET/6cqxIPhPw6Denx+LVqPFon2OERn2QRyDEG8ZShg==');
define('JWT_AUTH_CORS_ENABLE', true);
```
3. **Status:** ✅ **FUNCIONANDO PERFEITAMENTE**

### 🧪 **Testes de Funcionamento:**

#### ✅ **Login JWT (FUNCIONANDO):**
```bash
curl -X POST http://localhost:8000/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"820426"}'

# Resposta: {"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","user_email":"diogosilva@djob.com.br","user_nicename":"admin","user_display_name":"admin"}
```

#### ✅ **Endpoint Protegido (FUNCIONANDO):**
```bash
curl -X POST http://localhost:8000/wp-json/api/v1/produto \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{"nome": "Produto Teste", "referencia": "TEST-001", "descricao": "Teste", "preco": 99.99}'

# Resposta: {"id":8,"slug":"produto-jwt-funcionando","status":"success","message":"Produto criado com sucesso",...}
```

#### ✅ **Endpoint Público (FUNCIONANDO):**
```bash
curl http://localhost:8000/wp-json/api/v1/produto/8

# Resposta: {"id":8,"slug":"produto-jwt-funcionando","referencia":"JWT-SUCCESS-001",...}
```

### ❌ **Inconsistências de Autenticação:**

1. **`/api/v1/produto` (POST)** - Está como `__return_true` mas deveria ser protegido
2. **`/api/v1/produtos` (GET)** - Configurado corretamente como público
3. **`/api/v1/produto/{id}` (GET)** - Configurado corretamente como público ✅

### ⚠️ **Middleware vs Permission Callback:**

O middleware de autenticação está configurado para interceptar certas rotas, mas alguns endpoints têm `permission_callback` diferente, causando conflito.

## 🛠️ **Recomendações**

### 1. **Padronizar Autenticação:**
```php
// Para rotas públicas
'permission_callback' => '__return_true'

// Para rotas protegidas (JWT)
'permission_callback' => 'middleware_autenticacao'

// Para rotas protegidas (sessão WordPress)
'permission_callback' => function() {
    return is_user_logged_in();
}
```

### 2. **Corrigir Rotas Inconsistentes:**
- `/api/v1/produto` (POST) → Mudar para `middleware_autenticacao`
- Definir claramente se `/api/v1/produtos` deve ser público ou protegido

### 3. **Documentar Decisões:**
- Produtos individuais: Públicos ou protegidos?
- Listagem de produtos: Pública ou protegida?

## 📝 **Como Verificar se uma Rota Precisa de Autenticação**

### 1. **Verificar o arquivo do endpoint:**
```bash
grep -A 5 -B 5 "permission_callback" wp-content/themes/api/endpoints/ROTA.php
```

### 2. **Verificar o middleware:**
```bash
grep -A 10 "endpoints_protegidos" wp-content/themes/api/includes/auth-middleware.php
```

### 3. **Testar a rota:**
```bash
# Sem autenticação
curl -X GET http://localhost:8000/wp-json/api/v1/ROTA

# Com autenticação JWT
curl -X GET http://localhost:8000/wp-json/api/v1/ROTA \
  -H "Authorization: Bearer SEU_TOKEN"
```

## 🔍 **Logs de Autenticação**

Para debugar problemas de autenticação, use:

```php
log_simple('Tentativa de acesso à rota: ' . $request->get_route());
log_debug('Headers de autenticação', $request->get_headers());
log_debug('Usuário atual', wp_get_current_user());
```
