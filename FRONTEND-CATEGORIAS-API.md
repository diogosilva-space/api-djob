# 📋 Documentação Frontend - Sistema de Categorias

## 🎯 Visão Geral

O sistema de categorias permite gerenciar e filtrar produtos por categorias de forma dinâmica. As categorias são criadas automaticamente quando produtos são cadastrados com novas categorias.

---

## 🔗 Endpoints Disponíveis

### 1. **Listar Todas as Categorias**
- **URL:** `GET /wp-json/api/v1/categorias`
- **Autenticação:** Não necessária
- **Descrição:** Retorna todas as categorias disponíveis no sistema

#### Parâmetros de Query:
- `incluir_contadores` (boolean, padrão: false) - Inclui contador de produtos por categoria
- `incluir_preco_medio` (boolean, padrão: false) - Inclui preço médio por categoria
- `ordenar_por` (string, padrão: "nome") - Campo para ordenação: `nome`, `total_produtos`, `preco_medio`
- `ordenar` (string, padrão: "ASC") - Direção da ordenação: `ASC`, `DESC`

#### Resposta:
```json
{
  "categorias": [
    {
      "categoria": "Garrafas",
      "total_produtos": 15,
      "preco_medio": 29.90
    }
  ],
  "total": 1,
  "parametros": {
    "incluir_contadores": true,
    "incluir_preco_medio": true,
    "ordenar_por": "total_produtos",
    "ordenar": "DESC"
  }
}
```

### 2. **Buscar Produtos por Categoria**
- **URL:** `GET /wp-json/api/v1/produtos`
- **Autenticação:** Não necessária
- **Descrição:** Lista produtos com filtros, incluindo filtro por categoria

#### Parâmetros de Query:
- `categoria` (string) - Filtrar por categoria específica
- `page` (integer, padrão: 1) - Número da página
- `per_page` (integer, padrão: 10) - Itens por página
- `search` (string) - Termo de busca
- `preco_min` (number) - Preço mínimo
- `preco_max` (number) - Preço máximo
- `ordenar_por` (string) - Campo para ordenação
- `ordenar` (string) - Direção da ordenação

#### Resposta:
```json
{
  "produtos": [
    {
      "id": 123,
      "nome": "Produto Exemplo",
      "categorias": ["Garrafas"],
      "preco": 29.90,
      "imagens": ["url_da_imagem.jpg"]
    }
  ],
  "paginacao": {
    "pagina_atual": 1,
    "total_paginas": 2,
    "total_produtos": 15
  },
  "filtros_aplicados": {
    "categoria": "Garrafas"
  }
}
```

### 3. **Criar Produto com Categorias**
- **URL:** `POST /wp-json/api/v1/produto`
- **Autenticação:** JWT Bearer Token obrigatório
- **Content-Type:** `multipart/form-data`
- **Descrição:** Cria produto com categorias (categorias são criadas automaticamente se não existirem)

#### Body (multipart/form-data):
- `nome` (string, obrigatório) - Nome do produto
- `referencia` (string, obrigatório) - Referência única
- `descricao` (string, obrigatório) - Descrição do produto
- `categorias` (array, obrigatório) - Array de categorias
- `preco` (number, opcional) - Preço do produto
- `imagens` (file, obrigatório) - Arquivos de imagem
- `cores` (array, opcional) - Array de cores

#### Resposta:
```json
{
  "id": 124,
  "slug": "produto-exemplo",
  "status": "success",
  "message": "Produto criado com sucesso",
  "categorias_processadas": ["Garrafas", "Nova Categoria"],
  "usuario_id": 456
}
```

---

## 🚀 Casos de Uso Comuns

### 1. **Carregar Lista de Categorias para Filtro**
```javascript
// Buscar categorias com contadores para exibir em filtro
GET /wp-json/api/v1/categorias?incluir_contadores=true&ordenar_por=total_produtos&ordenar=DESC
```

### 2. **Exibir Produtos de uma Categoria**
```javascript
// Buscar produtos da categoria "Garrafas" com paginação
GET /wp-json/api/v1/produtos?categoria=Garrafas&page=1&per_page=20
```

### 3. **Busca com Múltiplos Filtros**
```javascript
// Buscar produtos da categoria "Garrafas" com preço entre 10 e 50
GET /wp-json/api/v1/produtos?categoria=Garrafas&preco_min=10&preco_max=50&search=termica
```

### 4. **Criar Produto com Categorias Existentes e Novas**
```javascript
// Categorias são criadas automaticamente se não existirem
POST /wp-json/api/v1/produto
// Body: { categorias: ["Garrafas", "Nova Categoria", "Térmicas"] }
```

---

## 📊 Funcionalidades Avançadas

### **Estatísticas de Categorias**
- **URL:** `GET /wp-json/api/v1/estatisticas?tipo=categorias`
- **Autenticação:** JWT Bearer Token obrigatório
- **Descrição:** Retorna estatísticas detalhadas das categorias

### **Busca Inteligente**
- O parâmetro `search` busca em nome, descrição e categorias
- Use `buscar_descricao=true` para incluir descrição na busca
- Filtros podem ser combinados (categoria + preço + busca)

---

## ⚠️ Observações Importantes

### **Criação de Categorias**
- **NÃO existe endpoint para criar categorias separadamente**
- Categorias são criadas automaticamente ao cadastrar produtos
- Use o endpoint de criação de produtos com as categorias desejadas

### **Validação de Categorias**
- Categorias são obrigatórias no cadastro de produtos
- Pelo menos uma categoria deve ser enviada
- Categorias são armazenadas como array de strings

### **Performance**
- Use paginação para listas grandes de produtos
- O endpoint de categorias é otimizado para consultas rápidas
- Contadores são opcionais para melhor performance

### **Ordenação**
- Categorias podem ser ordenadas por nome, total de produtos ou preço médio
- Produtos podem ser ordenados por data, nome, preço ou referência
- Use `ordenar=ASC` ou `ordenar=DESC` para direção

---

## 🔧 Implementação Sugerida

### **1. Componente de Filtro de Categorias**
- Carregue categorias com `incluir_contadores=true`
- Exiba contador de produtos ao lado de cada categoria
- Implemente busca/filtro em tempo real

### **2. Página de Produtos por Categoria**
- Use `categoria` como parâmetro de rota
- Implemente paginação para grandes volumes
- Adicione filtros adicionais (preço, busca)

### **3. Formulário de Cadastro de Produto**
- Campo de categorias como array de strings
- Validação de pelo menos uma categoria
- Sugestão de categorias existentes

### **4. Dashboard de Estatísticas**
- Use endpoint de estatísticas para gráficos
- Exiba categorias mais populares
- Mostre preços médios por categoria

---

## 📱 Exemplos de URLs para Teste

```
# Listar todas as categorias
GET /wp-json/api/v1/categorias

# Categorias com contadores
GET /wp-json/api/v1/categorias?incluir_contadores=true

# Produtos da categoria "Garrafas"
GET /wp-json/api/v1/produtos?categoria=Garrafas

# Busca com filtros
GET /wp-json/api/v1/produtos?categoria=Garrafas&preco_min=20&preco_max=100

# Estatísticas de categorias
GET /wp-json/api/v1/estatisticas?tipo=categorias
```

---

## 🎯 Próximos Passos

1. **Implementar componente de filtro de categorias**
2. **Criar página de produtos por categoria**
3. **Adicionar validação de categorias no formulário**
4. **Implementar busca inteligente com categorias**
5. **Criar dashboard de estatísticas**

---

*Documentação gerada automaticamente - Sistema de Categorias API v1.0*
