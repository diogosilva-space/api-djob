# 🔗 Guia de Integração: Scraping → API WordPress

## 📋 Visão Geral

Este guia explica como integrar uma aplicação de scraping (Node.js) com a API WordPress para postar produtos automaticamente.

## 🚀 Pré-requisitos

- ✅ Aplicação de scraping funcionando
- ✅ Node.js instalado
- ✅ Acesso à API WordPress
- ✅ Credenciais de usuário válidas

## 🔐 Autenticação

### 1. Login para Obter Token JWT

```javascript
const axios = require('axios');

async function fazerLogin(email, senha) {
  try {
    const response = await axios.post('http://localhost:8000/wp-json/api/v1/usuario/login', {
      user_email: email,
      user_pass: senha
    });
    
    if (response.data.status === 'success') {
      return response.data.token; // Token JWT
    }
  } catch (error) {
    console.error('Erro no login:', error.response?.data || error.message);
    throw error;
  }
}
```

### 2. Usar Token nas Requisições

```javascript
const headers = {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json'
};
```

## 📦 Estrutura do Payload para Produtos

### Campos Obrigatórios

```javascript
const produtoPayload = {
  // ✅ OBRIGATÓRIOS
  nome: "Nome do Produto",
  referencia: "REF-001", // Única por produto
  descricao: "Descrição detalhada do produto",
  imagens: [], // Array de URLs das imagens
  cores: [], // Array de objetos de cores
  categorias: [] // Array de strings com categorias
  
  // ❌ OPCIONAIS
  preco: 99.99,
  informacoes_adicionais: "Informações extras"
};
```

## 🎨 Estrutura das Cores (Híbridas)

### Tipo 1: Cor por Código

```javascript
const corCodigo = {
  nome: "Azul Marinho",
  tipo: "codigo",
  codigo: "#000080", // Hex, RGB, etc.
  codigoNumerico: "128" // Opcional
};
```

### Tipo 2: Cor por Imagem

```javascript
const corImagem = {
  nome: "Azul Marinho",
  tipo: "imagem",
  // codigo e codigoNumerico ficam vazios
};
```

## 🖼️ Processamento de Imagens

### 1. Upload de Imagens para WordPress

```javascript
const FormData = require('form-data');
const fs = require('fs');

async function uploadImagem(token, caminhoImagem) {
  const formData = new FormData();
  formData.append('file', fs.createReadStream(caminhoImagem));
  
  try {
    const response = await axios.post(
      'http://localhost:8000/wp-json/wp/v2/media',
      formData,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          ...formData.getHeaders()
        }
      }
    );
    
    return response.data.source_url; // URL da imagem
  } catch (error) {
    console.error('Erro no upload:', error.response?.data || error.message);
    throw error;
  }
}
```

### 2. Upload de Imagens de Cores

```javascript
async function uploadImagemCor(token, caminhoImagemCor) {
  // Mesmo processo do upload de imagem normal
  return await uploadImagem(token, caminhoImagemCor);
}
```

## 🏷️ Categorias

```javascript
const categorias = [
  "Eletrônicos",
  "Smartphones", 
  "Acessórios"
];
```

## 📝 Exemplo Completo de Integração

```javascript
const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');

class IntegracaoAPI {
  constructor(baseURL = 'http://localhost:8000/wp-json/api/v1') {
    this.baseURL = baseURL;
    this.token = null;
  }

  // 1. Autenticação
  async login(email, senha) {
    try {
      const response = await axios.post(`${this.baseURL}/usuario/login`, {
        user_email: email,
        user_pass: senha
      });
      
      if (response.data.status === 'success') {
        this.token = response.data.token;
        console.log('✅ Login realizado com sucesso');
        return true;
      }
    } catch (error) {
      console.error('❌ Erro no login:', error.response?.data || error.message);
      throw error;
    }
  }

  // 2. Upload de imagem
  async uploadImagem(caminhoImagem) {
    if (!this.token) throw new Error('Token não encontrado. Faça login primeiro.');

    const formData = new FormData();
    formData.append('file', fs.createReadStream(caminhoImagem));
    
    try {
      const response = await axios.post(
        'http://localhost:8000/wp-json/wp/v2/media',
        formData,
        {
          headers: {
            'Authorization': `Bearer ${this.token}`,
            ...formData.getHeaders()
          }
        }
      );
      
      return response.data.source_url;
    } catch (error) {
      console.error('❌ Erro no upload da imagem:', error.response?.data || error.message);
      throw error;
    }
  }

  // 3. Criar produto
  async criarProduto(dadosProduto) {
    if (!this.token) throw new Error('Token não encontrado. Faça login primeiro.');

    // Validar campos obrigatórios
    const camposObrigatorios = ['nome', 'referencia', 'descricao', 'imagens', 'cores', 'categorias'];
    for (const campo of camposObrigatorios) {
      if (!dadosProduto[campo] || (Array.isArray(dadosProduto[campo]) && dadosProduto[campo].length === 0)) {
        throw new Error(`Campo obrigatório '${campo}' não fornecido ou vazio`);
      }
    }

    const formData = new FormData();
    
    // Campos de texto
    formData.append('nome', dadosProduto.nome);
    formData.append('referencia', dadosProduto.referencia);
    formData.append('descricao', dadosProduto.descricao);
    
    if (dadosProduto.preco) {
      formData.append('preco', dadosProduto.preco);
    }
    
    if (dadosProduto.informacoes_adicionais) {
      formData.append('informacoes_adicionais', dadosProduto.informacoes_adicionais);
    }

    // Categorias (array)
    dadosProduto.categorias.forEach((categoria, index) => {
      formData.append(`categorias[${index}]`, categoria);
    });

    // Cores (array de objetos)
    dadosProduto.cores.forEach((cor, index) => {
      formData.append(`cores[${index}][nome]`, cor.nome);
      formData.append(`cores[${index}][tipo]`, cor.tipo);
      
      if (cor.tipo === 'codigo') {
        if (cor.codigo) formData.append(`cores[${index}][codigo]`, cor.codigo);
        if (cor.codigoNumerico) formData.append(`cores[${index}][codigoNumerico]`, cor.codigoNumerico);
      } else if (cor.tipo === 'imagem' && cor.imagem) {
        // Upload da imagem da cor
        formData.append(`cores[${index}][imagem]`, fs.createReadStream(cor.imagem));
      }
    });

    // Imagens do produto
    dadosProduto.imagens.forEach((imagem, index) => {
      if (typeof imagem === 'string') {
        // Se for URL, fazer download e upload
        formData.append(`imagens[${index}]`, fs.createReadStream(imagem));
      } else {
        // Se for caminho local
        formData.append(`imagens[${index}]`, fs.createReadStream(imagem));
      }
    });

    try {
      const response = await axios.post(
        `${this.baseURL}/produto`,
        formData,
        {
          headers: {
            'Authorization': `Bearer ${this.token}`,
            ...formData.getHeaders()
          }
        }
      );
      
      console.log('✅ Produto criado com sucesso:', response.data);
      return response.data;
    } catch (error) {
      console.error('❌ Erro ao criar produto:', error.response?.data || error.message);
      throw error;
    }
  }

  // 4. Método principal para processar produto do scraping
  async processarProdutoScraping(dadosScraping) {
    try {
      // 1. Fazer login
      await this.login('seu@email.com', 'sua_senha');
      
      // 2. Processar imagens
      const imagensUrls = [];
      for (const imagem of dadosScraping.imagens) {
        const url = await this.uploadImagem(imagem);
        imagensUrls.push(url);
      }
      
      // 3. Processar cores
      const coresProcessadas = [];
      for (const cor of dadosScraping.cores) {
        if (cor.tipo === 'imagem' && cor.imagem) {
          const urlImagemCor = await this.uploadImagem(cor.imagem);
          coresProcessadas.push({
            nome: cor.nome,
            tipo: 'imagem',
            imagem: urlImagemCor
          });
        } else {
          coresProcessadas.push(cor);
        }
      }
      
      // 4. Criar payload do produto
      const produtoPayload = {
        nome: dadosScraping.nome,
        referencia: dadosScraping.referencia,
        descricao: dadosScraping.descricao,
        preco: dadosScraping.preco,
        imagens: imagensUrls,
        cores: coresProcessadas,
        categorias: dadosScraping.categorias,
        informacoes_adicionais: dadosScraping.informacoes_adicionais
      };
      
      // 5. Criar produto
      const resultado = await this.criarProduto(produtoPayload);
      
      return resultado;
    } catch (error) {
      console.error('❌ Erro no processamento:', error.message);
      throw error;
    }
  }
}

// Exemplo de uso
async function exemploUso() {
  const api = new IntegracaoAPI();
  
  // Dados do scraping (exemplo)
  const dadosScraping = {
    nome: "Smartphone XYZ",
    referencia: "SM-XYZ-001",
    descricao: "Smartphone com excelente qualidade",
    preco: 899.99,
    imagens: [
      "/caminho/para/imagem1.jpg",
      "/caminho/para/imagem2.jpg"
    ],
    cores: [
      {
        nome: "Azul",
        tipo: "codigo",
        codigo: "#0000FF"
      },
      {
        nome: "Vermelho",
        tipo: "imagem",
        imagem: "/caminho/para/cor-vermelha.jpg"
      }
    ],
    categorias: ["Eletrônicos", "Smartphones"],
    informacoes_adicionais: "Produto importado"
  };
  
  try {
    const resultado = await api.processarProdutoScraping(dadosScraping);
    console.log('🎉 Produto criado com sucesso!', resultado);
  } catch (error) {
    console.error('💥 Erro:', error.message);
  }
}

module.exports = IntegracaoAPI;
```

## 🔍 Validações e Tratamento de Erros

### Códigos de Erro Comuns

```javascript
// 400 - Bad Request
if (error.response?.status === 400) {
  const erro = error.response.data;
  if (erro.code === 'campo_obrigatorio') {
    console.error('❌ Campo obrigatório:', erro.message);
  } else if (erro.code === 'imagem_obrigatoria') {
    console.error('❌ Imagem obrigatória:', erro.message);
  } else if (erro.code === 'cores_obrigatorias') {
    console.error('❌ Cores obrigatórias:', erro.message);
  }
}

// 401 - Unauthorized
if (error.response?.status === 401) {
  console.error('❌ Token inválido ou expirado');
  // Refazer login
}

// 409 - Conflict (referência duplicada)
if (error.response?.status === 409) {
  console.error('❌ Referência já existe:', error.response.data.message);
}
```

## 📊 Monitoramento e Logs

```javascript
class Logger {
  static log(acao, dados) {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ${acao}:`, JSON.stringify(dados, null, 2));
  }
  
  static error(erro, contexto) {
    const timestamp = new Date().toISOString();
    console.error(`[${timestamp}] ERRO:`, erro.message, contexto);
  }
}

// Uso
Logger.log('INICIANDO_SCRAPING', { produto: dadosScraping.nome });
Logger.log('UPLOAD_IMAGEM', { url: imagemUrl });
Logger.log('PRODUTO_CRIADO', { id: resultado.id });
```

## 🚀 Dependências NPM

```json
{
  "dependencies": {
    "axios": "^1.6.0",
    "form-data": "^4.0.0"
  }
}
```

## 📋 Checklist de Implementação

- [ ] ✅ Configurar autenticação JWT
- [ ] ✅ Implementar upload de imagens
- [ ] ✅ Processar cores híbridas
- [ ] ✅ Validar campos obrigatórios
- [ ] ✅ Tratar erros de referência duplicada
- [ ] ✅ Implementar logs de monitoramento
- [ ] ✅ Testar com dados reais do scraping
- [ ] ✅ Configurar retry para falhas temporárias

## 🔧 Configurações Recomendadas

### Timeout e Retry

```javascript
const axiosConfig = {
  timeout: 30000, // 30 segundos
  retry: 3,
  retryDelay: 1000 // 1 segundo
};
```

### Rate Limiting

```javascript
// Limitar a 10 produtos por minuto
const rateLimit = 10;
const timeWindow = 60000; // 1 minuto
```

## 📞 Suporte

Para dúvidas ou problemas:
- 📧 Email: suporte@exemplo.com
- 📚 Documentação: http://localhost:8000/api-docs/
- 🔗 Endpoint: http://localhost:8000/wp-json/api/v1/documentacao

---

**🎯 Objetivo:** Integrar perfeitamente o scraping com a API WordPress para automatizar a criação de produtos com todas as funcionalidades avançadas implementadas.
