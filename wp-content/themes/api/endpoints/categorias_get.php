<?php

function api_categorias_get($request) {
    global $wpdb;
    
    // Parâmetros opcionais
    $incluir_contadores = isset($request['incluir_contadores']) ? filter_var($request['incluir_contadores'], FILTER_VALIDATE_BOOLEAN) : false;
    $incluir_preco_medio = isset($request['incluir_preco_medio']) ? filter_var($request['incluir_preco_medio'], FILTER_VALIDATE_BOOLEAN) : false;
    $ordenar_por = isset($request['ordenar_por']) ? sanitize_text_field($request['ordenar_por']) : 'nome';
    $ordem = isset($request['ordenar']) ? sanitize_text_field($request['ordenar']) : 'ASC';
    
    // Validar parâmetros de ordenação
    $ordenar_por_valido = in_array($ordenar_por, ['nome', 'total_produtos', 'preco_medio']);
    $ordem_valida = in_array(strtoupper($ordem), ['ASC', 'DESC']);
    
    if (!$ordenar_por_valido) {
        return new WP_Error('parametro_invalido', 'Parâmetro ordenar_por deve ser: nome, total_produtos ou preco_medio', array('status' => 400));
    }
    
    if (!$ordem_valida) {
        return new WP_Error('parametro_invalido', 'Parâmetro ordenar deve ser: ASC ou DESC', array('status' => 400));
    }
    
    // Construir query base
    $select_fields = "DISTINCT pm.meta_value as categoria";
    $from_clause = "FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'categorias'";
    $where_clause = "WHERE p.post_type = 'produto' AND p.post_status = 'publish' AND pm.meta_value != '' AND pm.meta_value IS NOT NULL";
    
    // Se precisar de contadores ou preço médio, usar GROUP BY
    if ($incluir_contadores || $incluir_preco_medio) {
        $select_fields = "pm.meta_value as categoria";
        if ($incluir_contadores) {
            $select_fields .= ", COUNT(*) as total_produtos";
        }
        if ($incluir_preco_medio) {
            $select_fields .= ", AVG(CAST(pm2.meta_value AS DECIMAL(10,2))) as preco_medio";
            $from_clause .= " LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'preco'";
        }
        $group_by = "GROUP BY pm.meta_value";
    } else {
        $group_by = "";
    }
    
    // Construir ORDER BY
    $order_by = "ORDER BY ";
    switch ($ordenar_por) {
        case 'total_produtos':
            if ($incluir_contadores) {
                $order_by .= "total_produtos " . strtoupper($ordem);
            } else {
                $order_by .= "categoria " . strtoupper($ordem);
            }
            break;
        case 'preco_medio':
            if ($incluir_preco_medio) {
                $order_by .= "preco_medio " . strtoupper($ordem);
            } else {
                $order_by .= "categoria " . strtoupper($ordem);
            }
            break;
        default:
            $order_by .= "categoria " . strtoupper($ordem);
    }
    
    // Executar query
    $query = "SELECT {$select_fields} {$from_clause} {$where_clause} {$group_by} {$order_by}";
    $resultados = $wpdb->get_results($query);
    
    // Processar resultados
    $categorias = array();
    foreach ($resultados as $resultado) {
        // Decodificar o JSON das categorias
        $categorias_decodificadas = json_decode($resultado->categoria, true);
        
        // Se for um array, processar cada categoria individualmente
        if (is_array($categorias_decodificadas)) {
            foreach ($categorias_decodificadas as $categoria) {
                $categoria_data = array(
                    'categoria' => $categoria
                );
                
                if ($incluir_contadores) {
                    $categoria_data['total_produtos'] = intval($resultado->total_produtos);
                }
                
                if ($incluir_preco_medio) {
                    $categoria_data['preco_medio'] = floatval($resultado->preco_medio);
                }
                
                $categorias[] = $categoria_data;
            }
        } else {
            // Fallback para categorias que não são arrays (casos antigos)
            $categoria_data = array(
                'categoria' => $resultado->categoria
            );
            
            if ($incluir_contadores) {
                $categoria_data['total_produtos'] = intval($resultado->total_produtos);
            }
            
            if ($incluir_preco_medio) {
                $categoria_data['preco_medio'] = floatval($resultado->preco_medio);
            }
            
            $categorias[] = $categoria_data;
        }
    }
    
    // Resposta
    $response = array(
        'categorias' => $categorias,
        'total' => count($categorias),
        'parametros' => array(
            'incluir_contadores' => $incluir_contadores,
            'incluir_preco_medio' => $incluir_preco_medio,
            'ordenar_por' => $ordenar_por,
            'ordenar' => strtoupper($ordem)
        )
    );
    
    return rest_ensure_response($response);
}

function registrar_api_categorias_get() {
    register_rest_route('api/v1', '/categorias', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'api_categorias_get',
            'permission_callback' => '__return_true',
            'args' => array(
                'incluir_contadores' => array(
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean'
                ),
                'incluir_preco_medio' => array(
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean'
                ),
                'ordenar_por' => array(
                    'default' => 'nome',
                    'enum' => array('nome', 'total_produtos', 'preco_medio'),
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'ordenar' => array(
                    'default' => 'ASC',
                    'enum' => array('ASC', 'DESC'),
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_categorias_get');

?>
