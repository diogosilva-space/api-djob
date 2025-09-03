/**
 * 🧪 Teste de Integração: Scraping → WordPress API
 * 
 * Este arquivo testa a integração entre o scraping e a API WordPress
 */

const IntegracaoAPIWordPress = require('./exemplo-integracao-scraping');
const config = require('./config');

class TesteIntegracao {
  constructor() {
    this.api = new IntegracaoAPIWordPress({
      email: config.auth.email,
      password: config.auth.password,
      baseURL: config.api.baseURL
    });
    this.resultados = [];
  }

  /**
   * 🧪 Executar todos os testes
   */
  async executarTodosTestes() {
    console.log('🧪 Iniciando testes de integração...\n');
    
    try {
      // Teste 1: Autenticação
      await this.testarAutenticacao();
      
      // Teste 2: Validação de campos
      await this.testarValidacaoCampos();
      
      // Teste 3: Upload de imagem
      await this.testarUploadImagem();
      
      // Teste 4: Criação de produto simples
      await this.testarCriacaoProdutoSimples();
      
      // Teste 5: Criação de produto complexo
      await this.testarCriacaoProdutoComplexo();
      
      // Teste 6: Tratamento de erros
      await this.testarTratamentoErros();
      
      this.exibirResumoTestes();
      
    } catch (error) {
      console.error('💥 Erro nos testes:', error.message);
    }
  }

  /**
   * 🔐 Teste de autenticação
   */
  async testarAutenticacao() {
    console.log('🔐 Testando autenticação...');
    
    try {
      await this.api.login();
      this.adicionarResultado('Autenticação', true, 'Login realizado com sucesso');
    } catch (error) {
      this.adicionarResultado('Autenticação', false, error.message);
    }
  }

  /**
   * ✅ Teste de validação de campos
   */
  async testarValidacaoCampos() {
    console.log('✅ Testando validação de campos...');
    
    try {
      // Teste com campos obrigatórios faltando
      const dadosIncompletos = {
        nome: "Produto Teste",
        // referencia faltando
        descricao: "Descrição teste",
        imagens: [],
        cores: [],
        categorias: []
      };
      
      await this.api.criarProduto(dadosIncompletos);
      this.adicionarResultado('Validação Campos', false, 'Deveria ter falhado');
    } catch (error) {
      if (error.message.includes('Campo obrigatório')) {
        this.adicionarResultado('Validação Campos', true, 'Validação funcionando corretamente');
      } else {
        this.adicionarResultado('Validação Campos', false, error.message);
      }
    }
  }

  /**
   * 🖼️ Teste de upload de imagem
   */
  async testarUploadImagem() {
    console.log('🖼️ Testando upload de imagem...');
    
    try {
      // Criar uma imagem de teste simples
      const imagemTeste = this.criarImagemTeste();
      const url = await this.api.uploadImagem(imagemTeste, 'Imagem de teste');
      
      if (url && url.includes('http')) {
        this.adicionarResultado('Upload Imagem', true, 'Upload realizado com sucesso');
      } else {
        this.adicionarResultado('Upload Imagem', false, 'URL inválida retornada');
      }
    } catch (error) {
      this.adicionarResultado('Upload Imagem', false, error.message);
    }
  }

  /**
   * 📦 Teste de criação de produto simples
   */
  async testarCriacaoProdutoSimples() {
    console.log('📦 Testando criação de produto simples...');
    
    try {
      const dadosProduto = {
        nome: `Produto Teste ${Date.now()}`,
        referencia: `TEST-${Date.now()}`,
        descricao: "Produto criado para teste de integração",
        preco: 99.99,
        imagens: [this.criarImagemTeste()],
        cores: [
          {
            nome: "Azul Teste",
            tipo: "codigo",
            codigo: "#0000FF"
          }
        ],
        categorias: ["Teste", "Integração"],
        informacoes_adicionais: "Produto de teste"
      };
      
      const resultado = await this.api.criarProduto(dadosProduto);
      
      if (resultado && resultado.id) {
        this.adicionarResultado('Criação Produto Simples', true, `Produto criado com ID: ${resultado.id}`);
      } else {
        this.adicionarResultado('Criação Produto Simples', false, 'Resposta inválida');
      }
    } catch (error) {
      this.adicionarResultado('Criação Produto Simples', false, error.message);
    }
  }

  /**
   * 🎨 Teste de criação de produto complexo
   */
  async testarCriacaoProdutoComplexo() {
    console.log('🎨 Testando criação de produto complexo...');
    
    try {
      const dadosProduto = {
        nome: `Produto Complexo ${Date.now()}`,
        referencia: `COMPLEX-${Date.now()}`,
        descricao: "Produto complexo com múltiplas imagens, cores híbridas e categorias",
        preco: 299.99,
        imagens: [
          this.criarImagemTeste(),
          this.criarImagemTeste()
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
            imagem: this.criarImagemTeste()
          },
          {
            nome: "Verde Neon",
            tipo: "codigo",
            codigo: "#00FF00",
            codigoNumerico: "65280"
          }
        ],
        categorias: ["Eletrônicos", "Teste", "Complexo"],
        informacoes_adicionais: "Produto complexo para teste completo"
      };
      
      const resultado = await this.api.criarProduto(dadosProduto);
      
      if (resultado && resultado.id) {
        this.adicionarResultado('Criação Produto Complexo', true, `Produto criado com ID: ${resultado.id}`);
      } else {
        this.adicionarResultado('Criação Produto Complexo', false, 'Resposta inválida');
      }
    } catch (error) {
      this.adicionarResultado('Criação Produto Complexo', false, error.message);
    }
  }

  /**
   * 🚨 Teste de tratamento de erros
   */
  async testarTratamentoErros() {
    console.log('🚨 Testando tratamento de erros...');
    
    try {
      // Teste com referência duplicada
      const dadosProduto = {
        nome: "Produto Duplicado",
        referencia: "DUPLICADO-001", // Mesma referência
        descricao: "Produto com referência duplicada",
        imagens: [this.criarImagemTeste()],
        cores: [{ nome: "Azul", tipo: "codigo", codigo: "#0000FF" }],
        categorias: ["Teste"]
      };
      
      // Primeira criação (deve funcionar)
      await this.api.criarProduto(dadosProduto);
      
      // Segunda criação (deve falhar)
      await this.api.criarProduto(dadosProduto);
      
      this.adicionarResultado('Tratamento Erros', false, 'Deveria ter detectado referência duplicada');
    } catch (error) {
      if (error.message.includes('já existe') || error.response?.status === 409) {
        this.adicionarResultado('Tratamento Erros', true, 'Erro de duplicação detectado corretamente');
      } else {
        this.adicionarResultado('Tratamento Erros', false, error.message);
      }
    }
  }

  /**
   * 🖼️ Criar imagem de teste
   */
  criarImagemTeste() {
    const fs = require('fs');
    const path = require('path');
    
    // Criar diretório de imagens se não existir
    const dirImagens = './imagens-teste';
    if (!fs.existsSync(dirImagens)) {
      fs.mkdirSync(dirImagens, { recursive: true });
    }
    
    // Criar um arquivo de imagem simples (1x1 pixel PNG)
    const imagemPath = path.join(dirImagens, `teste-${Date.now()}.png`);
    const imagemBuffer = Buffer.from([
      0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A, // PNG signature
      0x00, 0x00, 0x00, 0x0D, 0x49, 0x48, 0x44, 0x52, // IHDR chunk
      0x00, 0x00, 0x00, 0x01, 0x00, 0x00, 0x00, 0x01, // 1x1 pixel
      0x08, 0x02, 0x00, 0x00, 0x00, 0x90, 0x77, 0x53, 0xDE, // IHDR data
      0x00, 0x00, 0x00, 0x0C, 0x49, 0x44, 0x41, 0x54, // IDAT chunk
      0x08, 0x99, 0x01, 0x01, 0x00, 0x00, 0x00, 0xFF, 0xFF, 0x00, 0x00, 0x00, 0x02, 0x00, 0x01, // IDAT data
      0x00, 0x00, 0x00, 0x00, 0x49, 0x45, 0x4E, 0x44, 0xAE, 0x42, 0x60, 0x82 // IEND chunk
    ]);
    
    fs.writeFileSync(imagemPath, imagemBuffer);
    return imagemPath;
  }

  /**
   * 📊 Adicionar resultado do teste
   */
  adicionarResultado(nome, sucesso, mensagem) {
    this.resultados.push({
      nome,
      sucesso,
      mensagem,
      timestamp: new Date().toISOString()
    });
    
    const status = sucesso ? '✅' : '❌';
    console.log(`${status} ${nome}: ${mensagem}\n`);
  }

  /**
   * 📋 Exibir resumo dos testes
   */
  exibirResumoTestes() {
    console.log('📊 RESUMO DOS TESTES');
    console.log('==================');
    
    const sucessos = this.resultados.filter(r => r.sucesso).length;
    const falhas = this.resultados.filter(r => !r.sucesso).length;
    const total = this.resultados.length;
    
    console.log(`Total de testes: ${total}`);
    console.log(`✅ Sucessos: ${sucessos}`);
    console.log(`❌ Falhas: ${falhas}`);
    console.log(`📈 Taxa de sucesso: ${((sucessos / total) * 100).toFixed(1)}%\n`);
    
    if (falhas > 0) {
      console.log('❌ TESTES QUE FALHARAM:');
      this.resultados
        .filter(r => !r.sucesso)
        .forEach(r => console.log(`  - ${r.nome}: ${r.mensagem}`));
    }
    
    console.log('\n🎯 Próximos passos:');
    if (sucessos === total) {
      console.log('  ✅ Todos os testes passaram! A integração está funcionando perfeitamente.');
      console.log('  🚀 Você pode começar a usar a integração em produção.');
    } else {
      console.log('  🔧 Corrija os testes que falharam antes de usar em produção.');
      console.log('  📚 Consulte a documentação: http://localhost:8000/api-docs/');
    }
  }
}

// Executar testes se o arquivo for executado diretamente
if (require.main === module) {
  const teste = new TesteIntegracao();
  teste.executarTodosTestes().catch(console.error);
}

module.exports = TesteIntegracao;
