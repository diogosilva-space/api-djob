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

  // Cores - Processar cores com suporte a upload de imagens
  if (isset($request['cores'])) {
    $cores_processed = array();
    $cores_param = $request->get_param('cores');
    $files = $request->get_file_params();
    
    // Se cores vem como string JSON (multipart/form-data), decodificar
    if (is_string($cores_param)) {
      $cores_param = json_decode($cores_param, true);
    }
    
    if (!empty($cores_param) && is_array($cores_param)) {
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      require_once(ABSPATH . 'wp-admin/includes/file.php');
      require_once(ABSPATH . 'wp-admin/includes/media.php');
      
      foreach ($cores_param as $index => $cor) {
        $cor_processed = array(
          'nome' => sanitize_text_field($cor['nome']),
          'tipo' => sanitize_text_field($cor['tipo']),
          'imagem' => '',
          'codigo' => '',
          'codigoNumerico' => ''
        );
        
        if ($cor['tipo'] === 'imagem') {
          // Processar upload de imagem da cor
          $cor_file_key = "cores_imagem_{$index}";
          if (!empty($files[$cor_file_key])) {
            $file = $files[$cor_file_key];
            
            if ($file['error'] === UPLOAD_ERR_OK) {
              $upload = wp_handle_upload($file, array(
                'test_form' => false,
                'mimes' => array(
                  'jpg|jpeg|jpe' => 'image/jpeg',
                  'gif' => 'image/gif',
                  'png' => 'image/png',
                  'webp' => 'image/webp'
                )
              ));
              
              if (!isset($upload['error']) && isset($upload['file'])) {
                // Criar anexo na biblioteca de mídia
                $attachment = array(
                  'post_mime_type' => $upload['type'],
                  'post_title' => sanitize_text_field($file['name']),
                  'post_content' => 'Imagem da cor: ' . $cor_processed['nome'],
                  'post_excerpt' => 'Imagem da cor ' . $cor_processed['nome'],
                  'post_status' => 'inherit'
                );
                
                $attachment_id = wp_insert_attachment($attachment, $upload['file']);
                
                if (!is_wp_error($attachment_id)) {
                  // Gerar metadados do anexo
                  $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                  wp_update_attachment_metadata($attachment_id, $attach_data);
                  
                  $cor_processed['imagem'] = $upload['url'];
                }
              }
            }
          } else {
            // Se não há arquivo novo, manter imagem existente se fornecida
            $cor_processed['imagem'] = isset($cor['imagem']) ? esc_url_raw($cor['imagem']) : '';
          }
        } elseif ($cor['tipo'] === 'codigo') {
          // Processar código de cor
          $cor_processed['codigo'] = sanitize_text_field($cor['codigo']);
          $cor_processed['codigoNumerico'] = isset($cor['codigoNumerico']) ? sanitize_text_field($cor['codigoNumerico']) : '';
        }
        
        $cores_processed[] = $cor_processed;
      }
    }
    
    $meta_updates['cores'] = json_encode($cores_processed, JSON_UNESCAPED_UNICODE);
  }

  // Imagens - Processar imagens do produto com suporte a upload
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

  // Processar upload de novas imagens do produto se fornecidas
  $files = $request->get_file_params();
  if (!empty($files['imagens'])) {
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    
    $imagens_urls = array();
    $imagens_ids = array();
    
    // Verificar se é array múltiplo (múltiplos arquivos com mesmo nome)
    if (is_array($files['imagens']['name'])) {
      $file_count = count($files['imagens']['name']);
      
      for ($i = 0; $i < $file_count; $i++) {
        // Verificar se o arquivo foi enviado corretamente
        if ($files['imagens']['error'][$i] === UPLOAD_ERR_OK) {
          $file = array(
            'name' => $files['imagens']['name'][$i],
            'type' => $files['imagens']['type'][$i],
            'tmp_name' => $files['imagens']['tmp_name'][$i],
            'error' => $files['imagens']['error'][$i],
            'size' => $files['imagens']['size'][$i]
          );
          
          // Usar wp_handle_upload para fazer o upload físico
          $upload = wp_handle_upload($file, array(
            'test_form' => false,
            'mimes' => array(
              'jpg|jpeg|jpe' => 'image/jpeg',
              'gif' => 'image/gif',
              'png' => 'image/png',
              'webp' => 'image/webp'
            )
          ));
          
          if (!isset($upload['error']) && isset($upload['file'])) {
            // Criar anexo na biblioteca de mídia
            $attachment = array(
              'post_mime_type' => $upload['type'],
              'post_title' => sanitize_text_field($file['name']),
              'post_content' => 'Imagem do produto: ' . get_the_title($post->ID),
              'post_excerpt' => 'Imagem do produto ' . get_the_title($post->ID),
              'post_status' => 'inherit'
            );
            
            $attachment_id = wp_insert_attachment($attachment, $upload['file']);
            
            if (!is_wp_error($attachment_id)) {
              // Gerar metadados do anexo
              $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
              wp_update_attachment_metadata($attachment_id, $attach_data);
              
              $imagens_urls[] = $upload['url'];
              $imagens_ids[] = $attachment_id;
            }
          }
        }
      }
    } else {
      // Apenas um arquivo
      if ($files['imagens']['error'] === UPLOAD_ERR_OK) {
        $upload = wp_handle_upload($files['imagens'], array(
          'test_form' => false,
          'mimes' => array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'webp' => 'image/webp'
          )
        ));
        
        if (!isset($upload['error']) && isset($upload['file'])) {
          // Criar anexo na biblioteca de mídia
          $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_text_field($files['imagens']['name']),
            'post_content' => 'Imagem do produto: ' . get_the_title($post->ID),
            'post_excerpt' => 'Imagem do produto ' . get_the_title($post->ID),
            'post_status' => 'inherit'
          );
          
          $attachment_id = wp_insert_attachment($attachment, $upload['file']);
          
          if (!is_wp_error($attachment_id)) {
            // Gerar metadados do anexo
            $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attach_data);
            
            $imagens_urls[] = $upload['url'];
            $imagens_ids[] = $attachment_id;
          }
        }
      }
    }
    
    // Atualizar metadados com novas imagens se houver upload
    if (!empty($imagens_urls)) {
      // Combinar imagens existentes com novas
      $imagens_existentes = json_decode(get_post_meta($post->ID, 'imagens', true), true) ?: array();
      $imagens_combinadas = array_merge($imagens_existentes, $imagens_urls);
      
      update_post_meta($post->ID, 'imagens', json_encode($imagens_combinadas, JSON_UNESCAPED_UNICODE));
      update_post_meta($post->ID, 'imagens_ids', json_encode($imagens_ids, JSON_UNESCAPED_UNICODE));
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
    'produto' => $produto_atualizado,
    'cores_processadas' => $cores_processed ?? array(),
    'imagens_enviadas' => $imagens_urls ?? array()
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
          'type' => array('string', 'array'),
          'sanitize_callback' => function($param, $request, $key) {
            // Se for string, retornar como está (será decodificado no processamento)
            if (is_string($param)) {
              return $param;
            }
            // Se for array, retornar como está
            if (is_array($param)) {
              return $param;
            }
            return null;
          }
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
