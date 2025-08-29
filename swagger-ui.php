<?php
/*
Template Name: Swagger UI - Documentação da API
*/

get_header(); ?>

<div class="swagger-container">
  <div id="swagger-ui"></div>
</div>

<style>
.swagger-container {
  padding: 20px;
  background: #f5f5f5;
  min-height: 100vh;
}

#swagger-ui {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
</style>

<script>
window.onload = function() {
  // Mostrar loading
  document.getElementById('swagger-ui').innerHTML = '<div class="loading">Carregando documentação da API...</div>';
  
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
          <h3>Erro ao carregar documentação</h3>
          <p>${error.message}</p>
          <p>Verifique se o endpoint <code>/wp-json/api/v1/documentacao</code> está funcionando.</p>
          <button onclick="location.reload()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Tentar Novamente
          </button>
        </div>
      `;
    });
};

function loadSwaggerUI(spec) {
  // Carregar Swagger UI dinamicamente - versão mais recente e estável
  const script = document.createElement('script');
  script.src = 'https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui-bundle.js';
  script.onload = function() {
    // Carregar CSS adicional
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
        <h3>Erro ao carregar Swagger UI</h3>
        <p>Não foi possível carregar a biblioteca Swagger UI.</p>
        <p>Verifique sua conexão com a internet e tente novamente.</p>
        <button onclick="location.reload()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
          Tentar Novamente
        </button>
      </div>
    `;
  };
  document.head.appendChild(script);
}

function initializeSwaggerUI(spec) {
  try {
    // Usar configuração mais simples e estável
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
        
        // Adicionar botão de teste de autenticação
        const authBtn = document.createElement('button');
        authBtn.innerHTML = '🔑 Testar Autenticação';
        authBtn.className = 'btn btn-primary';
        authBtn.style.margin = '10px';
        authBtn.style.padding = '8px 16px';
        authBtn.style.border = 'none';
        authBtn.style.borderRadius = '4px';
        authBtn.style.backgroundColor = '#007cba';
        authBtn.style.color = 'white';
        authBtn.style.cursor = 'pointer';
        authBtn.onclick = function() {
          testAuthentication();
        };
        
        // Adicionar botão ao cabeçalho do Swagger UI
        const swaggerHeader = document.querySelector('.swagger-ui .topbar');
        if (swaggerHeader) {
          swaggerHeader.appendChild(authBtn);
        }
        
        // Adicionar botão de login
        const loginBtn = document.createElement('button');
        loginBtn.innerHTML = '🔐 Fazer Login';
        loginBtn.style.margin = '10px';
        loginBtn.style.padding = '8px 16px';
        loginBtn.style.border = 'none';
        loginBtn.style.borderRadius = '4px';
        loginBtn.style.backgroundColor = '#28a745';
        loginBtn.style.color = 'white';
        loginBtn.style.cursor = 'pointer';
        loginBtn.onclick = function() {
          showLoginModal();
        };
        
        if (swaggerHeader) {
          swaggerHeader.appendChild(loginBtn);
        }
        
        // Mostrar sucesso
        document.getElementById('swagger-ui').insertAdjacentHTML('afterbegin', `
          <div class="success">
            <h3>✅ Swagger UI Carregado com Sucesso!</h3>
            <p>A documentação interativa da API está funcionando perfeitamente.</p>
            <p>Use os botões acima para testar autenticação e fazer login.</p>
          </div>
        `);
      }
    });
    
    // Função para testar autenticação
    window.testAuthentication = function() {
      const token = localStorage.getItem('jwt_token');
      if (!token) {
        alert('Nenhum token JWT encontrado. Faça login primeiro.');
        return;
      }
      
      // Testar endpoint protegido
      fetch('<?php echo get_site_url(); ?>/wp-json/api/v1/usuario', {
        headers: {
          'Authorization': 'Bearer ' + token
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.id) {
          alert('✅ Autenticação válida! Usuário: ' + data.display_name);
        } else {
          alert('❌ Erro na autenticação: ' + (data.message || 'Token inválido'));
        }
      })
      .catch(error => {
        alert('❌ Erro na requisição: ' + error.message);
      });
    };
    
    // Função para mostrar modal de login
    window.showLoginModal = function() {
      // Criar modal de login
      const modal = document.createElement('div');
      modal.id = 'loginModal';
      modal.style.cssText = `
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
      `;
      
      modal.innerHTML = `
        <div style="background-color: white; padding: 30px; border-radius: 8px; width: 350px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
          <h3 style="margin-top: 0; color: #333;">🔑 Login para Testar API</h3>
          <p style="color: #666; margin-bottom: 20px;">Digite suas credenciais para testar endpoints protegidos:</p>
          
          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: #333;">Email:</label>
            <input type="email" id="loginEmail" placeholder="seu@email.com" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
          </div>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; color: #333;">Senha:</label>
            <input type="password" id="loginPassword" placeholder="Sua senha" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
          </div>
          
          <div style="display: flex; gap: 10px;">
            <button onclick="window.loginToAPI()" style="flex: 1; padding: 12px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
              Entrar
            </button>
            <button onclick="document.getElementById('loginModal').remove()" style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
              Cancelar
            </button>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    };
    
    // Função para fazer login
    window.loginToAPI = function() {
      const email = document.getElementById('loginEmail').value;
      const password = document.getElementById('loginPassword').value;
      
      if (!email || !password) {
        alert('Por favor, preencha email e senha.');
        return;
      }
      
      fetch('<?php echo get_site_url(); ?>/wp-json/api/v1/usuario/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          user_email: email,
          user_pass: password
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          // Armazenar token (em produção, use httpOnly cookies)
          localStorage.setItem('jwt_token', 'demo_token');
          localStorage.setItem('user_info', JSON.stringify(data.usuario));
          alert('✅ Login realizado com sucesso!');
          document.getElementById('loginModal').remove();
        } else {
          alert('❌ Erro no login: ' + (data.message || 'Credenciais inválidas'));
        }
      })
      .catch(error => {
        alert('❌ Erro na requisição: ' + error.message);
      });
    };
    
  } catch (error) {
    console.error('Erro ao inicializar Swagger UI:', error);
    document.getElementById('swagger-ui').innerHTML = `
      <div class="error">
        <h3>Erro ao inicializar Swagger UI</h3>
        <p>${error.message}</p>
        <p>Verifique se todas as dependências foram carregadas corretamente.</p>
        <button onclick="location.reload()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
          Tentar Novamente
        </button>
      </div>
    `;
  }
}
</script>

<?php get_footer(); ?>
