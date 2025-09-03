# 🔐 Como o Middleware Diferencia Métodos HTTP

## 🎯 **Problema Original**

O middleware anterior **NÃO diferenciava métodos HTTP**. Ele apenas verificava a rota:

```php
// ❌ PROBLEMA: Só verifica a rota, não o método
if (strpos($route, '/api/v1/produto') === 0) {
    // Isso afeta TODOS os métodos: GET, POST, PUT, DELETE
    $auth_result = middleware_autenticacao($request);
}
```

## ✅ **Solução Implementada**

Agora o middleware verifica **ROTA + MÉTODO HTTP**:

```php
$route = $request->get_route();    // /api/v1/produto/123
$method = $request->get_method();  // GET, POST, PUT, DELETE
```

## 🔄 **Fluxo de Autenticação**

```
Requisição → Middleware → Permission Callback → Endpoint
     ↓           ↓              ↓                ↓
   GET /produto → Verifica → __return_true → api_produto_get_single()
  POST /produto → Verifica → middleware_autenticacao → api_produto_post()
   PUT /produto → Verifica → middleware_autenticacao → api_produto_put()
```

## 📋 **Configuração por Rota e Método**

```php
$configuracao_rotas = array(
    '/api/v1/produto' => array(
        'GET' => 'publico',      // ✅ Buscar produto único - SEM autenticação
        'POST' => 'protegido',   // 🔒 Criar produto - COM autenticação
        'PUT' => 'protegido',    // 🔒 Atualizar produto - COM autenticação
        'DELETE' => 'protegido'  // 🔒 Deletar produto - COM autenticação
    ),
    
    '/api/v1/produtos' => array(
        'GET' => 'publico'       // ✅ Listar produtos - SEM autenticação
    ),
    
    '/api/v1/usuario' => array(
        'GET' => 'protegido',    // 🔒 Dados do usuário - COM autenticação
        'POST' => 'publico',     // ✅ Criar usuário - SEM autenticação
        'PUT' => 'protegido'     // 🔒 Atualizar usuário - COM autenticação
    )
);
```

## 🧪 **Testes de Funcionamento**

### ✅ **GET /api/v1/produto/123** (Público)
```bash
curl -s http://localhost:8000/wp-json/api/v1/produto/123
# Resultado: {"code":"produto_nao_encontrado","message":"Produto não encontrado.","data":{"status":404}}
```

### 🔒 **POST /api/v1/produto** (Protegido)
```bash
curl -s -X POST http://localhost:8000/wp-json/api/v1/produto \
  -H "Content-Type: application/json" \
  -d '{"nome": "Teste", "referencia": "TEST-001", "descricao": "Teste"}'
# Resultado: {"code":"nao_autenticado","message":"Token de autorização inválido ou ausente","data":{"status":401}}
```

## 🔍 **Como o WordPress REST API Funciona**

### 1. **Registro de Rotas**
```php
// GET /api/v1/produto/{id}
register_rest_route('api/v1', '/produto/(?P<id>[a-zA-Z0-9-]+)', array(
    array(
        'methods' => WP_REST_Server::READABLE,  // GET
        'callback' => 'api_produto_get_single',
        'permission_callback' => '__return_true'  // PÚBLICO
    )
));

// POST /api/v1/produto
register_rest_route('api/v1', '/produto', array(
    array(
        'methods' => WP_REST_Server::CREATABLE,  // POST
        'callback' => 'api_produto_post',
        'permission_callback' => '__return_true'  // PÚBLICO (mas middleware protege)
    )
));
```

### 2. **Ordem de Execução**
```
1. Requisição chega
2. Middleware intercepta (rest_pre_dispatch)
3. Se middleware permitir → Permission Callback
4. Se permission_callback permitir → Endpoint
5. Resposta é enviada
```

### 3. **Diferenciação por Método**
- **GET** = `WP_REST_Server::READABLE`
- **POST** = `WP_REST_Server::CREATABLE`
- **PUT** = `WP_REST_Server::EDITABLE`
- **DELETE** = `WP_REST_Server::DELETABLE`

## 🎯 **Vantagens da Nova Implementação**

1. **✅ Controle Granular**: Cada método HTTP pode ter regras diferentes
2. **✅ Configuração Centralizada**: Todas as regras em um lugar
3. **✅ Fácil Manutenção**: Adicionar/remover regras é simples
4. **✅ Logs Detalhados**: Pode logar qual método foi acessado
5. **✅ Flexibilidade**: Pode ter regras específicas por rota

## 🔧 **Como Adicionar Nova Rota**

```php
 está stamo
 // 1. Adicionar no array $configuracao_rotas do middleware
'/api/v1/nova-rota' => array(
    'GET' => 'publico',      // Listar - público
    'POST' => 'protegido',   // Criar - protegido
    'PUT' => 'protegido',    // Atualizar - protegido
    'DELETE' => 'protegido'  // Deletar - protegido
)

// 2. Registrar a rota com permission_callback = '__return_true'
register_rest_route('api/v1', '/nova-rota', array(
    array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_nova_rota_get',
        'permission_callback' => '__return_true'  // ✅ SEMPRE usar __return_true
    )
));
```

## ⚠️ **IMPORTANTE: Configuração Correta dos Endpoints**

### ✅ **CORRETO:**
```php
'permission_callback' => '__return_true'  // Middleware personalizado faz a verificação
```

### ❌ **INCORRETO:**
```php
'permission_callback' => 'middleware_autenticacao'  // Conflito com middleware
'permission_callback' => function() { return is_user_logged_in(); }  // Não funciona com JWT
```

## 📊 **Resumo das Rotas**

| Rota | GET | POST | PUT | DELETE |
|------|-----|------|-----|--------|
| `/api/v1/produto` | ✅ Público | 🔒 Protegido | 🔒 Protegido | 🔒 Protegido |
| `/api/v1/produtos` | ✅ Público | - | - | - |
| `/api/v1/usuario` | 🔒 Protegido | ✅ Público | 🔒 Protegido | - |
| `/api/v1/usuario/login` | - | ✅ Público | - | - |
| `/api/v1/documentacao` | ✅ Público | - | - | - |
| `/api/v1/estatisticas` | 🔒 Protegido | - | - | - |
| `/api/v1/transacao` | 🔒 Protegido | 🔒 Protegido | - | - |

**Legenda:**
- ✅ Público = Não precisa de autenticação
- 🔒 Protegido = Precisa de token JWT
- - = Método não disponível
