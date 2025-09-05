# 🎨 Documentação - Sistema de Cores de Produtos

## 📋 Visão Geral

O sistema de cores da API DJob suporta **cores híbridas**, permitindo que cada produto tenha cores definidas tanto por **imagens** quanto por **códigos de cor**. As cores são sempre parte integrante dos produtos e não existem endpoints separados para gerenciá-las.

---

## 🔗 **Endpoints Disponíveis**

### **1. Criar Produto com Cores**
- **URL:** `POST /wp-json/api/v1/produto`
- **Autenticação:** JWT Bearer Token obrigatório
- **Content-Type:** `multipart/form-data`
- **✅ VALIDAÇÃO CORRIGIDA:** Parâmetro `cores` aceita tanto string JSON quanto array

### **2. Atualizar Produto com Cores**
- **URL:** `PUT /wp-json/api/v1/produto/{id}`
- **Autenticação:** JWT Bearer Token obrigatório
- **Content-Type:** `multipart/form-data` (para upload de imagens) ou `application/json` (apenas dados)
- **✅ CORRIGIDO:** Agora suporta upload de imagens das cores via multipart/form-data
- **✅ VALIDAÇÃO CORRIGIDA:** Parâmetro `cores` aceita tanto string JSON quanto array

### **3. Buscar Produtos por Cor**
- **URL:** `GET /wp-json/api/v1/produtos?cores={cor}`
- **Autenticação:** Não necessária

---

## 🎨 **Estrutura das Cores**

### **Formato da Cor**
```json
{
  "nome": "Azul Marinho",
  "tipo": "imagem", // ou "codigo"
  "imagem": "https://exemplo.com/cor-azul.jpg", // apenas se tipo = "imagem"
  "codigo": "#1e3a8a", // apenas se tipo = "codigo"
  "codigoNumerico": "123456" // opcional, apenas se tipo = "codigo"
}
```

### **Campos Obrigatórios**
- `nome` (string) - Nome da cor
- `tipo` (string) - Tipo da cor: `"imagem"` ou `"codigo"`

### **Campos Condicionais**

**Para `tipo: "imagem"`:**
- `imagem` (string) - URL da imagem da cor (gerada automaticamente no POST)

**Para `tipo: "codigo"`:**
- `codigo` (string) - Código hexadecimal da cor (ex: "#1e3a8a")
- `codigoNumerico` (string, opcional) - Código numérico da cor

---

## 🚀 **Criar Produto com Cores**

### **Request (POST)**
```javascript
// Content-Type: multipart/form-data
const formData = new FormData();

// Dados básicos
formData.append('nome', 'Garrafa Térmica');
formData.append('referencia', 'GAR001');
formData.append('descricao', 'Garrafa térmica de alta qualidade');
formData.append('preco', '29.90');

// Categorias (array)
formData.append('categorias[]', 'Garrafas');
formData.append('categorias[]', 'Térmicas');

// Cores (array de objetos JSON)
const cores = [
  {
    nome: "Azul Marinho",
    tipo: "imagem"
  },
  {
    nome: "Vermelho",
    tipo: "codigo",
    codigo: "#dc2626",
    codigoNumerico: "123456"
  }
];

formData.append('cores', JSON.stringify(cores));

// Imagens das cores (arquivos)
formData.append('cores_imagem_0', arquivoImagemAzul); // Para cores tipo "imagem"

// Imagens do produto
formData.append('imagens', arquivoImagem1);
formData.append('imagens', arquivoImagem2);
```

### **Response (Sucesso)**
```json
{
  "id": 123,
  "slug": "garrafa-termica",
  "status": "success",
  "message": "Produto criado com sucesso",
  "imagens_enviadas": ["https://exemplo.com/img1.jpg"],
  "imagens_ids": [456, 457],
  "usuario_id": 789,
  "usuario_login": "usuario@exemplo.com",
  "cores_processadas": [
    {
      "nome": "Azul Marinho",
      "tipo": "imagem",
      "imagem": "https://exemplo.com/cor-azul.jpg",
      "codigo": "",
      "codigoNumerico": ""
    },
    {
      "nome": "Vermelho",
      "tipo": "codigo",
      "imagem": "",
      "codigo": "#dc2626",
      "codigoNumerico": "123456"
    }
  ],
  "categorias_processadas": ["Garrafas", "Térmicas"]
}
```

---

## ✏️ **Atualizar Produto com Cores**

### **✅ CORRIGIDO: Endpoint PUT Atualizado**

O endpoint de atualização (PUT) agora suporta **ambos os formatos**:
- **JSON (application/json)**: Para atualizar dados existentes (URLs de imagens já salvas)
- **Multipart (multipart/form-data)**: Para upload de novas imagens das cores e do produto

### **Opção 1: Atualizar Apenas Dados (JSON)**
```javascript
// Content-Type: application/json
// Use quando NÃO precisar fazer upload de novas imagens
const coresAtualizadas = [
  {
    nome: "Azul Marinho",
    tipo: "imagem",
    imagem: "https://exemplo.com/cor-azul-existente.jpg" // URL já existente
  },
  {
    nome: "Verde",
    tipo: "codigo",
    codigo: "#16a34a",
    codigoNumerico: "789012"
  }
];

const response = await fetch('/wp-json/api/v1/produto/123', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + token
  },
  body: JSON.stringify({
    cores: coresAtualizadas
  })
});
```

### **Opção 2: Upload de Novas Imagens (Multipart)**
```javascript
// Content-Type: multipart/form-data
// Use quando precisar fazer upload de novas imagens de cores
const formData = new FormData();

// Cores com novas imagens
const cores = [
  {
    nome: "Azul Marinho",
    tipo: "imagem"
  },
  {
    nome: "Verde",
    tipo: "codigo",
    codigo: "#16a34a",
    codigoNumerico: "789012"
  }
];

formData.append('cores', JSON.stringify(cores));
formData.append('cores_imagem_0', arquivoNovaImagemAzul); // Nova imagem

const response = await fetch('/wp-json/api/v1/produto/123', {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer ' + token
    // NÃO definir Content-Type - deixar o browser definir automaticamente
  },
  body: formData
});
```

### **Response (Sucesso)**
```json
{
  "id": 123,
  "nome": "Garrafa Térmica",
  "referencia": "GAR001",
  "descricao": "Garrafa térmica de alta qualidade",
  "cores": [
    {
      "nome": "Azul Marinho",
      "tipo": "imagem",
      "imagem": "https://exemplo.com/cor-azul-atualizada.jpg",
      "codigo": "",
      "codigoNumerico": ""
    },
    {
      "nome": "Verde",
      "tipo": "codigo",
      "imagem": "",
      "codigo": "#16a34a",
      "codigoNumerico": "789012"
    },
    {
      "nome": "Preto",
      "tipo": "codigo",
      "imagem": "",
      "codigo": "#000000",
      "codigoNumerico": ""
    }
  ],
  "status": "success",
  "message": "Produto atualizado com sucesso"
}
```

---

## 🔍 **Buscar Produtos por Cor**

### **Request (GET)**
```javascript
// Buscar produtos com cor específica
GET /wp-json/api/v1/produtos?cores=azul

// Buscar com múltiplos filtros
GET /wp-json/api/v1/produtos?cores=vermelho&categoria=Garrafas&preco_min=20
```

### **Response**
```json
{
  "produtos": [
    {
      "id": 123,
      "nome": "Garrafa Térmica",
      "cores": [
        {
          "nome": "Azul Marinho",
          "tipo": "imagem",
          "imagem": "https://exemplo.com/cor-azul.jpg",
          "codigo": "",
          "codigoNumerico": ""
        }
      ],
      "categorias": ["Garrafas"],
      "preco": 29.90
    }
  ],
  "paginacao": {
    "pagina_atual": 1,
    "total_paginas": 1,
    "total_produtos": 1
  },
  "filtros_aplicados": {
    "cores": "azul"
  }
}
```

---

## 📱 **Exemplos de Implementação Frontend**

### **React/JavaScript - Criar Produto**
```javascript
const criarProdutoComCores = async (dadosProduto) => {
  const formData = new FormData();
  
  // Dados básicos
  formData.append('nome', dadosProduto.nome);
  formData.append('referencia', dadosProduto.referencia);
  formData.append('descricao', dadosProduto.descricao);
  formData.append('preco', dadosProduto.preco);
  
  // Categorias
  dadosProduto.categorias.forEach(cat => {
    formData.append('categorias[]', cat);
  });
  
  // Cores
  const cores = dadosProduto.cores.map((cor, index) => {
    const corData = {
      nome: cor.nome,
      tipo: cor.tipo
    };
    
    if (cor.tipo === 'codigo') {
      corData.codigo = cor.codigo;
      corData.codigoNumerico = cor.codigoNumerico || '';
    }
    
    return corData;
  });
  
  formData.append('cores', JSON.stringify(cores));
  
  // Imagens das cores (tipo imagem)
  dadosProduto.cores.forEach((cor, index) => {
    if (cor.tipo === 'imagem' && cor.arquivo) {
      formData.append(`cores_imagem_${index}`, cor.arquivo);
    }
  });
  
  // Imagens do produto
  dadosProduto.imagens.forEach(img => {
    formData.append('imagens', img);
  });
  
  const response = await fetch('/wp-json/api/v1/produto', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer ' + token
    },
    body: formData
  });
  
  return await response.json();
};
```

### **Vue.js - Atualizar Cores (Com Upload de Imagens)**
```javascript
const atualizarCoresProduto = async (produtoId, novasCores, arquivosImagens = []) => {
  // Se há arquivos para upload, usar multipart/form-data
  if (arquivosImagens.length > 0) {
    const formData = new FormData();
    
    // Cores
    const coresFormatadas = novasCores.map(cor => ({
      nome: cor.nome,
      tipo: cor.tipo,
      codigo: cor.codigo || '',
      codigoNumerico: cor.codigoNumerico || ''
    }));
    
    formData.append('cores', JSON.stringify(coresFormatadas));
    
    // Arquivos de imagens das cores
    arquivosImagens.forEach((arquivo, index) => {
      if (arquivo && novasCores[index]?.tipo === 'imagem') {
        formData.append(`cores_imagem_${index}`, arquivo);
      }
    });
    
    const response = await fetch(`/wp-json/api/v1/produto/${produtoId}`, {
      method: 'PUT',
      headers: {
        'Authorization': 'Bearer ' + token
      },
      body: formData
    });
    
    return await response.json();
  } else {
    // Apenas dados, usar JSON
    const coresFormatadas = novasCores.map(cor => ({
      nome: cor.nome,
      tipo: cor.tipo,
      imagem: cor.imagem || '',
      codigo: cor.codigo || '',
      codigoNumerico: cor.codigoNumerico || ''
    }));
    
    const response = await fetch(`/wp-json/api/v1/produto/${produtoId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
      },
      body: JSON.stringify({
        cores: coresFormatadas
      })
    });
    
    return await response.json();
  }
};
```

### **Angular - Buscar por Cor**
```typescript
interface Cor {
  nome: string;
  tipo: 'imagem' | 'codigo';
  imagem?: string;
  codigo?: string;
  codigoNumerico?: string;
}

@Injectable()
export class ProdutoService {
  buscarPorCor(cor: string, filtros?: any): Observable<any> {
    const params = new HttpParams()
      .set('cores', cor)
      .set('page', filtros?.page || '1')
      .set('per_page', filtros?.perPage || '10');
    
    return this.http.get('/wp-json/api/v1/produtos', { params });
  }
}
```

---

## ⚠️ **Validações e Regras**

### **1. Validações Obrigatórias**
- **Pelo menos uma cor** deve ser enviada
- **Nome da cor** é obrigatório
- **Tipo da cor** deve ser `"imagem"` ou `"codigo"`
- **Código** é obrigatório se `tipo = "codigo"`

### **2. Validações de Arquivo (POST)**
- **Imagens de cor** devem ser JPG, PNG, GIF ou WebP
- **Tamanho máximo** por arquivo: 10MB
- **Chave do arquivo** deve seguir padrão: `cores_imagem_{index}`

### **3. Validações de URL (PUT)**
- **URLs de imagem** devem ser válidas
- **Códigos de cor** devem ser hexadecimais válidos

---

## 🎯 **Casos de Uso Comuns**

### **1. Produto com Cores por Imagem**
```javascript
const cores = [
  {
    nome: "Azul Marinho",
    tipo: "imagem"
    // arquivo será enviado via FormData
  }
];
```

### **2. Produto com Cores por Código**
```javascript
const cores = [
  {
    nome: "Vermelho",
    tipo: "codigo",
    codigo: "#dc2626",
    codigoNumerico: "123456"
  }
];
```

### **3. Produto com Cores Mistas**
```javascript
const cores = [
  {
    nome: "Azul",
    tipo: "imagem"
  },
  {
    nome: "Vermelho",
    tipo: "codigo",
    codigo: "#dc2626"
  }
];
```

### **4. Atualizar Apenas Cores**
```javascript
// Manter outras propriedades, atualizar apenas cores
const response = await fetch('/wp-json/api/v1/produto/123', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + token
  },
  body: JSON.stringify({
    cores: novasCores
  })
});
```

---

## 🚨 **Observações Importantes**

### **1. Upload de Imagens (POST)**
- Use `multipart/form-data` para envio de arquivos
- Chaves dos arquivos: `cores_imagem_0`, `cores_imagem_1`, etc.
- URLs são geradas automaticamente pelo servidor

### **2. Atualização (PUT) - ✅ CORRIGIDO**
- **JSON (application/json)**: Para atualizar dados existentes
- **Multipart (multipart/form-data)**: Para upload de novas imagens das cores e do produto
- **✅ Funcionalidade**: Agora suporta upload de imagens das cores via multipart/form-data
- **✅ Compatibilidade**: Mantém compatibilidade com JSON para atualizações simples

### **3. Busca por Cor**
- Busca é case-sensitive
- Busca por nome da cor (não por código)
- Use filtros combinados para melhor precisão

### **4. Performance**
- Cores são armazenadas como JSON no banco
- Use paginação para listas grandes
- Cache cores no frontend (mudam pouco)

## 🐛 **Correções de Bugs**

### **✅ Erro 400 - Parâmetro 'cores' Inválido (POST e PUT)**
**Problema:** Erro `rest_invalid_param` ao enviar cores via `multipart/form-data`

**Causa:** O WordPress REST API esperava array, mas recebia string JSON

**Endpoints Afetados:** 
- ✅ **POST** `/produto` - Corrigido
- ✅ **PUT** `/produto/{id}` - Corrigido

**Solução:** 
- Validação atualizada para aceitar tanto `string` quanto `array`
- Decodificação automática de JSON quando necessário
- Sanitização customizada para ambos os formatos
- Validação inteligente que funciona com ambos os tipos

**Código corrigido:**
```php
// Validação flexível
'cores' => array(
  'required' => false,
  'type' => array('string', 'array'),
  'sanitize_callback' => function($param, $request, $key) {
    if (is_string($param)) return $param;
    if (is_array($param)) return $param;
    return null;
  }
)

// Processamento inteligente
if (is_string($cores_param)) {
  $cores_param = json_decode($cores_param, true);
}
```

---

## 🔧 **Funcionalidades Implementadas**

### **✅ Upload de Imagens das Cores (PUT)**
```javascript
// Agora o endpoint PUT suporta upload de imagens das cores
const formData = new FormData();

// Cores com novas imagens
const cores = [
  {
    nome: "Azul Marinho",
    tipo: "imagem"
  },
  {
    nome: "Vermelho",
    tipo: "codigo",
    codigo: "#dc2626"
  }
];

formData.append('cores', JSON.stringify(cores));
formData.append('cores_imagem_0', arquivoNovaImagemAzul); // Nova imagem

const response = await fetch(`/wp-json/api/v1/produto/${produtoId}`, {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer ' + token
    // NÃO definir Content-Type - deixar o browser definir automaticamente
  },
  body: formData
});
```

### **✅ Upload de Imagens do Produto (PUT)**
```javascript
// Também suporta upload de novas imagens do produto
formData.append('imagens', arquivoImagem1);
formData.append('imagens', arquivoImagem2);
```

### **✅ Resposta Atualizada**
```json
{
  "status": "success",
  "message": "Produto atualizado com sucesso",
  "produto": { /* dados do produto */ },
  "cores_processadas": [ /* cores processadas */ ],
  "imagens_enviadas": [ /* URLs das novas imagens */ ]
}
```

---

## 🔧 **Dicas de Implementação**

### **1. Formulário de Cores**
- Valide tipo antes de mostrar campos específicos
- Preview de imagens antes do upload
- Validação de códigos hexadecimais

### **2. Exibição de Cores**
- Mostre imagem ou código baseado no tipo
- Fallback para código se imagem não carregar
- Use códigos de cor para backgrounds

### **3. Filtros**
- Implemente busca por nome da cor
- Combine com outros filtros (categoria, preço)
- Use debounce na busca

---

*Documentação gerada automaticamente - Sistema de Cores API v1.0*
