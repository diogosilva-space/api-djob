<?php

function api_documentacao_get($request) {
  $format = isset($request['format']) ? sanitize_text_field($request['format']) : 'json';
  
  $documentacao = array(
    'openapi' => '3.0.0',
    'info' => array(
      'title' => 'API Sistema de E-commerce WordPress',
      'description' => 'API REST completa para gerenciamento de usuários, produtos e transações com funcionalidades avançadas de busca e categorização',
      'version' => '2.0.0',
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
          'summary' => 'Listar produtos com busca avançada',
          'description' => 'Lista produtos com filtros avançados, busca inteligente e paginação',
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
              'description' => 'Filtrar por categoria específica',
              'schema' => array('type' => 'string')
            ),
            array(
              'name' => 'cores',
              'in' => 'query',
              'description' => 'Filtrar por cor específica',
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
              'description' => 'Busca por palavras-chave no nome/slug',
              'schema' => array('type' => 'string')
            ),
            array(
              'name' => 'referencia',
              'in' => 'query',
              'description' => 'Busca por referência específica',
              'schema' => array('type' => 'string')
            ),
            array(
              'name' => 'buscar_descricao',
              'in' => 'query',
              'description' => 'Incluir descrição na busca',
              'schema' => array('type' => 'boolean', 'default' => false)
            ),
            array(
              'name' => 'ordenar_por',
              'in' => 'query',
              'description' => 'Campo para ordenação',
              'schema' => array(
                'type' => 'string',
                'enum' => ['data', 'nome', 'preco'],
                'default' => 'data'
              )
            ),
            array(
              'name' => 'ordem',
              'in' => 'query',
              'description' => 'Direção da ordenação',
              'schema' => array(
                'type' => 'string',
                'enum' => ['asc', 'desc'],
                'default' => 'desc'
              )
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
          'description' => 'Cria um novo produto no sistema com cores híbridas e categorias obrigatórias',
          'tags' => ['Produtos'],
          'security' => array(array('bearerAuth' => [])),
          'requestBody' => array(
            'required' => true,
            'content' => array(
              'multipart/form-data' => array(
                'schema' => array(
                  'type' => 'object',
                  'required' => ['nome', 'referencia', 'descricao', 'imagens', 'cores', 'categorias'],
                  'properties' => array(
                    'nome' => array(
                      'type' => 'string',
                      'description' => 'Nome do produto'
                    ),
                    'referencia' => array(
                      'type' => 'string',
                      'description' => 'Referência única do produto'
                    ),
                    'descricao' => array(
                      'type' => 'string',
                      'description' => 'Descrição detalhada do produto'
                    ),
                    'preco' => array(
                      'type' => 'number',
                      'format' => 'float',
                      'description' => 'Preço do produto'
                    ),
                    'categorias' => array(
                      'type' => 'array',
                      'items' => array('type' => 'string'),
                      'description' => 'Array de categorias (obrigatório, pelo menos uma)',
                      'minItems' => 1
                    ),
                    'cores' => array(
                      'type' => 'array',
                      'items' => array(
                        'type' => 'object',
                        'required' => ['nome', 'tipo'],
                        'properties' => array(
                          'nome' => array('type' => 'string'),
                          'tipo' => array(
                            'type' => 'string',
                            'enum' => ['imagem', 'codigo']
                          ),
                          'codigo' => array('type' => 'string'),
                          'codigoNumerico' => array('type' => 'string')
                        )
                      ),
                      'description' => 'Array de cores híbridas (obrigatório, pelo menos uma)',
                      'minItems' => 1
                    ),
                    'imagens' => array(
                      'type' => 'array',
                      'items' => array(
                        'type' => 'string',
                        'format' => 'binary'
                      ),
                      'description' => 'Array de imagens do produto (obrigatório, pelo menos uma)',
                      'minItems' => 1
                    ),
                    'informacoes_adicionais' => array(
                      'type' => 'string',
                      'description' => 'Informações adicionais do produto'
                    )
                  )
                )
              )
            )
          ),
          'responses' => array(
            '201' => array(
              'description' => 'Produto criado com sucesso',
              'content' => array(
                'application/json' => array(
                  'schema' => array(
                    'type' => 'object',
                    'properties' => array(
                      'id' => array('type' => 'integer'),
                      'slug' => array('type' => 'string'),
                      'status' => array('type' => 'string'),
                      'message' => array('type' => 'string'),
                      'imagens_enviadas' => array('type' => 'array'),
                      'cores_processadas' => array('type' => 'array'),
                      'categorias_processadas' => array('type' => 'array')
                    )
                  )
                )
              )
            )
          )
        )
      ),
      '/produto/{id}' => array(
        'get' => array(
          'summary' => 'Buscar produto por ID, slug, referência ou palavras-chave',
          'description' => 'Busca inteligente de produto por múltiplos critérios',
          'tags' => ['Produtos'],
          'parameters' => array(
            array(
              'name' => 'id',
              'in' => 'path',
              'required' => true,
              'description' => 'ID numérico, slug, referência ou palavras-chave',
              'schema' => array('type' => 'string')
            )
          ),
          'responses' => array(
            '200' => array(
              'description' => 'Produto encontrado',
              'content' => array(
                'application/json' => array(
                  'schema' => array('$ref' => '#/components/schemas/Produto')
                )
              )
            ),
            '404' => array(
              'description' => 'Produto não encontrado'
            )
          )
        ),
        'put' => array(
          'summary' => 'Atualizar produto',
          'description' => 'Atualiza dados de um produto existente',
          'tags' => ['Produtos'],
          'security' => array(array('bearerAuth' => [])),
          'parameters' => array(
            array(
              'name' => 'id',
              'in' => 'path',
              'required' => true,
              'schema' => array('type' => 'integer')
            )
          ),
          'requestBody' => array(
            'content' => array(
              'application/json' => array(
                'schema' => array(
                  'type' => 'object',
                  'properties' => array(
                    'nome' => array('type' => 'string'),
                    'referencia' => array('type' => 'string'),
                    'descricao' => array('type' => 'string'),
                    'preco' => array('type' => 'number'),
                    'categorias' => array('type' => 'array'),
                    'cores' => array('type' => 'array'),
                    'informacoes_adicionais' => array('type' => 'string')
                  )
                )
              )
            )
          ),
          'responses' => array(
            '200' => array(
              'description' => 'Produto atualizado',
              'content' => array(
                'application/json' => array(
                  'schema' => array('$ref' => '#/components/schemas/Produto')
                )
              )
            )
          )
        ),
        'delete' => array(
          'summary' => 'Excluir produto',
          'description' => 'Exclui um produto do sistema',
          'tags' => ['Produtos'],
          'security' => array(array('bearerAuth' => [])),
          'parameters' => array(
            array(
              'name' => 'id',
              'in' => 'path',
              'required' => true,
              'schema' => array('type' => 'integer')
            )
          ),
          'responses' => array(
            '200' => array(
              'description' => 'Produto excluído com sucesso'
            )
          )
        )
      ),
      '/categorias' => array(
        'get' => array(
          'summary' => 'Listar categorias de produtos',
          'description' => 'Retorna todas as categorias disponíveis dos produtos com opções de contadores e preço médio',
          'tags' => ['Produtos'],
          'parameters' => array(
            array(
              'name' => 'incluir_contadores',
              'in' => 'query',
              'description' => 'Incluir contador de produtos por categoria',
              'schema' => array('type' => 'boolean', 'default' => false)
            ),
            array(
              'name' => 'incluir_preco_medio',
              'in' => 'query',
              'description' => 'Incluir preço médio por categoria',
              'schema' => array('type' => 'boolean', 'default' => false)
            ),
            array(
              'name' => 'ordenar_por',
              'in' => 'query',
              'description' => 'Campo para ordenação',
              'schema' => array(
                'type' => 'string',
                'enum' => ['nome', 'total_produtos', 'preco_medio'],
                'default' => 'nome'
              )
            ),
            array(
              'name' => 'ordenar',
              'in' => 'query',
              'description' => 'Direção da ordenação',
              'schema' => array(
                'type' => 'string',
                'enum' => ['ASC', 'DESC'],
                'default' => 'ASC'
              )
            )
          ),
          'responses' => array(
            '200' => array(
              'description' => 'Lista de categorias',
              'content' => array(
                'application/json' => array(
                  'schema' => array(
                    'type' => 'object',
                    'properties' => array(
                      'categorias' => array(
                        'type' => 'array',
                        'items' => array(
                          'type' => 'object',
                          'properties' => array(
                            'categoria' => array('type' => 'string'),
                            'total_produtos' => array('type' => 'integer'),
                            'preco_medio' => array('type' => 'number')
                          )
                        )
                      ),
                      'total' => array('type' => 'integer'),
                      'parametros' => array('type' => 'object')
                    )
                  )
                )
              )
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
            'slug' => array('type' => 'string'),
            'referencia' => array('type' => 'string'),
            'nome' => array('type' => 'string'),
            'descricao' => array('type' => 'string'),
            'preco' => array('type' => 'number'),
            'categorias' => array(
              'type' => 'array',
              'items' => array('type' => 'string'),
              'description' => 'Array de categorias do produto'
            ),
            'cores' => array(
              'type' => 'array',
              'items' => array(
                'type' => 'object',
                'properties' => array(
                  'nome' => array('type' => 'string'),
                  'tipo' => array('type' => 'string'),
                  'imagem' => array('type' => 'string'),
                  'codigo' => array('type' => 'string'),
                  'codigoNumerico' => array('type' => 'string')
                )
              ),
              'description' => 'Array de cores híbridas (imagem ou código)'
            ),
            'imagens' => array(
              'type' => 'array',
              'items' => array('type' => 'string'),
              'description' => 'Array de URLs das imagens'
            ),
            'informacoes_adicionais' => array('type' => 'string'),
            'usuario_id' => array('type' => 'string'),
            'data_criacao' => array('type' => 'string', 'format' => 'date-time'),
            'data_modificacao' => array('type' => 'string', 'format' => 'date-time'),
            'autor' => array(
              'type' => 'object',
              'properties' => array(
                'id' => array('type' => 'integer'),
                'nome' => array('type' => 'string'),
                'email' => array('type' => 'string')
              )
            )
          )
        ),
        'Paginacao' => array(
          'type' => 'object',
          'properties' => array(
            'pagina_atual' => array('type' => 'integer'),
            'total_paginas' => array('type' => 'integer'),
            'total_produtos' => array('type' => 'integer'),
            'produtos_por_pagina' => array('type' => 'integer')
          )
        ),
        'Transacao' => array(
          'type' => 'object',
          'properties' => array(
            'id' => array('type' => 'integer'),
            'produto_id' => array('type' => 'integer'),
            'quantidade' => array('type' => 'integer'),
            'valor_total' => array('type' => 'number'),
            'observacoes' => array('type' => 'string'),
            'data_criacao' => array('type' => 'string', 'format' => 'date-time'),
            'usuario_id' => array('type' => 'integer')
          )
        )
      )
    ),
    'tags' => array(
      array('name' => 'Usuários', 'description' => 'Operações relacionadas a usuários'),
      array('name' => 'Autenticação', 'description' => 'Login e autenticação JWT'),
      array('name' => 'Produtos', 'description' => 'Gerenciamento de produtos com busca inteligente'),
      array('name' => 'Transações', 'description' => 'Gerenciamento de transações de compra'),
      array('name' => 'Relatórios', 'description' => 'Estatísticas e relatórios do sistema')
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
