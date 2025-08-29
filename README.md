# 📚 Documentação da API - Sistema de E-commerce WordPress

## 🚀 **Visão Geral**

Esta API REST foi desenvolvida para um sistema de marketplace/e-commerce em WordPress, permitindo gerenciar usuários, produtos e transações de forma robusta e segura.

## 🧭 **Navegação Rápida**

### **📖 Documentação**
- **[Documentação OpenAPI](https://api.djob.com.br/wp-json/api/v1/documentacao)** - Especificação técnica completa
- **[Documentação HTML](https://api.djob.com.br/wp-json/api/v1/documentacao?format=html)** - Interface visual organizada
- **[API Principal](https://api.djob.com.br/wp-json/api/v1/)** - Endpoint raiz com todos os recursos

### **🧪 Teste de Endpoints**
- **[Listar Produtos](https://api.djob.com.br/wp-json/api/v1/produtos)** - Teste de busca e filtros
- **[Criar Usuário](https://api.djob.com.br/wp-json/api/v1/usuario)** - Teste de cadastro
- **[Login](https://api.djob.com.br/wp-json/api/v1/usuario/login)** - Teste de autenticação
- **[Estatísticas](https://api.djob.com.br/wp-json/api/v1/estatisticas)** - Teste de relatórios

### **🔗 Links Úteis**
- **Base URL**: `https://api.djob.com.br/wp-json/api/v1/`
- **WordPress Admin**: `https://api.djob.com.br/wp-admin/`
- **Status da API**: `https://api.djob.com.br/wp-json/`

## 🔐 **Autenticação**

A API utiliza **JWT (JSON Web Tokens)** para autenticação. Todos os endpoints protegidos requerem um token válido no header:

```
Authorization: Bearer {seu_token_jwt}
```

## 📍 **Base URL**

```
https://api.djob.com.br/wp-json/api/v1/
```

## 📖 **Documentação Automática**

### **GET** `/documentacao` - Documentação da API

Retorna documentação completa da API em formato OpenAPI 3.0.

**Parâmetros de Query:**
- `format` (opcional): `json` ou `html` (padrão: `json`)

**Exemplos de Uso:**
```
GET /documentacao                    # Retorna OpenAPI JSON
GET /documentacao?format=json       # Retorna OpenAPI JSON
GET /documentacao?format=html       # Retorna HTML formatado
```

**Resposta JSON (OpenAPI 3.0):**
```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "API Sistema de E-commerce WordPress",
    "version": "1.0.0"
  },
  "paths": {
    "/usuario": { ... },
    "/produtos": { ... }
  }
}
```

**Resposta HTML:**
- Interface visual organizada por categorias
- Navegação lateral com links para seções
- Exemplos de uso e parâmetros
- Formatação com Bootstrap e Prism.js

### **Swagger UI Interativo**

Para uma experiência completa de documentação interativa, crie uma página no WordPress usando o template `swagger-ui.php`:

1. **Criar nova página** no WordPress
2. **Selecionar template** "Swagger UI - Documentação da API"
3. **Acessar a página** para interface interativa

**Funcionalidades do Swagger UI:**
- ✅ **Teste direto** dos endpoints
- ✅ **Autenticação JWT** integrada
- ✅ **Validação** de parâmetros
- ✅ **Exemplos** de requisições
- ✅ **Respostas** em tempo real
- ✅ **Download** da especificação OpenAPI

## 👥 **Usuários**

### **POST** `/usuario` - Criar Usuário

Cria um novo usuário no sistema.

**Dados de Entrada:**
```json
{
  "user_email": "usuario@exemplo.com",
  "user_pass": "senha123",
  "display_name": "Nome Completo",
  "first_name": "Nome",
  "last_name": "Sobrenome",
  "endereco": {
    "rua": "Rua das Flores",
    "cep": "12345-678",
    "numero": "123",
    "bairro": "Centro",
    "cidade": "São Paulo",
    "estado": "SP",
    "complemento": "Apto 45"
  },
  "telefone": "(11) 99999-9999",
  "cpf_cnpj": "123.456.789-00",
  "data_nascimento": "1990-01-01",
  "genero": "masculino"
}
```

**Resposta de Sucesso:**
```json
{
  "status": "success",
  "message": "Usuário criado com sucesso",
  "usuario": {
    "id": 123,
    "user_login": "usuario@exemplo.com",
    "display_name": "Nome Completo",
    "first_name": "Nome",
    "last_name": "Sobrenome",
    "user_email": "usuario@exemplo.com",
    "user_registered": "2024-01-15T10:30:00",
    "role": "subscriber",
    "status": "active",
    "endereco": {
      "cep": "12345-678",
      "rua": "Rua das Flores",
      "numero": "123",
      "bairro": "Centro",
      "cidade": "São Paulo",
      "estado": "SP",
      "complemento": "Apto 45"
    },
    "telefone": "(11) 99999-9999",
    "cpf_cnpj": "123.456.789-00",
    "data_nascimento": "1990-01-01",
    "genero": "masculino",
    "avatar": "https://api.djob.com.br/avatar/123.jpg",
    "preferencias": {
      "notificacoes_email": true,
      "notificacoes_push": true,
      "newsletter": false
    }
  },
  "data_criacao": "2024-01-15T10:30:00Z"
}
```

### **POST** `/usuario/login` - Login de Usuário

Realiza autenticação do usuário e retorna dados do perfil.

**Dados de Entrada:**
```json
{
  "user_email": "usuario@exemplo.com",
  "user_pass": "senha123"
}
```

**Resposta de Sucesso:**
```json
{
  "status": "success",
  "message": "Login realizado com sucesso",
  "usuario": {
    "id": 123,
    "user_login": "usuario@exemplo.com",
    "display_name": "Nome Completo",
    "first_name": "Nome",
    "last_name": "Sobrenome",
    "user_email": "usuario@exemplo.com",
    "user_registered": "2024-01-15T10:30:00",
    "role": "subscriber",
    "status": "active",
    "endereco": {
      "cep": "12345-678",
      "rua": "Rua das Flores",
      "numero": "123",
      "bairro": "Centro",
      "cidade": "São Paulo",
      "estado": "SP",
      "complemento": "Apto 45"
    },
    "telefone": "(11) 99999-9999",
    "cpf_cnpj": "123.456.789-00",
    "data_nascimento": "1990-01-01",
    "genero": "masculino",
    "avatar": "https://api.djob.com.br/avatar/123.jpg",
    "ultimo_login": "2024-01-15T10:30:00",
    "preferencias": {
      "notificacoes_email": true,
      "notificacoes_push": true,
      "newsletter": false
    }
  },
  "data_login": "2024-01-15T10:30:00Z",
  "token_info": {
    "note": "Use o token JWT do WordPress para autenticação em endpoints protegidos",
    "endpoint": "/wp-json/jwt-auth/v1/token"
  }
}
```

### **GET** `/usuario` - Buscar Usuário

Retorna informações completas do usuário logado, incluindo estatísticas.

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "id": 123,
  "user_login": "usuario@exemplo.com",
  "display_name": "Nome Completo",
  "first_name": "Nome",
  "last_name": "Sobrenome",
  "user_email": "usuario@exemplo.com",
  "user_registered": "2024-01-15T10:30:00",
  "role": "subscriber",
  "status": "active",
  "endereco": {
    "cep": "12345-678",
    "rua": "Rua das Flores",
    "numero": "123",
    "bairro": "Centro",
    "cidade": "São Paulo",
    "estado": "SP",
    "complemento": "Apto 45"
  },
  "telefone": "(11) 99999-9999",
  "cpf_cnpj": "123.456.789-00",
  "data_nascimento": "1990-01-01",
  "genero": "masculino",
  "avatar": "https://api.djob.com.br/avatar/123.jpg",
  "ultimo_login": "2024-01-15T10:30:00",
  "preferencias": {
    "notificacoes_email": true,
    "notificacoes_push": true,
    "newsletter": false
  },
  "estatisticas": {
    "total_produtos": 15,
    "produtos_vendidos": 8,
    "total_transacoes": 12,
    "data_cadastro": "2024-01-15T10:30:00"
  }
}
```

### **PUT** `/usuario` - Atualizar Usuário

Atualiza dados do usuário logado. Permite atualização parcial de campos.

**Headers:**
```
Authorization: Bearer {token}
```

**Dados de Entrada (exemplo de atualização parcial):**
```json
{
  "display_name": "Novo Nome",
  "endereco": {
    "cidade": "Nova Cidade",
    "estado": "RJ"
  },
  "telefone": "(21) 88888-8888",
  "preferencias": {
    "newsletter": true
  }
}
```

**Resposta de Sucesso:**
```json
{
  "status": "success",
  "message": "Usuário atualizado com sucesso",
  "usuario_id": 123,
  "campos_atualizados": {
    "display_name": "Novo Nome",
    "endereco": {
      "cidade": "Nova Cidade",
      "estado": "RJ"
    },
    "telefone": "(21) 88888-8888",
    "preferencias": {
      "newsletter": true
    }
  },
  "data_atualizacao": "2024-01-15T10:30:00Z"
}
```

## 🛍️ **Produtos**

### **POST** `/produto` - Criar Produto

Cria um novo produto no sistema.

**Headers:**
```
Authorization: Bearer {token}
```

**Dados de Entrada:**
```json
{
  "referencia": "SP-94690",
  "nome": "Nome do Produto",
  "descricao": "Descrição detalhada do produto",
  "preco": 29.90,
  "categorias": "Garrafas",
  "cores": [
    {
      "nome": "Cromado satinado",
      "imagem": "/fotos/opcionais/127_95942891861fd2ed30cc0a.png",
      "tipo": "imagem",
      "codigoNumerico": "127"
    }
  ],
  "imagens": [
    "https://exemplo.com/imagem1.jpg",
    "https://exemplo.com/imagem2.jpg"
  ],
  "informacoes_adicionais": "Informações técnicas adicionais"
}
```

**Resposta de Sucesso:**
```json
{
  "id": 456,
  "slug": "nome-do-produto",
  "status": "success",
  "message": "Produto criado com sucesso"
}
```

### **GET** `/produtos` - Listar Produtos

Lista produtos com filtros e paginação.

**Parâmetros de Query:**
- `page` (opcional): Número da página (padrão: 1)
- `per_page` (opcional): Itens por página (padrão: 10, máximo: 100)
- `search` (opcional): Termo de busca
- `categoria` (opcional): Filtrar por categoria
- `preco_min` (opcional): Preço mínimo
- `preco_max` (opcional): Preço máximo
- `status` (opcional): `disponivel`, `vendido` ou `todos` (padrão: `disponivel`)
- `ordenar_por` (opcional): `date`, `title`, `preco`, `referencia` (padrão: `date`)
- `ordenar` (opcional): `ASC` ou `DESC` (padrão: `DESC`)
- `referencia` (opcional): Filtrar por referência

**Exemplo de Uso:**
```
GET /produtos?page=1&per_page=20&categoria=Garrafas&preco_min=10&preco_max=50&ordenar_por=preco&ordenar=ASC
```

**Resposta:**
```json
{
  "produtos": [
    {
      "id": 456,
      "slug": "nome-do-produto",
      "referencia": "SP-94690",
      "nome": "Nome do Produto",
      "descricao": "Descrição do produto",
      "cores": [...],
      "imagens": [...],
      "categorias": "Garrafas",
      "informacoes_adicionais": "...",
      "preco": 29.90,
      "vendido": false,
      "usuario_id": "usuario@exemplo.com",
      "data_criacao": "2024-01-15T10:30:00Z",
      "data_modificacao": "2024-01-15T10:30:00Z",
      "autor": {
        "id": 123,
        "nome": "Nome Completo",
        "email": "usuario@exemplo.com"
      }
    }
  ],
  "paginacao": {
    "pagina_atual": 1,
    "por_pagina": 20,
    "total_produtos": 150,
    "total_paginas": 8,
    "tem_proxima": true,
    "tem_anterior": false
  },
  "filtros_aplicados": {
    "search": "",
    "categoria": "Garrafas",
    "preco_min": 10,
    "preco_max": 50,
    "status": "disponivel",
    "ordenar_por": "preco",
    "ordenar": "ASC"
  }
}
```

### **GET** `/produto/{id}` - Buscar Produto Específico

Retorna dados de um produto específico por ID ou slug.

**Exemplo:**
```
GET /produto/456
GET /produto/nome-do-produto
```

### **PUT** `/produto/{id}` - Atualizar Produto

Atualiza um produto existente (apenas o dono pode editar).

**Headers:**
```
Authorization: Bearer {token}
```

**Dados de Entrada:**
```json
{
  "nome": "Novo Nome do Produto",
  "preco": 39.90,
  "categorias": "Novas Categorias"
}
```

### **DELETE** `/produto/{id}` - Excluir Produto

Remove um produto do sistema (apenas o dono pode excluir).

**Headers:**
```
Authorization: Bearer {token}
```

## 💰 **Transações**

### **POST** `/transacao` - Criar Transação

Registra uma nova transação de compra/venda.

**Headers:**
```
Authorization: Bearer {token}
```

**Dados de Entrada:**
```json
{
  "produto": {
    "id": "nome-do-produto",
    "nome": "Nome do Produto",
    "vendido": "false"
  },
  "comprador_id": "comprador@exemplo.com",
  "vendedor_id": "vendedor@exemplo.com",
  "endereco": {
    "rua": "Rua das Flores",
    "numero": "123",
    "bairro": "Centro",
    "cidade": "São Paulo",
    "estado": "SP",
    "cep": "12345-678"
  }
}
```

### **GET** `/transacao` - Listar Transações

Lista transações do usuário logado.

**Headers:**
```
Authorization: Bearer {token}
```

## 📊 **Estatísticas**

### **GET** `/estatisticas` - Relatórios e Estatísticas

Retorna estatísticas detalhadas sobre produtos e transações.

**Headers:**
```
Authorization: Bearer {token}
```

**Parâmetros de Query:**
- `tipo` (opcional): `geral`, `produtos`, `vendas`, `categorias` (padrão: `geral`)
- `periodo` (opcional): `7dias`, `30dias`, `90dias`, `6meses`, `1ano`, `todos` (padrão: `30dias`)

**Exemplo de Uso:**
```
GET /estatisticas?tipo=produtos&periodo=90dias
```

**Resposta de Estatísticas Gerais:**
```json
{
  "status": "success",
  "tipo": "geral",
  "periodo": "30dias",
  "data_geracao": "2024-01-15T10:30:00Z",
  "estatisticas": {
    "produtos": {
      "total_produtos": 25,
      "produtos_vendidos": 8,
      "produtos_disponiveis": 17,
      "taxa_venda": 32.0,
      "valor_total": 1250.00,
      "valor_vendido": 400.00,
      "valor_disponivel": 850.00
    },
    "vendas": {
      "total_transacoes": 8,
      "vendas_como_vendedor": 8,
      "compras_como_comprador": 0,
      "valor_total_vendas": 400.00,
      "valor_total_compras": 0.00,
      "saldo": 400.00
    },
    "categorias": [
      {
        "categoria": "Garrafas",
        "total_produtos": 15,
        "produtos_vendidos": 5,
        "produtos_disponiveis": 10,
        "preco_medio": 45.50,
        "taxa_venda": 33.33
      }
    ]
  }
}
```

## 🔧 **Funcionalidades Avançadas**

### **Upload de Imagens**

Para enviar imagens junto com produtos, use `multipart/form-data`:

```bash
curl -X POST \
  -H "Authorization: Bearer {token}" \
  -F "referencia=SP-94690" \
  -F "nome=Nome do Produto" \
  -F "descricao=Descrição" \
  -F "preco=29.90" \
  -F "categorias=Garrafas" \
  -F "imagens[]=@imagem1.jpg" \
  -F "imagens[]=@imagem2.jpg" \
  https://api.djob.com.br/wp-json/api/v1/produto
```

### **Filtros de Busca**

A API suporta filtros avançados:

- **Busca por texto**: Pesquisa em título, descrição e referência
- **Filtro por categoria**: Busca produtos de categorias específicas
- **Filtro por preço**: Faixa de preços (mínimo e máximo)
- **Filtro por status**: Produtos disponíveis, vendidos ou todos
- **Ordenação**: Por data, título, preço ou referência
- **Paginação**: Controle de quantidade e navegação entre páginas

### **Validações**

A API inclui validações robustas:

- **Campos obrigatórios**: Verificação de campos essenciais
- **Tipos de dados**: Validação de tipos (string, number, array)
- **Unicidade**: Verificação de referências duplicadas
- **Permissões**: Controle de acesso baseado em propriedade
- **Sanitização**: Limpeza automática de dados de entrada

## 📝 **Códigos de Status HTTP**

- `200` - Sucesso
- `201` - Criado com sucesso
- `400` - Dados inválidos
- `401` - Não autorizado
- `403` - Proibido
- `404` - Não encontrado
- `409` - Conflito (ex: referência duplicada)
- `500` - Erro interno do servidor

## 🚨 **Tratamento de Erros**

Todos os erros retornam no formato:

```json
{
  "code": "codigo_erro",
  "message": "Mensagem descritiva do erro",
  "data": {
    "status": 400
  }
}
```

## 🔒 **Segurança**

- **JWT Authentication**: Tokens com expiração de 24 horas
- **Sanitização**: Limpeza automática de dados
- **Validação**: Verificação de tipos e formatos
- **CORS**: Configuração para aplicações web
- **Permissões**: Controle de acesso baseado em usuário

## 📱 **Exemplos de Uso**

### **Frontend JavaScript**

```javascript
// Criar produto
const criarProduto = async (dados) => {
  const response = await fetch('/wp-json/api/v1/produto', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(dados)
  });
  
  return await response.json();
};

// Buscar produtos com filtros
const buscarProdutos = async (filtros = {}) => {
  const params = new URLSearchParams(filtros);
  const response = await fetch(`/wp-json/api/v1/produtos?${params}`);
  
  return await response.json();
};
```

### **cURL**

```bash
# Listar produtos
curl -X GET "https://api.djob.com.br/wp-json/api/v1/produtos?categoria=Garrafas&preco_min=10"

# Criar produto
curl -X POST \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"referencia":"SP-123","nome":"Produto Teste","descricao":"Descrição","preco":29.90,"categorias":"Teste"}' \
  https://api.djob.com.br/wp-json/api/v1/produto
```

## 🆘 **Suporte**

Para dúvidas ou problemas com a API, consulte:

- **Logs do WordPress**: Verifique erros no painel administrativo
- **Status da API**: Teste endpoints com ferramentas como Postman
- **Documentação**: Este arquivo e comentários no código
- **Desenvolvedor**: Contato direto para suporte técnico

---

**Versão da API:** 1.0  
**Última Atualização:** Janeiro 2024  
**Desenvolvido por:** Sistema de E-commerce WordPress
