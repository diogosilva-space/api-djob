/**
 * ⚙️ Configurações para Integração Scraping → WordPress API
 */

module.exports = {
  // 🔗 URLs da API
  api: {
    baseURL: process.env.WP_API_URL || 'http://localhost:8000/wp-json/api/v1',
    mediaURL: process.env.WP_MEDIA_URL || 'http://localhost:8000/wp-json/wp/v2/media',
    documentacaoURL: process.env.WP_DOCS_URL || 'http://localhost:8000/api-docs/'
  },

  // 🔐 Credenciais de autenticação
  auth: {
    email: process.env.WP_USER_EMAIL || 'admin@exemplo.com',
    password: process.env.WP_USER_PASSWORD || 'senha123'
  },

  // ⏱️ Configurações de timeout e retry
  timeouts: {
    request: 30000, // 30 segundos
    upload: 60000,  // 60 segundos para uploads
    retry: 3,       // 3 tentativas
    retryDelay: 1000 // 1 segundo entre tentativas
  },

  // 🚦 Rate limiting
  rateLimit: {
    enabled: true,
    maxRequests: 10,    // 10 requisições
    timeWindow: 60000   // por minuto
  },

  // 📁 Caminhos de arquivos
  paths: {
    imagens: './imagens/',
    cores: './cores/',
    logs: './logs/',
    backup: './backup/'
  },

  // 📊 Configurações de processamento
  processamento: {
    delayEntreProdutos: 2000, // 2 segundos entre produtos
    maxProdutosPorLote: 50,   // máximo 50 produtos por lote
    salvarBackup: true,       // salvar backup dos dados
    gerarLogs: true          // gerar logs detalhados
  },

  // 🎨 Configurações de cores
  cores: {
    tiposSuportados: ['codigo', 'imagem'],
    formatosCodigo: ['hex', 'rgb', 'hsl', 'nome'],
    tamanhoMaxImagem: 5 * 1024 * 1024, // 5MB
    formatosImagem: ['jpg', 'jpeg', 'png', 'webp']
  },

  // 📦 Configurações de produtos
  produtos: {
    camposObrigatorios: ['nome', 'referencia', 'descricao', 'imagens', 'cores', 'categorias'],
    maxImagens: 10,
    maxCores: 20,
    maxCategorias: 10,
    tamanhoMaxDescricao: 5000 // caracteres
  },

  // 🔍 Configurações de validação
  validacao: {
    verificarReferenciaDuplicada: true,
    validarImagens: true,
    validarCores: true,
    sanitizarTexto: true
  },

  // 📝 Configurações de logs
  logs: {
    nivel: process.env.LOG_LEVEL || 'info', // debug, info, warn, error
    formato: 'json', // json, text
    rotacao: {
      enabled: true,
      maxSize: '10MB',
      maxFiles: 5
    }
  },

  // 🚨 Configurações de alertas
  alertas: {
    email: {
      enabled: false,
      smtp: {
        host: '',
        port: 587,
        secure: false,
        auth: {
          user: '',
          pass: ''
        }
      },
      destinatarios: []
    },
    webhook: {
      enabled: false,
      url: '',
      eventos: ['erro', 'sucesso', 'falha_autenticacao']
    }
  }
};
