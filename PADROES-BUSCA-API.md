# 🔍 Padrões de Busca - API DJob

## 📋 Visão Geral

Este documento descreve todos os padrões de busca e filtros disponíveis na API para a equipe de frontend implementar funcionalidades de pesquisa e filtragem.

---

## 🛍️ **Endpoint: Produtos** 
**URL:** `GET /wp-json/api/v1/produtos`

### **Parâmetros de Busca e Filtros**

| Parâmetro | Tipo | Padrão | Descrição | Exemplo |
|-----------|------|--------|-----------|---------|
| `page` | integer | 1 | Número da página | `?page=2` |
| `per_page` | integer | 10 | Itens por página (máx: 100) | `?per_page=20` |
| `search` | string | - | Busca por palavras-chave no nome/slug | `?search=garrafa` |
| `categoria` | string | - | Filtrar por categoria específica | `?categoria=Garrafas` |
| `cores` | string | - | Filtrar por cor específica | `?cores=azul` |
| `preco_min` | number | - | Preço mínimo | `?preco_min=10.50` |
| `preco_max` | number | - | Preço máximo | `?preco_max=100.00` |
| `referencia` | string | - | Busca por referência específica | `?referencia=GAR001` |
| `buscar_descricao` | boolean | false | Incluir descrição na busca | `?buscar_descricao=true` |
| `ordenar_por` | string | "date" | Campo para ordenação | `?ordenar_por=preco` |
| `ordenar` | string | "DESC" | Direção da ordenação | `?ordenar=ASC` |

### **Valores Válidos para Ordenação**

**`ordenar_por`:**
- `date` - Data de criação
- `title` - Nome do produto
- `preco` - Preço
- `referencia` - Referência

**`ordenar`:**
- `ASC` - Crescente
- `DESC` - Decrescente

### **Exemplos de URLs de Busca**

```javascript
// Busca básica
GET /wp-json/api/v1/produtos?search=garrafa

// Filtro por categoria e preço
GET /wp-json/api/v1/produtos?categoria=Garrafas&preco_min=20&preco_max=50

// Busca com paginação
GET /wp-json/api/v1/produtos?page=2&per_page=20

// Ordenação por preço
GET /wp-json/api/v1/produtos?ordenar_por=preco&ordenar=ASC

// Busca avançada com múltiplos filtros
GET /wp-json/api/v1/produtos?search=termica&categoria=Garrafas&cores=azul&preco_min=15&buscar_descricao=true

// Busca por referência específica
GET /wp-json/api/v1/produtos?referencia=GAR001
```

---

## 🏷️ **Endpoint: Categorias**
**URL:** `GET /wp-json/api/v1/categorias`

### **Parâmetros de Busca e Filtros**

| Parâmetro | Tipo | Padrão | Descrição | Exemplo |
|-----------|------|--------|-----------|---------|
| `incluir_contadores` | boolean | false | Incluir contador de produtos por categoria | `?incluir_contadores=true` |
| `incluir_preco_medio` | boolean | false | Incluir preço médio por categoria | `?incluir_preco_medio=true` |
| `ordenar_por` | string | "nome" | Campo para ordenação | `?ordenar_por=total_produtos` |
| `ordenar` | string | "ASC" | Direção da ordenação | `?ordenar=DESC` |

### **Valores Válidos para Ordenação**

**`ordenar_por`:**
- `nome` - Nome da categoria
- `total_produtos` - Quantidade de produtos (requer `incluir_contadores=true`)
- `preco_medio` - Preço médio (requer `incluir_preco_medio=true`)

**`ordenar`:**
- `ASC` - Crescente
- `DESC` - Decrescente

### **Exemplos de URLs de Busca**

```javascript
// Listar todas as categorias
GET /wp-json/api/v1/categorias

// Categorias com contadores de produtos
GET /wp-json/api/v1/categorias?incluir_contadores=true

// Categorias ordenadas por quantidade de produtos
GET /wp-json/api/v1/categorias?incluir_contadores=true&ordenar_por=total_produtos&ordenar=DESC

// Categorias com preço médio
GET /wp-json/api/v1/categorias?incluir_preco_medio=true&ordenar_por=preco_medio&ordenar=DESC
```

---

## 📊 **Endpoint: Estatísticas**
**URL:** `GET /wp-json/api/v1/estatisticas` *(Requer Autenticação JWT)*

### **Parâmetros de Busca e Filtros**

| Parâmetro | Tipo | Padrão | Descrição | Exemplo |
|-----------|------|--------|-----------|---------|
| `tipo` | string | "geral" | Tipo de estatística | `?tipo=produtos` |
| `periodo` | string | "30dias" | Período dos dados | `?periodo=7dias` |

### **Valores Válidos**

**`tipo`:**
- `geral` - Todas as estatísticas
- `produtos` - Estatísticas de produtos
- `vendas` - Estatísticas de vendas
- `categorias` - Estatísticas de categorias

**`periodo`:**
- `7dias` - Últimos 7 dias
- `30dias` - Últimos 30 dias
- `90dias` - Últimos 90 dias
- `1ano` - Último ano

### **Exemplos de URLs de Busca**

```javascript
// Estatísticas gerais
GET /wp-json/api/v1/estatisticas

// Estatísticas de produtos dos últimos 7 dias
GET /wp-json/api/v1/estatisticas?tipo=produtos&periodo=7dias

// Estatísticas de categorias
GET /wp-json/api/v1/estatisticas?tipo=categorias
```

---

## 🔍 **Padrões de Busca Inteligente**

### **1. Busca por Texto (`search`)**
- Busca no **nome** e **slug** do produto
- Case-insensitive (não diferencia maiúsculas/minúsculas)
- Suporte a palavras parciais
- **Exemplo:** `search=garrafa` encontra "Garrafa Térmica", "Garrafinha", etc.

### **2. Busca com Descrição (`buscar_descricao=true`)**
- Inclui o campo **descrição** na busca
- Usado em conjunto com `search`
- **Exemplo:** `search=termica&buscar_descricao=true`

### **3. Filtro por Categoria (`categoria`)**
- Busca exata por categoria
- Case-sensitive (diferencia maiúsculas/minúsculas)
- **Exemplo:** `categoria=Garrafas` (não encontra "garrafas")

### **4. Filtro por Preço (`preco_min` + `preco_max`)**
- Filtro por faixa de preço
- Valores numéricos decimais
- **Exemplo:** `preco_min=10.50&preco_max=99.99`

### **5. Filtro por Cor (`cores`)**
- Busca por cor específica
- Case-sensitive
- **Exemplo:** `cores=azul`

### **6. Busca por Referência (`referencia`)**
- Busca exata por referência do produto
- Case-sensitive
- **Exemplo:** `referencia=GAR001`

---

## 🎯 **Casos de Uso Comuns**

### **1. Barra de Pesquisa Principal**
```javascript
// Busca simples
GET /wp-json/api/v1/produtos?search={termo}

// Busca com descrição
GET /wp-json/api/v1/produtos?search={termo}&buscar_descricao=true
```

### **2. Filtros de Categoria**
```javascript
// Carregar categorias para filtro
GET /wp-json/api/v1/categorias?incluir_contadores=true&ordenar_por=total_produtos&ordenar=DESC

// Filtrar produtos por categoria
GET /wp-json/api/v1/produtos?categoria={categoria_selecionada}
```

### **3. Filtros de Preço**
```javascript
// Slider de preço
GET /wp-json/api/v1/produtos?preco_min={min}&preco_max={max}
```

### **4. Busca Avançada**
```javascript
// Múltiplos filtros combinados
GET /wp-json/api/v1/produtos?search={termo}&categoria={cat}&preco_min={min}&preco_max={max}&cores={cor}
```

### **5. Paginação**
```javascript
// Navegação por páginas
GET /wp-json/api/v1/produtos?page={numero}&per_page={itens}
```

---

## ⚡ **Dicas de Performance**

### **1. Paginação**
- Use `per_page` máximo de 50-100 itens
- Implemente paginação no frontend
- Carregue dados sob demanda

### **2. Filtros**
- Aplique filtros no servidor, não no frontend
- Use debounce na busca por texto
- Cache categorias (mudam pouco)

### **3. Ordenação**
- Use ordenação do servidor
- Evite ordenação no frontend para grandes listas

---

## 🚨 **Observações Importantes**

### **1. Autenticação**
- Endpoint de estatísticas requer JWT Bearer Token
- Outros endpoints são públicos

### **2. Validação**
- Todos os parâmetros são sanitizados automaticamente
- Valores inválidos são ignorados ou usam padrões

### **3. Resposta**
- Sempre retorna JSON
- Inclui metadados de paginação e filtros aplicados
- Categorias agora retornam como **arrays** (não strings JSON)

### **4. Limites**
- `per_page` máximo: 100 itens
- Timeout de busca: 30 segundos
- Rate limiting: 100 requests/minuto

---

## 📱 **Exemplos de Implementação Frontend**

### **React/JavaScript**
```javascript
// Hook para busca de produtos
const useProdutos = (filtros) => {
  const [produtos, setProdutos] = useState([]);
  const [loading, setLoading] = useState(false);
  
  useEffect(() => {
    const buscarProdutos = async () => {
      setLoading(true);
      const params = new URLSearchParams(filtros);
      const response = await fetch(`/wp-json/api/v1/produtos?${params}`);
      const data = await response.json();
      setProdutos(data.produtos);
      setLoading(false);
    };
    
    buscarProdutos();
  }, [filtros]);
  
  return { produtos, loading };
};

// Uso
const { produtos, loading } = useProdutos({
  search: 'garrafa',
  categoria: 'Garrafas',
  preco_min: 20,
  page: 1,
  per_page: 20
});
```

### **Vue.js**
```javascript
// Composable para busca
export const useProdutos = () => {
  const produtos = ref([]);
  const loading = ref(false);
  
  const buscarProdutos = async (filtros = {}) => {
    loading.value = true;
    try {
      const params = new URLSearchParams(filtros);
      const response = await fetch(`/wp-json/api/v1/produtos?${params}`);
      const data = await response.json();
      produtos.value = data.produtos;
    } finally {
      loading.value = false;
    }
  };
  
  return { produtos, loading, buscarProdutos };
};
```

---

*Documentação gerada automaticamente - Padrões de Busca API v1.0*
