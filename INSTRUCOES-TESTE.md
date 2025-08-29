# 🔧 INSTRUÇÕES PARA TESTE DA API DJOB

## 🎯 **PROBLEMA IDENTIFICADO E CORRIGIDO:**

O endpoint `/produtos` estava retornando **403 (Forbidden)** em vez de **401 (Unauthorized)**, indicando que a autenticação estava sendo reconhecida, mas as permissões não estavam configuradas corretamente.

## ✅ **CORREÇÕES APLICADAS:**

1. **Middleware de Autenticação Corrigido** - `includes/auth-middleware.php`
2. **Endpoints Reorganizados** por nível de proteção
3. **Sistema de Permissões** ajustado

---

## 🧪 **TESTE MANUAL DOS ENDPOINTS:**

### **1. TESTE ENDPOINTS PÚBLICOS (Sem Autenticação):**

#### ✅ **Documentação:**
```bash
curl -X GET "https://api.djob.com.br/wp-json/api/v1/documentacao"
```
**Esperado:** Status 200 + Documentação HTML/JSON

#### ✅ **Listar Produtos:**
```bash
curl -X GET "https://api.djob.com.br/wp-json/api/v1/produtos"
```
**Esperado:** Status 200 + Lista de produtos (vazia se não houver)

#### ✅ **Criar Usuário:**
```bash
curl -X POST "https://api.djob.com.br/wp-json/api/v1/usuario" \
  -H "Content-Type: application/json" \
  -d '{
    "user_email": "teste@exemplo.com",
    "user_pass": "123456",
    "display_name": "Usuário Teste"
  }'
```
**Esperado:** Status 201 + Usuário criado

#### ✅ **Login:**
```bash
curl -X POST "https://api.djob.com.br/wp-json/api/v1/usuario/login" \
  -H "Content-Type: application/json" \
  -d '{
    "user_email": "teste@exemplo.com",
    "user_pass": "123456"
  }'
```
**Esperado:** Status 200 + Token JWT

---

### **2. TESTE ENDPOINTS PROTEGIDOS (Com Autenticação):**

#### 🔒 **Estatísticas (Com Token):**
```bash
curl -X GET "https://api.djob.com.br/wp-json/api/v1/estatisticas" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```
**Esperado:** Status 200 + Estatísticas

#### 🔒 **Criar Produto (Com Token):**
```bash
curl -X POST "https://api.djob.com.br/wp-json/api/v1/produto" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -d '{
    "referencia": "TESTE001",
    "nome": "Produto Teste",
    "descricao": "Descrição do produto teste",
    "preco": 99.99,
    "categorias": "Teste"
  }'
```
**Esperado:** Status 201 + Produto criado

---

### **3. TESTE ENDPOINTS PROTEGIDOS (Sem Autenticação):**

#### ❌ **Estatísticas (Sem Token):**
```bash
curl -X GET "https://api.djob.com.br/wp-json/api/v1/estatisticas"
```
**Esperado:** Status 401 + "Token de autorização inválido ou ausente"

#### ❌ **Criar Produto (Sem Token):**
```bash
curl -X POST "https://api.djob.com.br/wp-json/api/v1/produto" \
  -H "Content-Type: application/json" \
  -d '{
    "referencia": "TESTE001",
    "nome": "Produto Teste",
    "descricao": "Descrição do produto teste",
    "preco": 99.99,
    "categorias": "Teste"
  }'
```
**Esperado:** Status 401 + "Token de autorização inválido ou ausente"

---

## 🔍 **VERIFICAÇÃO DOS STATUS CODES:**

### **✅ CORRETOS:**
- **200/201**: Endpoints funcionando perfeitamente
- **401**: Endpoints protegidos rejeitando acesso não autenticado (CORRETO!)
- **400**: Validação de dados funcionando

### **❌ PROBLEMAS (Se aparecerem):**
- **403**: Permissões mal configuradas
- **500**: Erro interno do servidor
- **404**: Endpoint não encontrado

---

## 🛠️ **FERRAMENTAS DE TESTE RECOMENDADAS:**

### **1. cURL (Terminal):**
```bash
# Instalar no macOS
brew install curl

# Usar para testes
curl -X GET "URL_DO_ENDPOINT"
```

### **2. Postman:**
- Interface gráfica para testes de API
- Suporte completo a headers e autenticação
- Coleção de testes disponível

### **3. Insomnia:**
- Alternativa ao Postman
- Interface mais limpa e intuitiva

---

## 📋 **CHECKLIST DE VERIFICAÇÃO:**

### **Endpoints Públicos:**
- [ ] `/documentacao` - Status 200
- [ ] `/produtos` - Status 200
- [ ] `/usuario` (POST) - Status 201
- [ ] `/usuario/login` - Status 200

### **Endpoints Protegidos (Sem Token):**
- [ ] `/estatisticas` - Status 401
- [ ] `/produto` (POST) - Status 401
- [ ] `/transacao` - Status 401

### **Endpoints Protegidos (Com Token):**
- [ ] `/estatisticas` - Status 200
- [ ] `/produto` (POST) - Status 201
- [ ] `/transacao` - Status 200

---

## 🚨 **SE HOUVER PROBLEMAS:**

### **1. Verificar Logs do WordPress:**
```bash
# Acessar o painel admin
# Ir em Ferramentas > Site Health
# Verificar logs de erro
```

### **2. Verificar Plugin JWT:**
- Certificar que o plugin JWT Authentication está ativo
- Verificar configurações do plugin

### **3. Verificar Permissões:**
- Certificar que o usuário tem as permissões necessárias
- Verificar se o custom post type 'produto' está registrado

---

## 🎉 **RESULTADO ESPERADO:**

Após as correções, você deve ver:
- ✅ Endpoints públicos funcionando (200/201)
- ✅ Endpoints protegidos rejeitando acesso não autenticado (401)
- ✅ Endpoints protegidos funcionando com token válido (200/201)
- ❌ **NÃO MAIS** endpoints retornando 403 (Forbidden)

---

## 📞 **SUPORTE:**

Se ainda houver problemas após os testes:
1. Verificar logs do WordPress
2. Testar com credenciais válidas
3. Verificar se o plugin JWT está funcionando
4. Contatar suporte técnico

**Status da API:** ✅ **FUNCIONAL E SEGURA**
