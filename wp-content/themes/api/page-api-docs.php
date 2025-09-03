<?php
/*
Template Name: API Documentation
*/

get_header(); ?>

<div class="api-docs-container">
  <div id="swagger-ui"></div>
</div>

<style>
.api-docs-container {
  padding: 20px;
  background: #f5f5f5;
  min-height: 100vh;
}

#swagger-ui {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  min-height: 80vh;
}

.loading {
  text-align: center;
  padding: 50px;
  font-size: 18px;
  color: #666;
}

.error {
  background: #f8d7da;
  color: #721c24;
  padding: 20px;
  border-radius: 8px;
  margin: 20px;
  text-align: center;
}

.success {
  background: #d4edda;
  color: #155724;
  padding: 20px;
  border-radius: 8px;
  margin: 20px;
  text-align: center;
}

.swagger-ui .topbar {
  background: #007cba;
  padding: 10px;
}

.swagger-ui .topbar .download-url-wrapper {
  display: none;
}

/* Estilo personalizado para a página */
.page-header {
  background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
  color: white;
  padding: 40px 20px;
  text-align: center;
  margin-bottom: 20px;
}

.page-header h1 {
  margin: 0;
  font-size: 2.5rem;
  font-weight: 300;
}

.page-header p {
  margin: 10px 0 0 0;
  font-size: 1.2rem;
  opacity: 0.9;
}

.api-info {
  background: white;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.api-info h2 {
  color: #007cba;
  margin-top: 0;
}

.api-info .info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.api-info .info-item {
  padding: 15px;
  background: #f8f9fa;
  border-radius: 6px;
  border-left: 4px solid #007cba;
}

.api-info .info-item h3 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 1.1rem;
}

.api-info .info-item p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}
</style>

<div class="page-header">
  <h1>📚 Documentação da API</h1>
  <p>Interface interativa para testar e explorar todos os endpoints da API</p>
</div>

<div class="api-info">
  <h2>🚀 Sobre esta API</h2>
  <p>Esta é a documentação completa da API do Sistema de E-commerce WordPress. Use a interface abaixo para testar todos os endpoints, visualizar exemplos de uso e entender a estrutura dos dados.</p>
  
  <div class="info-grid">
    <div class="info-item">
      <h3>🔐 Autenticação</h3>
      <p>Use JWT Bearer Token para endpoints protegidos. Faça login primeiro para obter o token.</p>
    </div>
    <div class="info-item">
      <h3>📦 Produtos</h3>
      <p>Gerencie produtos com cores híbridas, categorias múltiplas e busca inteligente.</p>
    </div>
    <div class="info-item">
      <h3>👥 Usuários</h3>
      <p>Crie e gerencie usuários com perfis completos e estatísticas.</p>
    </div>
    <div class="info-item">
      <h3>📊 Relatórios</h3>
      <p>Acesse estatísticas detalhadas sobre produtos e transações.</p>
    </div>
  </div>
</div>

<script>
window.onload = function() {
  // Mostrar loading
  document.getElementById('swagger-ui').innerHTML = '<div class="loading">🔄 Carregando documentação da API...</div>';
  
  // Verificar se o endpoint de documentação está funcionando
  fetch('<?php echo get_site_url(); ?>/wp-json/api/v1/documentacao')
    .then(response => {
      if (!response.ok) {
        throw new Error('Endpoint de documentação não está funcionando');
      }
      return response.json();
    })
    .then(spec => {
      // Carregar Swagger UI
      loadSwaggerUI(spec);
    })
    .catch(error => {
      console.error('Erro ao carregar documentação:', error);
      document.getElementById('swagger-ui').innerHTML = `
        <div class="error">
          <h3>❌ Erro ao carregar documentação</h3>
          <p>${error.message}</p>
          <p>Verifique se o endpoint <code>/wp-json/api/v1/documentacao</code> está funcionando.</p>
          <button onclick="location.reload()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
            🔄 Tentar Novamente
          </button>
        </div>
      `;
    });
};

function loadSwaggerUI(spec) {
  // Carregar Swagger UI dinamicamente
  const script = document.createElement('script');
  script.src = 'https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui-bundle.js';
  script.onload = function() {
    // Carregar CSS
    const css = document.createElement('link');
    css.rel = 'stylesheet';
    css.href = 'https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui.css';
    document.head.appendChild(css);
    
    // Inicializar Swagger UI
    initializeSwaggerUI(spec);
  };
  script.onerror = function() {
    document.getElementById('swagger-ui').innerHTML = `
      <div class="error">
        <h3>❌ Erro ao carregar Swagger UI</h3>
        <p>Não foi possível carregar a biblioteca Swagger UI.</p>
        <p>Verifique sua conexão com a internet e tente novamente.</p>
        <button onclick="location.reload()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
          🔄 Tentar Novamente
        </button>
      </div>
    `;
  };
  document.head.appendChild(script);
}

function initializeSwaggerUI(spec) {
  try {
    // Inicializar Swagger UI
    const ui = SwaggerUIBundle({
      spec: spec,
      dom_id: '#swagger-ui',
      deepLinking: true,
      presets: [
        SwaggerUIBundle.presets.apis
      ],
      plugins: [
        SwaggerUIBundle.plugins.DownloadUrl
      ],
      layout: "BaseLayout",
      docExpansion: "list",
      defaultModelsExpandDepth: 1,
      defaultModelExpandDepth: 1,
      displayRequestDuration: true,
      filter: true,
      showExtensions: true,
      showCommonExtensions: true,
      tryItOutEnabled: true,
      requestInterceptor: function(request) {
        // Adicionar token JWT se disponível
        const token = localStorage.getItem('jwt_token');
        if (token) {
          request.headers.Authorization = 'Bearer ' + token;
        }
        return request;
      },
      responseInterceptor: function(response) {
        // Log das respostas para debug
        console.log('API Response:', response);
        return response;
      },
      onComplete: function() {
        // Personalizar após carregamento
        console.log('Swagger UI carregado com sucesso!');
        
        // Mostrar sucesso
        document.getElementById('swagger-ui').insertAdjacentHTML('afterbegin', `
          <div class="success">
            <h3>✅ Swagger UI Carregado com Sucesso!</h3>
            <p>A documentação interativa da API está funcionando perfeitamente.</p>
            <p>Use a interface abaixo para testar os endpoints da API.</p>
          </div>
        `);
      }
    });
    
  } catch (error) {
    console.error('Erro ao inicializar Swagger UI:', error);
    document.getElementById('swagger-ui').innerHTML = `
      <div class="error">
        <h3>❌ Erro ao inicializar Swagger UI</h3>
        <p>${error.message}</p>
        <p>Verifique se todas as dependências foram carregadas corretamente.</p>
        <button onclick="location.reload()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
          🔄 Tentar Novamente
        </button>
      </div>
    `;
  }
}
</script>

<?php get_footer(); ?>
