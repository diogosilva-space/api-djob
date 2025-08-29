<?php

function api_documentacao_get($request) {
  $format = isset($request['format']) ? sanitize_text_field($request['format']) : 'json';
  
  $documentacao = array(
    'openapi' => '3.0.0',
    'info' => array(
      'title' => 'API Sistema de E-commerce WordPress',
      'description' => 'API REST completa para gerenciamento de usuários, produtos e transações',
      'version' => '1.0.0',
      'contact' => array(
        'name' => 'Suporte Técnico',
        'email' => 'suporte@exemplo.com'
      )
    ),
    'servers' => array(
      array(
        'url' => get_site_url() . '/wp-json/api/v1',
        'description' => 'Servidor de Produção'
      )
    ),
    'paths' => array(
      '/usuario' => array(
        'post' => array(
          'summary' => 'Criar novo usuário',
          'description' => 'Cria um novo usuário no sistema com dados completos',
          'tags' => ['Usuários'],
          'requestBody' => array(
            'required' => true,
            'content' => array(
              'application/json' => array(
                'schema' => array(
                  'type' => 'object',
                  'required' => ['user_email', 'user_pass', 'display_name'],
                  'properties' => array(
                    'user_email' => array(
                      'type' => 'string',
                      'format' => 'email',
                      'description' => 'Email do usuário'
                    ),
                    'user_pass' => array(
                      'type' => 'string',
                      'minLength' => 6,
                      'description' => 'Senha do usuário'
                    ),
                    'display_name' => array(
                      'type' => 'string',
                      'minLength' => 2,
                      'description' => 'Nome completo do usuário'
                    ),
                    'endereco' => array(
                      'type' => 'object',
                      'properties' => array(
                        'cep' => array('type' => 'string'),
                        'rua' => array('type' => 'string'),
                        'numero' => array('type' => 'string'),
                        'bairro' => array('type' => 'string'),
                        'cidade' => array('type' => 'string'),
                        'estado' => array('type' => 'string'),
                        'complemento' => array('type' => 'string')
                      )
                    )
                  )
                )
              )
            )
          ),
          'responses' => array(
            '201' => array(
              'description' => 'Usuário criado com sucesso',
              'content' => array(
                'application/json' => array(
                  'schema' => array(
                    'type' => 'object',
                    'properties' => array(
                      'status' => array('type' => 'string'),
                      'message' => array('type' => 'string'),
                      'usuario' => array('type' => 'object')
                    )
                  )
                )
              )
            )
          )
        ),
        'get' => array(
          'summary' => 'Buscar perfil do usuário',
          'description' => 'Retorna dados completos do usuário logado',
          'tags' => ['Usuários'],
          'security' => array(array('bearerAuth' => [])),
          'responses' => array(
            '200' => array(
              'description' => 'Dados do usuário',
              'content' => array(
                'application/json' => array(
                  'schema' => array('type' => 'object')
                )
              )
            )
          )
        ),
        'put' => array(
          'summary' => 'Atualizar dados do usuário',
          'description' => 'Atualiza dados do usuário logado',
          'tags' => ['Usuários'],
          'security' => array(array('bearerAuth' => [])),
          'requestBody' => array(
            'content' => array(
              'application/json' => array(
                'schema' => array(
                  'type' => 'object',
                  'properties' => array(
                    'display_name' => array('type' => 'string'),
                    'endereco' => array('type' => 'object'),
                    'preferencias' => array('type' => 'object')
                  )
                )
              )
            )
          ),
          'responses' => array(
            '200' => array(
              'description' => 'Usuário atualizado',
              'content' => array(
                'application/json' => array(
                  'schema' => array('type' => 'object')
                )
              )
            )
          )
        )
      ),
      '/usuario/login' => array(
        'post' => array(
          'summary' => 'Login de usuário',
          'description' => 'Autentica usuário e retorna dados do perfil',
          'tags' => ['Autenticação'],
          'requestBody' => array(
            'required' => true,
            'content' => array(
              'application/json' => array(
                'schema' => array(
                  'type' => 'object',
                  'required' => ['user_email', 'user_pass'],
                  'properties' => array(
                    'user_email' => array(
                      'type' => 'string',
                      'format' => 'email'
                    ),
                    'user_pass' => array('type' => 'string')
                  )
                )
              )
            )
          ),
          'responses' => array(
            '200' => array(
              'description' => 'Login realizado com sucesso'
            )
          )
        )
      ),
      '/produtos' => array(
        'get' => array(
          'summary' => 'Listar produtos',
          'description' => 'Lista produtos com filtros e paginação',
          'tags' => ['Produtos'],
          'parameters' => array(
            array(
              'name' => 'page',
              'in' => 'query',
              'description' => 'Número da página',
              'schema' => array('type' => 'integer', 'default' => 1)
            ),
            array(
              'name' => 'per_page',
              'in' => 'query',
              'description' => 'Itens por página',
              'schema' => array('type' => 'integer', 'default' => 10, 'maximum' => 100)
            ),
            array(
              'name' => 'categoria',
              'in' => 'query',
              'description' => 'Filtrar por categoria',
              'schema' => array('type' => 'string')
            ),
            array(
              'name' => 'preco_min',
              'in' => 'query',
              'description' => 'Preço mínimo',
              'schema' => array('type' => 'number')
            ),
            array(
              'name' => 'preco_max',
              'in' => 'query',
              'description' => 'Preço máximo',
              'schema' => array('type' => 'number')
            ),
            array(
              'name' => 'search',
              'in' => 'query',
              'description' => 'Termo de busca',
              'schema' => array('type' => 'string')
            )
          ),
          'responses' => array(
            '200' => array(
              'description' => 'Lista de produtos',
              'content' => array(
                'application/json' => array(
                  'schema' => array(
                    'type' => 'object',
                    'properties' => array(
                      'produtos' => array('type' => 'array'),
                      'paginacao' => array('type' => 'object'),
                      'filtros_aplicados' => array('type' => 'object')
                    )
                  )
                )
              )
            )
          )
        )
      ),
      '/produto' => array(
        'post' => array(
          'summary' => 'Criar produto',
          'description' => 'Cria um novo produto no sistema',
          'tags' => ['Produtos'],
          'security' => array(array('bearerAuth' => [])),
          'requestBody' => array(
            'required' => true,
            'content' => array(
              'multipart/form-data' => array(
                'schema' => array(
                  'type' => 'object',
                  'required' => ['referencia', 'nome', 'descricao', 'preco', 'categorias'],
                  'properties' => array(
                    'referencia' => array('type' => 'string'),
                    'nome' => array('type' => 'string'),
                    'descricao' => array('type' => 'string'),
                    'preco' => array('type' => 'number'),
                    'categorias' => array('type' => 'string'),
                    'cores' => array('type' => 'array'),
                    'imagens' => array('type' => 'array')
                  )
                )
              )
            )
          ),
          'responses' => array(
            '201' => array(
              'description' => 'Produto criado com sucesso'
            )
          )
        )
      ),
      '/estatisticas' => array(
        'get' => array(
          'summary' => 'Estatísticas e relatórios',
          'description' => 'Retorna estatísticas detalhadas sobre produtos e transações',
          'tags' => ['Relatórios'],
          'security' => array(array('bearerAuth' => [])),
          'parameters' => array(
            array(
              'name' => 'tipo',
              'in' => 'query',
              'description' => 'Tipo de estatística',
              'schema' => array(
                'type' => 'string',
                'enum' => ['geral', 'produtos', 'vendas', 'categorias'],
                'default' => 'geral'
              )
            ),
            array(
              'name' => 'periodo',
              'in' => 'query',
              'description' => 'Período dos dados',
              'schema' => array(
                'type' => 'string',
                'enum' => ['7dias', '30dias', '90dias', '6meses', '1ano', 'todos'],
                'default' => '30dias'
              )
            )
          ),
          'responses' => array(
            '200' => array(
              'description' => 'Estatísticas solicitadas'
            )
          )
        )
      )
    ),
    'components' => array(
      'securitySchemes' => array(
        'bearerAuth' => array(
          'type' => 'http',
          'scheme' => 'bearer',
          'bearerFormat' => 'JWT'
        )
      ),
      'schemas' => array(
        'Usuario' => array(
          'type' => 'object',
          'properties' => array(
            'id' => array('type' => 'integer'),
            'user_login' => array('type' => 'string'),
            'display_name' => array('type' => 'string'),
            'user_email' => array('type' => 'string', 'format' => 'email'),
            'role' => array('type' => 'string'),
            'status' => array('type' => 'string'),
            'endereco' => array('$ref' => '#/components/schemas/Endereco'),
            'preferencias' => array('$ref' => '#/components/schemas/Preferencias'),
            'estatisticas' => array('$ref' => '#/components/schemas/EstatisticasUsuario')
          )
        ),
        'Endereco' => array(
          'type' => 'object',
          'properties' => array(
            'cep' => array('type' => 'string'),
            'rua' => array('type' => 'string'),
            'numero' => array('type' => 'string'),
            'bairro' => array('type' => 'string'),
            'cidade' => array('type' => 'string'),
            'estado' => array('type' => 'string'),
            'complemento' => array('type' => 'string')
          )
        ),
        'Produto' => array(
          'type' => 'object',
          'properties' => array(
            'id' => array('type' => 'integer'),
            'referencia' => array('type' => 'string'),
            'nome' => array('type' => 'string'),
            'descricao' => array('type' => 'string'),
            'preco' => array('type' => 'number'),
            'categorias' => array('type' => 'string'),
            'cores' => array('type' => 'array'),
            'imagens' => array('type' => 'array'),
            'vendido' => array('type' => 'boolean')
          )
        )
      )
    ),
    'tags' => array(
      array('name' => 'Usuários', 'description' => 'Operações relacionadas a usuários'),
      array('name' => 'Autenticação', 'description' => 'Login e autenticação'),
      array('name' => 'Produtos', 'description' => 'Gerenciamento de produtos'),
      array('name' => 'Relatórios', 'description' => 'Estatísticas e relatórios')
    )
  );

  if ($format === 'html') {
    return gerar_html_documentacao($documentacao);
  }

  return rest_ensure_response($documentacao);
}

function gerar_html_documentacao($documentacao) {
  $html = '
  <!DOCTYPE html>
  <html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação da API - Sistema de E-commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism.min.css" rel="stylesheet">
    <style>
      .endpoint { margin-bottom: 2rem; padding: 1rem; border: 1px solid #dee2e6; border-radius: 0.375rem; }
      .method { font-weight: bold; padding: 0.25rem 0.5rem; border-radius: 0.25rem; color: white; }
      .method.post { background-color: #198754; }
      .method.get { background-color: #0d6efd; }
      .method.put { background-color: #fd7e14; }
      .method.delete { background-color: #dc3545; }
      .tag { background-color: #e9ecef; padding: 0.25rem 0.5rem; border-radius: 0.25rem; margin-right: 0.5rem; }
      .schema { background-color: #f8f9fa; padding: 1rem; border-radius: 0.375rem; margin-top: 1rem; }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
          <div class="position-sticky pt-3">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
              <span>Endpoints</span>
            </h6>
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link" href="#usuarios">Usuários</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#produtos">Produtos</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#estatisticas">Estatísticas</a>
              </li>
            </ul>
          </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">' . $documentacao['info']['title'] . '</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
              <div class="btn-group me-2">
                <a href="?format=json" class="btn btn-sm btn-outline-secondary">JSON</a>
                <a href="?format=html" class="btn btn-sm btn-outline-secondary">HTML</a>
              </div>
            </div>
          </div>

          <p class="lead">' . $documentacao['info']['description'] . '</p>
          
          <div class="alert alert-info">
            <strong>Base URL:</strong> ' . $documentacao['servers'][0]['url'] . '
          </div>

          <h2 id="usuarios">Usuários</h2>';

  foreach ($documentacao['paths'] as $path => $methods) {
    if (strpos($path, 'usuario') !== false) {
      foreach ($methods as $method => $details) {
        $html .= gerar_html_endpoint($path, $method, $details);
      }
    }
  }

  $html .= '
          <h2 id="produtos">Produtos</h2>';

  foreach ($documentacao['paths'] as $path => $methods) {
    if (strpos($path, 'produto') !== false) {
      foreach ($methods as $method => $details) {
        $html .= gerar_html_endpoint($path, $method, $details);
      }
    }
  }

  $html .= '
          <h2 id="estatisticas">Estatísticas</h2>';

  foreach ($documentacao['paths'] as $path => $methods) {
    if (strpos($path, 'estatisticas') !== false) {
      foreach ($methods as $method => $details) {
        $html .= gerar_html_endpoint($path, $method, $details);
      }
    }
  }

  $html .= '
        </main>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
  </body>
  </html>';

  return new WP_REST_Response($html, 200, array('Content-Type' => 'text/html'));
}

function gerar_html_endpoint($path, $method, $details) {
  $html = '
  <div class="endpoint">
    <div class="d-flex align-items-center mb-2">
      <span class="method ' . $method . '">' . strtoupper($method) . '</span>
      <code class="ms-2">' . $path . '</code>';
  
  if (isset($details['tags'])) {
    foreach ($details['tags'] as $tag) {
      $html .= '<span class="tag">' . $tag . '</span>';
    }
  }
  
  $html .= '
    </div>
    <h5>' . $details['summary'] . '</h5>
    <p>' . $details['description'] . '</p>';

  if (isset($details['parameters'])) {
    $html .= '<h6>Parâmetros:</h6><ul>';
    foreach ($details['parameters'] as $param) {
      $html .= '<li><strong>' . $param['name'] . '</strong> (' . $param['in'] . ') - ' . $param['description'] . '</li>';
    }
    $html .= '</ul>';
  }

  if (isset($details['requestBody'])) {
    $html .= '<h6>Corpo da Requisição:</h6>';
    $html .= '<div class="schema"><pre><code class="language-json">' . json_encode($details['requestBody']['content']['application/json']['schema'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</code></pre></div>';
  }

  $html .= '</div>';

  return $html;
}

function registrar_api_documentacao_get() {
  register_rest_route('api/v1', '/documentacao', array(
    array(
      'methods' => WP_REST_Server::READABLE,
      'callback' => 'api_documentacao_get',
      'permission_callback' => '__return_true',
      'args' => array(
        'format' => array(
          'default' => 'json',
          'enum' => array('json', 'html')
        )
      )
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_documentacao_get');

?>
