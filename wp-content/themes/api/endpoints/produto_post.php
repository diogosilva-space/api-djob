<?php

function api_produto_post($request) {
    $usuario = obter_usuario_autenticado($request);

    if (!$usuario) {
        return new WP_Error('nao_autenticado', 'Usuário não autenticado.', array('status' => 401));
    }
    
    // Validação dos campos obrigatórios
    $campos_obrigatorios = ['nome', 'referencia', 'descricao', 'imagens', 'cores', 'categorias'];
    foreach ($campos_obrigatorios as $campo) {
        if ($campo === 'imagens') {
            // Validação especial para imagens (verificar se há arquivos)
            $files = $request->get_file_params();
            if (empty($files['imagens'])) {
                return new WP_Error('campo_obrigatorio', "Campo '$campo' é obrigatório. É necessário enviar pelo menos uma imagem.", array('status' => 400));
            }
        } elseif ($campo === 'cores') {
            // Validação especial para cores (verificar se há cores enviadas)
            $cores_param = $request->get_param('cores');
            if (empty($cores_param) || !is_array($cores_param)) {
                return new WP_Error('campo_obrigatorio', "Campo '$campo' é obrigatório. É necessário enviar pelo menos uma cor.", array('status' => 400));
            }
        } elseif ($campo === 'categorias') {
            // Validação especial para categorias (verificar se há categorias enviadas)
            $categorias_param = $request->get_param('categorias');
            if (empty($categorias_param) || !is_array($categorias_param)) {
                return new WP_Error('campo_obrigatorio', "Campo '$campo' é obrigatório. É necessário enviar pelo menos uma categoria.", array('status' => 400));
            }
        } elseif (empty($request[$campo])) {
            return new WP_Error('campo_obrigatorio', "Campo '$campo' é obrigatório.", array('status' => 400));
        }
    }

    // Sanitização dos dados de texto
    $nome = sanitize_text_field($request['nome']);
    $referencia = sanitize_text_field($request['referencia']);
    $descricao = sanitize_textarea_field($request['descricao']);
    // Processar categorias como array
    $categorias_param = $request->get_param('categorias');
    $categorias = array();
    if (!empty($categorias_param) && is_array($categorias_param)) {
        foreach ($categorias_param as $categoria) {
            $categoria_sanitizada = sanitize_text_field($categoria);
            if (!empty($categoria_sanitizada)) {
                $categorias[] = $categoria_sanitizada;
            }
        }
    }
    $preco = isset($request['preco']) ? floatval($request['preco']) : 0;

    // Verificar se a referência já existe
    $produto_existente = get_posts(array(
        'post_type' => 'produto',
        'meta_query' => array(
            array(
                'key' => 'referencia',
                'value' => $referencia,
                'compare' => '='
            )
        ),
        'numberposts' => 1
    ));

    if (!empty($produto_existente)) {
        return new WP_Error('referencia_existente', 'Referência já cadastrada.', array('status' => 409));
    }

    // Processar cores - Suporte a imagens e códigos de cor
    $cores_processed = array();
    $cores_param = $request->get_param('cores');
    $files = $request->get_file_params();
    
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
                }
            } elseif ($cor['tipo'] === 'codigo') {
                // Processar código de cor
                $cor_processed['codigo'] = sanitize_text_field($cor['codigo']);
                $cor_processed['codigoNumerico'] = isset($cor['codigoNumerico']) ? sanitize_text_field($cor['codigoNumerico']) : '';
            }
            
            $cores_processed[] = $cor_processed;
        }
    }

    // Processar upload de imagens do produto
    $imagens_urls = array();
    $imagens_ids = array();
    $files = $request->get_file_params();
    
    if (!empty($files['imagens'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
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
                            'post_title' => sanitize_text_field($files['imagens']['name'][$i]),
                            'post_content' => 'Imagem do produto: ' . $nome,
                            'post_excerpt' => 'Imagem do produto ' . $nome,
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
                        'post_content' => 'Imagem do produto: ' . $nome,
                        'post_excerpt' => 'Imagem do produto ' . $nome,
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
    }

    // Validar se pelo menos uma imagem foi processada com sucesso
    if (empty($imagens_urls)) {
        return new WP_Error('imagem_obrigatoria', 'É necessário enviar pelo menos uma imagem válida.', array('status' => 400));
    }

    // Validar se pelo menos uma cor foi processada com sucesso
    if (empty($cores_processed)) {
        return new WP_Error('cores_obrigatorias', 'É necessário enviar pelo menos uma cor válida.', array('status' => 400));
    }

    // Validar se pelo menos uma categoria foi processada com sucesso
    if (empty($categorias)) {
        return new WP_Error('categorias_obrigatorias', 'É necessário enviar pelo menos uma categoria válida.', array('status' => 400));
    }

    // Criar o produto
    $post_data = array(
        'post_author' => $usuario->ID,
        'post_type' => 'produto',
        'post_title' => $nome,
        'post_content' => $descricao,
        'post_status' => 'publish',
        'meta_input' => array(
            'referencia' => $referencia,
            'descricao' => $descricao,
            'cores' => json_encode($cores_processed, JSON_UNESCAPED_UNICODE),
            'imagens' => json_encode($imagens_urls, JSON_UNESCAPED_UNICODE),
            'imagens_ids' => json_encode($imagens_ids, JSON_UNESCAPED_UNICODE),
            'categorias' => json_encode($categorias, JSON_UNESCAPED_UNICODE),
            'preco' => $preco,
            'usuario_id' => $usuario->ID, // Usar ID numérico em vez de login
            'usuario_login' => $usuario->user_login,

        ),
    );

    $produto_id = wp_insert_post($post_data);
    
    if (is_wp_error($produto_id)) {
        return $produto_id;
    }

    // Vincular as imagens ao produto após a criação
    if (!empty($imagens_ids)) {
        foreach ($imagens_ids as $attachment_id) {
            // Atualizar o anexo para associar ao produto
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_parent' => $produto_id
            ));
            
            // Adicionar metadados específicos do produto
            update_post_meta($attachment_id, '_produto_id', $produto_id);
            update_post_meta($attachment_id, '_usuario_id', $usuario->ID);
        }
    }

    // Retornar resposta com ID do produto
    $response_data = array(
        'id' => $produto_id,
        'slug' => get_post_field('post_name', $produto_id),
        'status' => 'success',
        'message' => 'Produto criado com sucesso',
        'imagens_enviadas' => $imagens_urls,
        'imagens_ids' => $imagens_ids,
        'usuario_id' => $usuario->ID,
        'usuario_login' => $usuario->user_login,
        'cores_processadas' => $cores_processed,
        'categorias_processadas' => $categorias
    );

    return rest_ensure_response($response_data);
}

function registrar_api_produto_post() {
    register_rest_route('api/v1', '/produto', array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'api_produto_post',
            'permission_callback' => '__return_true',
            'args' => array(
                'referencia' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'nome' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'descricao' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field'
                ),
                'preco' => array(
                    'required' => false,
                    'type' => 'number'
                ),
                'categorias' => array(
                    'required' => true,
                    'type' => 'array',
                    'items' => array(
                        'type' => 'string'
                    ),
                    'validate_callback' => function($param, $request, $key) {
                        return is_array($param) && !empty($param);
                    }
                ),
                'cores' => array(
                    'required' => true,
                    'type' => 'array',
                    'items' => array(
                        'type' => 'object',
                        'properties' => array(
                            'nome' => array('type' => 'string'),
                            'tipo' => array('type' => 'string', 'enum' => ['imagem', 'codigo']),
                            'codigo' => array('type' => 'string'),
                            'codigoNumerico' => array('type' => 'string')
                        ),
                        'required' => ['nome', 'tipo']
                    ),
                    'validate_callback' => function($param, $request, $key) {
                        return is_array($param) && !empty($param);
                    }
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

add_action('rest_api_init', 'registrar_api_produto_post');

?>