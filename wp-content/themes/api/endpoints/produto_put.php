<?php

function api_produto_put($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;

  if($user_id <= 0) {
    return new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
  }

  $produto_id = $request['id'];
  
  // Verificar se o produto existe
  if (is_numeric($produto_id)) {
    $post = get_post($produto_id);
  } else {
    $post = get_page_by_path($produto_id, OBJECT, 'produto');
  }

  if (!$post || $post->post_type !== 'produto') {
    return new WP_Error('produto_nao_encontrado', 'Produto não encontrado.', array('status' => 404));
  }

  // Verificar se o usuário é o dono do produto
  if ($post->post_author != $user_id) {
    return new WP_Error('permissao', 'Você só pode editar seus próprios produtos.', array('status' => 403));
  }



  // Preparar dados para atualização
  $update_data = array(
    'ID' => $post->ID,
    'post_status' => 'publish'
  );

  // Atualizar título se fornecido
  if (isset($request['nome']) && !empty($request['nome'])) {
    $update_data['post_title'] = sanitize_text_field($request['nome']);
  }

  // Atualizar descrição se fornecida
  if (isset($request['descricao']) && !empty($request['descricao'])) {
    $update_data['post_content'] = sanitize_textarea_field($request['descricao']);
  }

  // Atualizar post
  $post_id = wp_update_post($update_data);
  
  if (is_wp_error($post_id)) {
    return $post_id;
  }

  // Atualizar metadados
  $meta_updates = array();

  // Referência (com validação de unicidade)
  if (isset($request['referencia']) && !empty($request['referencia'])) {
    $nova_referencia = sanitize_text_field($request['referencia']);
    $referencia_atual = get_post_meta($post->ID, 'referencia', true);
    
    if ($nova_referencia !== $referencia_atual) {
      // Verificar se a nova referência já existe
      $produto_existente = get_posts(array(
        'post_type' => 'produto',
        'meta_query' => array(
          array(
            'key' => 'referencia',
            'value' => $nova_referencia,
            'compare' => '='
          )
        ),
        'numberposts' => 1,
        'exclude' => array($post->ID)
      ));

      if (!empty($produto_existente)) {
        return new WP_Error('referencia_existente', 'Referência já cadastrada.', array('status' => 409));
      }
      
      $meta_updates['referencia'] = $nova_referencia;
    }
  }

  // Preço
  if (isset($request['preco'])) {
    $preco = floatval($request['preco']);
    if ($preco > 0) {
      $meta_updates['preco'] = $preco;
    } else {
      return new WP_Error('preco_invalido', 'Preço deve ser maior que zero.', array('status' => 400));
    }
  }

  // Categorias
  if (isset($request['categorias'])) {
    $meta_updates['categorias'] = sanitize_text_field($request['categorias']);
  }

  // Descrição
  if (isset($request['descricao'])) {
    $meta_updates['descricao'] = sanitize_textarea_field($request['descricao']);
  }

  // Informações adicionais
  if (isset($request['informacoes_adicionais'])) {
    $meta_updates['informacoes_adicionais'] = sanitize_textarea_field($request['informacoes_adicionais']);
  }

  // Cores
  if (isset($request['cores']) && is_array($request['cores'])) {
    $cores = array();
    foreach ($request['cores'] as $cor) {
      $cores[] = array(
        'nome' => sanitize_text_field($cor['nome']),
        'imagem' => isset($cor['imagem']) ? esc_url_raw($cor['imagem']) : '',
        'codigo' => isset($cor['codigo']) ? sanitize_text_field($cor['codigo']) : '',
        'tipo' => sanitize_text_field($cor['tipo']),
        'codigoNumerico' => sanitize_text_field($cor['codigoNumerico'])
      );
    }
    $meta_updates['cores'] = json_encode($cores, JSON_UNESCAPED_UNICODE);
  }

  // Imagens
  if (isset($request['imagens']) && is_array($request['imagens'])) {
    $imagens = array();
    foreach ($request['imagens'] as $imagem) {
      $imagens[] = esc_url_raw($imagem);
    }
    $meta_updates['imagens'] = json_encode($imagens, JSON_UNESCAPED_UNICODE);
  }

  // Aplicar atualizações de metadados
  foreach ($meta_updates as $key => $value) {
    update_post_meta($post->ID, $key, $value);
  }

  // Processar upload de novas imagens se fornecidas
  $files = $request->get_file_params();
  if($files) {
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    $imagens_upload = array();
    foreach ($files as $file => $array) {
      $attachment_id = media_handle_upload($file, $post->ID);
      if (!is_wp_error($attachment_id)) {
        $imagens_upload[] = wp_get_attachment_url($attachment_id);
      }
    }
    
    if (!empty($imagens_upload)) {
      update_post_meta($post->ID, 'imagens_upload', json_encode($imagens_upload));
    }
  }

  // Buscar dados atualizados
  $referencia = get_post_meta($post->ID, 'referencia', true);
  $descricao = get_post_meta($post->ID, 'descricao', true);
  $cores = json_decode(get_post_meta($post->ID, 'cores', true), true);
  $imagens = json_decode(get_post_meta($post->ID, 'imagens', true), true);
  $categorias = get_post_meta($post->ID, 'categorias', true);
  $informacoes_adicionais = get_post_meta($post->ID, 'informacoes_adicionais', true);
  $preco = get_post_meta($post->ID, 'preco', true);

  $usuario_id = get_post_meta($post->ID, 'usuario_id', true);

  $produto_atualizado = array(
    'id' => $post->ID,
    'slug' => get_post_field('post_name', $post->ID),
    'referencia' => $referencia,
    'nome' => get_the_title($post->ID),
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

  $response = array(
    'status' => 'success',
    'message' => 'Produto atualizado com sucesso',
    'produto' => $produto_atualizado
  );

  return rest_ensure_response($response);
}

function registrar_api_produto_put() {
  register_rest_route('api/v1', '/produto/(?P<id>[a-zA-Z0-9-]+)', array(
    array(
      'methods' => WP_REST_Server::EDITABLE,
      'callback' => 'api_produto_put',
      'permission_callback' => '__return_true',
      'args' => array(
        'id' => array(
          'required' => true,
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'nome' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'descricao' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_textarea_field'
        ),
        'referencia' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'preco' => array(
          'required' => false,
          'type' => 'number',
          'sanitize_callback' => 'floatval'
        ),
        'categorias' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'cores' => array(
          'required' => false,
          'type' => 'array',
          'items' => array(
            'type' => 'object',
            'properties' => array(
              'nome' => array('type' => 'string'),
              'imagem' => array('type' => 'string'),
              'codigo' => array('type' => 'string'),
              'tipo' => array('type' => 'string'),
              'codigoNumerico' => array('type' => 'string')
            )
          )
        ),
        'imagens' => array(
          'required' => false,
          'type' => 'array',
          'items' => array('type' => 'string')
        ),
        'informacoes_adicionais' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_textarea_field'
        )
      )
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_produto_put');

?>
