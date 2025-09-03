<?php

function api_produto_get($request) {

  log_simple('🚀 ROTA ACESSADA: /wp-json/api/v1/produtos');

  // Parâmetros de paginação e filtros
  $page = isset($request['page']) ? intval($request['page']) : 1;
  $per_page = isset($request['per_page']) ? intval($request['per_page']) : 10;
  $search = isset($request['search']) ? sanitize_text_field($request['search']) : '';
  $categoria = isset($request['categoria']) ? sanitize_text_field($request['categoria']) : '';
  $preco_min = isset($request['preco_min']) ? floatval($request['preco_min']) : 0;
  $preco_max = isset($request['preco_max']) ? floatval($request['preco_max']) : 999999;

  $ordenar_por = isset($request['ordenar_por']) ? sanitize_text_field($request['ordenar_por']) : 'date';
  $ordem = isset($request['ordenar']) ? sanitize_text_field($request['ordenar']) : 'DESC';
  $referencia = isset($request['referencia']) ? sanitize_text_field($request['referencia']) : '';
  
  // Novos parâmetros de busca
  $cores = isset($request['cores']) ? sanitize_text_field($request['cores']) : '';
  $buscar_descricao = isset($request['buscar_descricao']) ? filter_var($request['buscar_descricao'], FILTER_VALIDATE_BOOLEAN) : false;

  // Construir query
  $args = array(
    'post_type' => 'produto',
    'post_status' => 'publish',
    'posts_per_page' => $per_page,
    'paged' => $page,
    'orderby' => $ordenar_por,
    'order' => $ordem
  );

  // Meta query para filtros
  $meta_query = array('relation' => 'AND');



  // Filtro por preço
  if ($preco_min > 0 || $preco_max < 999999) {
    $meta_query[] = array(
      'key' => 'preco',
      'value' => array($preco_min, $preco_max),
      'type' => 'NUMERIC',
      'compare' => 'BETWEEN'
    );
  }

  // Filtro por categoria
  if (!empty($categoria)) {
    $meta_query[] = array(
      'key' => 'categorias',
      'value' => $categoria,
      'compare' => 'LIKE'
    );
  }

  // Filtro por referência
  if (!empty($referencia)) {
    $meta_query[] = array(
      'key' => 'referencia',
      'value' => $referencia,
      'compare' => 'LIKE'
    );
  }

  // Filtro por cores
  if (!empty($cores)) {
    $meta_query[] = array(
      'key' => 'cores',
      'value' => $cores,
      'compare' => 'LIKE'
    );
  }

  if (count($meta_query) > 1) {
    $args['meta_query'] = $meta_query;
  }

  // Busca por texto
  if (!empty($search)) {
    $args['s'] = $search;
    
    // Se buscar_descricao for true, adicionar busca na descrição via meta_query
    if ($buscar_descricao) {
      $meta_query[] = array(
        'key' => 'descricao',
        'value' => $search,
        'compare' => 'LIKE'
      );
    }
  }

  // Query personalizada para ordenação por preço
  if ($ordenar_por === 'preco') {
    $args['meta_key'] = 'preco';
    $args['orderby'] = 'meta_value_num';
  }

  $query = new WP_Query($args);
  $produtos = array();

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $post_id = get_the_ID();
      
      // Buscar metadados
      $referencia = get_post_meta($post_id, 'referencia', true);
      $descricao = get_post_meta($post_id, 'descricao', true);
      $cores = json_decode(get_post_meta($post_id, 'cores', true), true);
      $imagens = json_decode(get_post_meta($post_id, 'imagens', true), true);
      $categorias = json_decode(get_post_meta($post_id, 'categorias', true), true);
      $informacoes_adicionais = get_post_meta($post_id, 'informacoes_adicionais', true);
      $preco = get_post_meta($post_id, 'preco', true);

      $usuario_id = get_post_meta($post_id, 'usuario_id', true);

      $produto = array(
        'id' => $post_id,
        'slug' => get_post_field('post_name', $post_id),
        'referencia' => $referencia,
        'nome' => get_the_title($post_id),
        'descricao' => $descricao,
        'cores' => $cores ?: array(),
        'imagens' => $imagens ?: array(),
        'categorias' => $categorias,
        'informacoes_adicionais' => $informacoes_adicionais,
        'preco' => floatval($preco),

        'usuario_id' => $usuario_id,
        'data_criacao' => get_the_date('c', $post_id),
        'data_modificacao' => get_the_modified_date('c', $post_id),
        'autor' => array(
          'id' => get_the_author_meta('ID', get_post_field('post_author', $post_id)),
          'nome' => get_the_author_meta('display_name', get_post_field('post_author', $post_id)),
          'email' => get_the_author_meta('user_email', get_post_field('post_author', $post_id))
        )
      );

      $produtos[] = $produto;
    }
    wp_reset_postdata();
  }

  // Informações de paginação
  $total_posts = $query->found_posts;
  $total_pages = ceil($total_posts / $per_page);

  $response = array(
    'produtos' => $produtos,
    'paginacao' => array(
      'pagina_atual' => $page,
      'total_paginas' => $total_pages,
      'total_produtos' => $total_posts,
      'produtos_por_pagina' => $per_page
    )
  );

  return rest_ensure_response($response);
}

// Função auxiliar para busca inteligente
function buscar_produto_inteligente($busca) {
  // 1. Tentar ID numérico
  if (is_numeric($busca)) {
    $post = get_post($busca);
    if ($post && $post->post_type === 'produto') {
      return $post;
    }
  }
  
  // 2. Tentar slug exato
  $post = get_page_by_path($busca, OBJECT, 'produto');
  if ($post) {
    return $post;
  }
  
  // 3. Tentar referência exata
  $posts = get_posts(array(
    'post_type' => 'produto',
    'post_status' => 'publish',
    'meta_query' => array(
      array(
        'key' => 'referencia',
        'value' => $busca,
        'compare' => '='
      )
    ),
    'numberposts' => 1
  ));
  if (!empty($posts)) {
    return $posts[0];
  }
  
  // 4. Busca por palavras-chave (nome/slug)
  $palavras = explode('-', $busca);
  $search_terms = array();
  
  // Adicionar palavras individuais
  foreach ($palavras as $palavra) {
    if (strlen($palavra) > 2) { // Ignorar palavras muito curtas
      $search_terms[] = $palavra;
    }
  }
  
  if (!empty($search_terms)) {
    // Busca no título
    $posts = get_posts(array(
      'post_type' => 'produto',
      'post_status' => 'publish',
      's' => implode(' ', $search_terms),
      'numberposts' => 1
    ));
    if (!empty($posts)) {
      return $posts[0];
    }
    
    // Busca por referência parcial
    $posts = get_posts(array(
      'post_type' => 'produto',
      'post_status' => 'publish',
      'meta_query' => array(
        array(
          'key' => 'referencia',
          'value' => $busca,
          'compare' => 'LIKE'
        )
      ),
      'numberposts' => 1
    ));
    if (!empty($posts)) {
      return $posts[0];
    }
  }
  
  return null;
}

function api_produto_get_single($request) {
  $id = $request['id'];
  
  // Usar busca inteligente
  $post = buscar_produto_inteligente($id);
  
  if (!$post) {
    return new WP_Error('produto_nao_encontrado', 'Produto não encontrado.', array('status' => 404));
  }

  // Buscar metadados
  $referencia = get_post_meta($post->ID, 'referencia', true);
  $descricao = get_post_meta($post->ID, 'descricao', true);
  $cores = json_decode(get_post_meta($post->ID, 'cores', true), true);
  $imagens = json_decode(get_post_meta($post->ID, 'imagens', true), true);
  $categorias = json_decode(get_post_meta($post->ID, 'categorias', true), true);
  $informacoes_adicionais = get_post_meta($post->ID, 'informacoes_adicionais', true);
  $preco = get_post_meta($post->ID, 'preco', true);

  $usuario_id = get_post_meta($post->ID, 'usuario_id', true);

  $produto = array(
    'id' => $post->ID,
    'slug' => $post->post_name,
    'referencia' => $referencia,
    'nome' => $post->post_title,
    'descricao' => $descricao,
    'cores' => $cores ?: array(),
    'imagens' => $imagens ?: array(),
    'categorias' => $categorias,
    'informacoes_adicionais' => $informacoes_adicionais,
    'preco' => floatval($preco),

    'usuario_id' => $usuario_id,
    'data_criacao' => get_the_date('c', $post->ID),
    'data_modificacao' => get_the_modified_date('c', $post->ID),
    'autor' => array(
      'id' => get_the_author_meta('ID', $post->post_author),
      'nome' => get_the_author_meta('display_name', $post->post_author),
      'email' => get_the_author_meta('user_email', $post->post_author)
    )
  );

  return rest_ensure_response($produto);
}

function registrar_api_produto_get() {
  register_rest_route('api/v1', '/produtos', array(
    array(
      'methods' => WP_REST_Server::READABLE,
      'callback' => 'api_produto_get',
      'permission_callback' => '__return_true',
      'args' => array(
        'page' => array(
          'default' => 1,
          'sanitize_callback' => 'absint'
        ),
        'per_page' => array(
          'default' => 10,
          'sanitize_callback' => 'absint',
          'validate_callback' => function($param) {
            return $param <= 100;
          }
        ),
        'search' => array(
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'categoria' => array(
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'preco_min' => array(
          'sanitize_callback' => 'floatval'
        ),
        'preco_max' => array(
          'sanitize_callback' => 'floatval'
        ),

        'ordenar_por' => array(
          'default' => 'date',
          'enum' => array('date', 'title', 'preco', 'referencia')
        ),
        'ordenar' => array(
          'default' => 'DESC',
          'enum' => array('ASC', 'DESC')
        ),
        'referencia' => array(
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'cores' => array(
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'buscar_descricao' => array(
          'default' => false,
          'sanitize_callback' => 'rest_sanitize_boolean'
        )
      )
    ),
  ));

  // Endpoint para buscar produto específico
  register_rest_route('api/v1', '/produto/(?P<id>[a-zA-Z0-9-]+)', array(
    array(
      'methods' => WP_REST_Server::READABLE,
      'callback' => 'api_produto_get_single',
      'permission_callback' => '__return_true',
      'args' => array(
        'id' => array(
          'required' => true,
          'sanitize_callback' => 'sanitize_text_field'
        )
      )
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_produto_get');

?>
