<?php

function api_produto_get($request) {
  // Parâmetros de paginação e filtros
  $page = isset($request['page']) ? intval($request['page']) : 1;
  $per_page = isset($request['per_page']) ? intval($request['per_page']) : 10;
  $search = isset($request['search']) ? sanitize_text_field($request['search']) : '';
  $categoria = isset($request['categoria']) ? sanitize_text_field($request['categoria']) : '';
  $preco_min = isset($request['preco_min']) ? floatval($request['preco_min']) : 0;
  $preco_max = isset($request['preco_max']) ? floatval($request['preco_max']) : 999999;
  $status = isset($request['status']) ? sanitize_text_field($request['status']) : 'disponivel';
  $ordenar_por = isset($request['ordenar_por']) ? sanitize_text_field($request['ordenar_por']) : 'date';
  $ordem = isset($request['ordenar']) ? sanitize_text_field($request['ordenar']) : 'DESC';
  $referencia = isset($request['referencia']) ? sanitize_text_field($request['referencia']) : '';

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

  // Filtro por status de venda
  if ($status === 'disponivel') {
    $meta_query[] = array(
      'key' => 'vendido',
      'value' => 'false',
      'compare' => '='
    );
  } elseif ($status === 'vendido') {
    $meta_query[] = array(
      'key' => 'vendido',
      'value' => 'true',
      'compare' => '='
    );
  }

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

  if (count($meta_query) > 1) {
    $args['meta_query'] = $meta_query;
  }

  // Busca por texto
  if (!empty($search)) {
    $args['s'] = $search;
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
      $categorias = get_post_meta($post_id, 'categorias', true);
      $informacoes_adicionais = get_post_meta($post_id, 'informacoes_adicionais', true);
      $preco = get_post_meta($post_id, 'preco', true);
      $vendido = get_post_meta($post_id, 'vendido', true);
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
        'vendido' => $vendido === 'true',
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

function api_produto_get_single($request) {
  $id = $request['id'];
  
  // Buscar produto por ID ou slug
  $post = get_page_by_path($id, OBJECT, 'produto');
  
  if (!$post) {
    $post = get_post($id);
  }
  
  if (!$post || $post->post_type !== 'produto') {
    return new WP_Error('produto_nao_encontrado', 'Produto não encontrado.', array('status' => 404));
  }

  // Buscar metadados
  $referencia = get_post_meta($post->ID, 'referencia', true);
  $descricao = get_post_meta($post->ID, 'descricao', true);
  $cores = json_decode(get_post_meta($post->ID, 'cores', true), true);
  $imagens = json_decode(get_post_meta($post->ID, 'imagens', true), true);
  $categorias = get_post_meta($post->ID, 'categorias', true);
  $informacoes_adicionais = get_post_meta($post->ID, 'informacoes_adicionais', true);
  $preco = get_post_meta($post->ID, 'preco', true);
  $vendido = get_post_meta($post->ID, 'vendido', true);
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
    'vendido' => $vendido === 'true',
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
  // Endpoint para listar produtos com filtros
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
        'status' => array(
          'default' => 'disponivel',
          'enum' => array('disponivel', 'vendido', 'todos')
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
