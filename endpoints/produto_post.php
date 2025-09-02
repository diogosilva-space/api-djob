<?php

function api_produto_post($request) {
    // Verificar autenticação via middleware
    $usuario = obter_usuario_autenticado($request);
    
    if (!$usuario) {
        return new WP_Error('nao_autenticado', 'Usuário não autenticado.', array('status' => 401));
    }
    
    $user_id = $usuario->ID;
    
    // Validação dos campos obrigatórios
    $campos_obrigatorios = ['referencia', 'nome', 'descricao', 'preco', 'categorias'];
    foreach ($campos_obrigatorios as $campo) {
        if (empty($request[$campo])) {
            return new WP_Error('campo_obrigatorio', "Campo '$campo' é obrigatório.", array('status' => 400));
        }
    }

    // Sanitização dos dados
    $referencia = sanitize_text_field($request['referencia']);
    $nome = sanitize_text_field($request['nome']);
    $descricao = sanitize_textarea_field($request['descricao']);
    $preco = floatval($request['preco']);
    $categorias = sanitize_text_field($request['categorias']);
    $informacoes_adicionais = isset($request['informacoes_adicionais']) ? 
        sanitize_textarea_field($request['informacoes_adicionais']) : '';
    
    // Validação do preço
    if ($preco <= 0) {
        return new WP_Error('preco_invalido', 'Preço deve ser maior que zero.', array('status' => 400));
    }

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

    // Processar cores
    $cores = array();
    if (isset($request['cores']) && is_array($request['cores'])) {
        foreach ($request['cores'] as $cor) {
            $cores[] = array(
                'nome' => sanitize_text_field($cor['nome']),
                'imagem' => isset($cor['imagem']) ? esc_url_raw($cor['imagem']) : '',
                'codigo' => isset($cor['codigo']) ? sanitize_text_field($cor['codigo']) : '',
                'tipo' => sanitize_text_field($cor['tipo']),
                'codigoNumerico' => sanitize_text_field($cor['codigoNumerico'])
            );
        }
    }

    // Processar imagens
    $imagens = array();
    if (isset($request['imagens']) && is_array($request['imagens'])) {
        foreach ($request['imagens'] as $imagem) {
            $imagens[] = esc_url_raw($imagem);
        }
    }

    // Criar o produto
    $post_data = array(
        'post_author' => $user_id,
        'post_type' => 'produto',
        'post_title' => $nome,
        'post_content' => $descricao,
        'post_status' => 'publish',
        'meta_input' => array(
            'referencia' => $referencia,
            'descricao' => $descricao,
            'cores' => json_encode($cores, JSON_UNESCAPED_UNICODE),
            'imagens' => json_encode($imagens, JSON_UNESCAPED_UNICODE),
            'categorias' => $categorias,
            'informacoes_adicionais' => $informacoes_adicionais,
            'preco' => $preco,
            'usuario_id' => $usuario->user_login,
            'vendido' => 'false',
        ),
    );

    $produto_id = wp_insert_post($post_data);
    
    if (is_wp_error($produto_id)) {
        return $produto_id;
    }

    // Processar upload de arquivos de imagem
    $files = $request->get_file_params();
    if($files) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $imagens_upload = array();
        foreach ($files as $file => $array) {
            $attachment_id = media_handle_upload($file, $produto_id);
            if (!is_wp_error($attachment_id)) {
                $imagens_upload[] = wp_get_attachment_url($attachment_id);
            }
        }
        
        // Atualizar metadados com as imagens enviadas
        if (!empty($imagens_upload)) {
            update_post_meta($produto_id, 'imagens_upload', json_encode($imagens_upload));
        }
    }

    // Retornar resposta com ID do produto
    $response_data = array(
        'id' => $produto_id,
        'slug' => get_post_field('post_name', $produto_id),
        'status' => 'success',
        'message' => 'Produto criado com sucesso'
    );

    return rest_ensure_response($response_data);
}

function registrar_api_produto_post() {
    register_rest_route('api/v1', '/produto', array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'api_produto_post',
            'permission_callback' => '__return_true', // Removido para evitar conflito com middleware
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
                    'required' => true,
                    'type' => 'number',
                    'sanitize_callback' => 'floatval'
                ),
                'categorias' => array(
                    'required' => true,
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

add_action('rest_api_init', 'registrar_api_produto_post');

?>
