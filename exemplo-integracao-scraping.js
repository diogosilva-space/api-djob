/**
 * 🔗 Exemplo de Integração: Scraping → API WordPress
 * 
 * Este arquivo demonstra como integrar uma aplicação de scraping
 * com a API WordPress para criar produtos automaticamente.
 */

const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');
const path = require('path');

class IntegracaoAPIWordPress {
  constructor(config = {}) {
    this.baseURL = config.baseURL || 'http://localhost:8000/wp-json/api/v1';
    this.wpMediaURL = config.wpMediaURL || 'http://localhost:8000/wp-json/wp/v2/media';
    this.token = null;
    this.credentials = {
      email: config.email || 'seu@email.com',
      password: config.password || 'sua_senha'
    };
  }

  /**
   * 🔐 Autenticação JWT
   */
  async login() {
    try {
      console.log('🔑 Fazendo login...');
      
      const response = await axios.post(`${this.baseURL}/usuario/login`, {
        user_email: this.credentials.email,
        user_pass: this.credentials.password
      });
      
      if (response.data.status === 'success') {
        this.token = response.data.token;
        console.log('✅ Login realizado com sucesso');
        return true;
      } else {
        throw new Error('Falha na autenticação');
      }
    } catch (error) {
      console.error('❌ Erro no login:', error.response?.data || error.message);
      throw error;
    }
  }

  /**
   * 🖼️ Upload de imagem para WordPress
   */
  async uploadImagem(caminhoImagem, descricao = '') {
    if (!this.token) {
      throw new Error('Token não encontrado. Faça login primeiro.');
    }

    try {
      console.log(`📤 Fazendo upload da imagem: ${path.basename(caminhoImagem)}`);
      
      const formData = new FormData();
      formData.append('file', fs.createReadStream(caminhoImagem));
      if (descricao) {
        formData.append('description', descricao);
      }
      
      const response = await axios.post(this.wpMediaURL, formData, {
        headers: {
          'Authorization': `Bearer ${this.token}`,
          ...formData.getHeaders()
        },
        timeout: 30000 // 30 segundos
      });
      
      console.log('✅ Imagem enviada com sucesso');
      return response.data.source_url;
    } catch (error) {
      console.error('❌ Erro no upload da imagem:', error.response?.data || error.message);
      throw error;
    }
  }

  /**
   * 🎨 Processar cores do produto
   */
  async processarCores(cores) {
    const coresProcessadas = [];
    
    for (const cor of cores) {
      if (cor.tipo === 'imagem' && cor.imagem) {
        // Upload da imagem da cor
        const urlImagemCor = await this.uploadImagem(cor.imagem, `Cor: ${cor.nome}`);
        coresProcessadas.push({
          nome: cor.nome,
          tipo: 'imagem',
          imagem: urlImagemCor
        });
      } else if (cor.tipo === 'codigo') {
        coresProcessadas.push({
          nome: cor.nome,
          tipo: 'codigo',
          codigo: cor.codigo || '',
          codigoNumerico: cor.codigoNumerico || ''
        });
      }
    }
    
    return coresProcessadas;
  }

  /**
   * 📦 Criar produto na API
   */
  async criarProduto(dadosProduto) {
    if (!this.token) {
      throw new Error('Token não encontrado. Faça login primeiro.');
    }

    // Validar campos obrigatórios
    this.validarCamposObrigatorios(dadosProduto);

    try {
      console.log(`📦 Criando produto: ${dadosProduto.nome}`);
      
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
        formData.append(`imagens[${index}]`, fs.createReadStream(imagem));
      });

      const response = await axios.post(
        `${this.baseURL}/produto`,
        formData,
        {
          headers: {
            'Authorization': `Bearer ${this.token}`,
            ...formData.getHeaders()
          },
          timeout: 60000 // 60 segundos para uploads
        }
      );
      
      console.log('✅ Produto criado com sucesso!');
      return response.data;
    } catch (error) {
      this.tratarErroCriacaoProduto(error);
      throw error;
    }
  }

  /**
   * ✅ Validar campos obrigatórios
   */
  validarCamposObrigatorios(dadosProduto) {
    const camposObrigatorios = ['nome', 'referencia', 'descricao', 'imagens', 'cores', 'categorias'];
    
    for (const campo of camposObrigatorios) {
      if (!dadosProduto[campo] || (Array.isArray(dadosProduto[campo]) && dadosProduto[campo].length === 0)) {
        throw new Error(`Campo obrigatório '${campo}' não fornecido ou vazio`);
      }
    }
  }

  /**
   * 🚨 Tratar erros de criação de produto
   */
  tratarErroCriacaoProduto(error) {
    if (error.response?.status === 400) {
      const erro = error.response.data;
      if (erro.code === 'campo_obrigatorio') {
        console.error('❌ Campo obrigatório:', erro.message);
      } else if (erro.code === 'imagem_obrigatoria') {
        console.error('❌ Imagem obrigatória:', erro.message);
      } else if (erro.code === 'cores_obrigatorias') {
        console.error('❌ Cores obrigatórias:', erro.message);
      }
    } else if (error.response?.status === 409) {
      console.error('❌ Referência já existe:', error.response.data.message);
    } else if (error.response?.status === 401) {
      console.error('❌ Token inválido ou expirado');
    } else {
      console.error('❌ Erro ao criar produto:', error.response?.data || error.message);
    }
  }

  /**
   * 🔄 Método principal para processar produto do scraping
   */
  async processarProdutoScraping(dadosScraping) {
    try {
      console.log(`\n🚀 Iniciando processamento do produto: ${dadosScraping.nome}`);
      
      // 1. Fazer login
      await this.login();
      
      // 2. Processar imagens
      console.log('📸 Processando imagens...');
      const imagensUrls = [];
      for (const imagem of dadosScraping.imagens) {
        const url = await this.uploadImagem(imagem, `Imagem do produto: ${dadosScraping.nome}`);
        imagensUrls.push(url);
      }
      
      // 3. Processar cores
      console.log('🎨 Processando cores...');
      const coresProcessadas = await this.processarCores(dadosScraping.cores);
      
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
      
      console.log('🎉 Produto processado com sucesso!');
      return resultado;
    } catch (error) {
      console.error('💥 Erro no processamento:', error.message);
      throw error;
    }
  }

  /**
   * 📊 Processar múltiplos produtos
   */
  async processarMultiplosProdutos(produtos, delay = 2000) {
    const resultados = [];
    
    for (let i = 0; i < produtos.length; i++) {
      try {
        console.log(`\n📦 Processando produto ${i + 1}/${produtos.length}`);
        const resultado = await this.processarProdutoScraping(produtos[i]);
        resultados.push({ sucesso: true, produto: produtos[i], resultado });
        
        // Delay entre produtos para não sobrecarregar a API
        if (i < produtos.length - 1) {
          console.log(`⏳ Aguardando ${delay}ms antes do próximo produto...`);
          await new Promise(resolve => setTimeout(resolve, delay));
        }
      } catch (error) {
        console.error(`❌ Erro no produto ${i + 1}:`, error.message);
        resultados.push({ sucesso: false, produto: produtos[i], erro: error.message });
      }
    }
    
    return resultados;
  }
}

// 📋 Exemplo de uso
async function exemploUso() {
  const api = new IntegracaoAPIWordPress({
    email: 'admin@exemplo.com',
    password: 'senha123',
    baseURL: 'http://localhost:8000/wp-json/api/v1'
  });
  
  // Dados do scraping (exemplo)
  const dadosScraping = {
    nome: "Smartphone XYZ Pro",
    referencia: "SM-XYZ-PRO-001",
    descricao: "Smartphone com excelente qualidade, câmera de 48MP e bateria de longa duração",
    preco: 899.99,
    imagens: [
      "./imagens/smartphone-front.jpg",
      "./imagens/smartphone-back.jpg",
      "./imagens/smartphone-side.jpg"
    ],
    cores: [
      {
        nome: "Azul Marinho",
        tipo: "codigo",
        codigo: "#000080"
      },
      {
        nome: "Vermelho Metálico",
        tipo: "imagem",
        imagem: "./imagens/cor-vermelha.jpg"
      },
      {
        nome: "Preto Brilhante",
        tipo: "codigo",
        codigo: "#000000"
      }
    ],
    categorias: ["Eletrônicos", "Smartphones", "Tecnologia"],
    informacoes_adicionais: "Produto importado com garantia de 1 ano"
  };
  
  try {
    const resultado = await api.processarProdutoScraping(dadosScraping);
    console.log('\n🎉 Produto criado com sucesso!');
    console.log('📋 Detalhes:', {
      id: resultado.id,
      slug: resultado.slug,
      imagens: resultado.imagens_enviadas?.length || 0,
      cores: resultado.cores_processadas?.length || 0,
      categorias: resultado.categorias_processadas?.length || 0
    });
  } catch (error) {
    console.error('💥 Erro:', error.message);
  }
}

// 📊 Exemplo com múltiplos produtos
async function exemploMultiplosProdutos() {
  const api = new IntegracaoAPIWordPress({
    email: 'admin@exemplo.com',
    password: 'senha123'
  });
  
  const produtos = [
    {
      nome: "Produto 1",
      referencia: "PROD-001",
      descricao: "Descrição do produto 1",
      preco: 99.99,
      imagens: ["./imagens/produto1.jpg"],
      cores: [{ nome: "Azul", tipo: "codigo", codigo: "#0000FF" }],
      categorias: ["Categoria 1"]
    },
    {
      nome: "Produto 2", 
      referencia: "PROD-002",
      descricao: "Descrição do produto 2",
      preco: 149.99,
      imagens: ["./imagens/produto2.jpg"],
      cores: [{ nome: "Vermelho", tipo: "codigo", codigo: "#FF0000" }],
      categorias: ["Categoria 2"]
    }
  ];
  
  try {
    const resultados = await api.processarMultiplosProdutos(produtos, 3000);
    
    const sucessos = resultados.filter(r => r.sucesso).length;
    const falhas = resultados.filter(r => !r.sucesso).length;
    
    console.log(`\n📊 Resumo do processamento:`);
    console.log(`✅ Sucessos: ${sucessos}`);
    console.log(`❌ Falhas: ${falhas}`);
  } catch (error) {
    console.error('💥 Erro:', error.message);
  }
}

// Exportar classe para uso em outros arquivos
module.exports = IntegracaoAPIWordPress;

// Executar exemplo se o arquivo for executado diretamente
if (require.main === module) {
  console.log('🚀 Executando exemplo de integração...');
  exemploUso().catch(console.error);
}
