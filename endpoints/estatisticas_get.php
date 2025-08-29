<?php

function api_estatisticas_get($request) {
  // Verificar autenticação via middleware
  $usuario = obter_usuario_autenticado($request);
  
  if (!$usuario) {
    return new WP_Error('nao_autenticado', 'Usuário não autenticado.', array('status' => 401));
  }
  
  $user_id = $usuario->ID;

  $tipo = isset($request['tipo']) ? sanitize_text_field($request['tipo']) : 'geral';
  $periodo = isset($request['periodo']) ? sanitize_text_field($request['periodo']) : '30dias';

  $estatisticas = array();

  switch ($tipo) {
    case 'produtos':
      $estatisticas = get_estatisticas_produtos($user_id, $periodo);
      break;
    case 'vendas':
      $estatisticas = get_estatisticas_vendas($user_id, $periodo);
      break;
    case 'categorias':
      $estatisticas = get_estatisticas_categorias($periodo);
      break;
    case 'geral':
    default:
      $estatisticas = array(
        'produtos' => get_estatisticas_produtos($user_id, $periodo),
        'vendas' => get_estatisticas_vendas($user_id, $periodo),
        'categorias' => get_estatisticas_categorias($periodo)
      );
      break;
  }

  $response = array(
    'status' => 'success',
    'tipo' => $tipo,
    'periodo' => $periodo,
    'data_geracao' => current_time('c'),
    'estatisticas' => $estatisticas
  );

  return rest_ensure_response($response);
}

function get_estatisticas_produtos($user_id, $periodo) {
  $args = array(
    'post_type' => 'produto',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_query' => array(
      array(
        'key' => 'usuario_id',
        'value' => get_user_by('id', $user_id)->user_login,
        'compare' => '='
      )
    )
  );

  // Filtrar por período se especificado
  if ($periodo !== 'todos') {
    $args['date_query'] = get_date_query_by_period($periodo);
  }

  $query = new WP_Query($args);
  $total_produtos = $query->found_posts;
  
  // Produtos vendidos
  $args_vendidos = $args;
  $args_vendidos['meta_query'][] = array(
    'key' => 'vendido',
    'value' => 'true',
    'compare' => '='
  );
  $query_vendidos = new WP_Query($args_vendidos);
  $produtos_vendidos = $query_vendidos->found_posts;

  // Produtos disponíveis
  $args_disponiveis = $args;
  $args_disponiveis['meta_query'][] = array(
    'key' => 'vendido',
    'value' => 'false',
    'compare' => '='
  );
  $query_disponiveis = new WP_Query($args_disponiveis);
  $produtos_disponiveis = $query_disponiveis->found_posts;

  // Valor total dos produtos
  $valor_total = 0;
  $valor_vendido = 0;
  
  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $preco = floatval(get_post_meta(get_the_ID(), 'preco', true));
      $vendido = get_post_meta(get_the_ID(), 'vendido', true);
      
      $valor_total += $preco;
      if ($vendido === 'true') {
        $valor_vendido += $preco;
      }
    }
  }
  wp_reset_postdata();

  return array(
    'total_produtos' => $total_produtos,
    'produtos_vendidos' => $produtos_vendidos,
    'produtos_disponiveis' => $produtos_disponiveis,
    'taxa_venda' => $total_produtos > 0 ? round(($produtos_vendidos / $total_produtos) * 100, 2) : 0,
    'valor_total' => $valor_total,
    'valor_vendido' => $valor_vendido,
    'valor_disponivel' => $valor_total - $valor_vendido
  );
}

function get_estatisticas_vendas($user_id, $periodo) {
  $args = array(
    'post_type' => 'transacao',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_query' => array(
      'relation' => 'OR',
      array(
        'key' => 'vendedor_id',
        'value' => get_user_by('id', $user_id)->user_login,
        'compare' => '='
      ),
      array(
        'key' => 'comprador_id',
        'value' => get_user_by('id', $user_id)->user_login,
        'compare' => '='
      )
    )
  );

  // Filtrar por período se especificado
  if ($periodo !== 'todos') {
    $args['date_query'] = get_date_query_by_period($periodo);
  }

  $query = new WP_Query($args);
  $total_transacoes = $query->found_posts;
  
  $vendas_como_vendedor = 0;
  $compras_como_comprador = 0;
  $valor_total_vendas = 0;
  $valor_total_compras = 0;

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      $vendedor_id = get_post_meta(get_the_ID(), 'vendedor_id', true);
      $comprador_id = get_post_meta(get_the_ID(), 'comprador_id', true);
      $produto_data = json_decode(get_post_meta(get_the_ID(), 'produto', true), true);
      
      if ($vendedor_id === get_user_by('id', $user_id)->user_login) {
        $vendas_como_vendedor++;
        if (isset($produto_data['preco'])) {
          $valor_total_vendas += floatval($produto_data['preco']);
        }
      }
      
      if ($comprador_id === get_user_by('id', $user_id)->user_login) {
        $compras_como_comprador++;
        if (isset($produto_data['preco'])) {
          $valor_total_compras += floatval($produto_data['preco']);
        }
      }
    }
  }
  wp_reset_postdata();

  return array(
    'total_transacoes' => $total_transacoes,
    'vendas_como_vendedor' => $vendas_como_vendedor,
    'compras_como_comprador' => $compras_como_comprador,
    'valor_total_vendas' => $valor_total_vendas,
    'valor_total_compras' => $valor_total_compras,
    'saldo' => $valor_total_vendas - $valor_total_compras
  );
}

function get_estatisticas_categorias($periodo) {
  global $wpdb;
  
  $date_condition = '';
  if ($periodo !== 'todos') {
    $date_query = get_date_query_by_period($periodo);
    $date_condition = $wpdb->prepare(
      "AND p.post_date >= %s",
      $date_query['after']
    );
  }

  $query = "
    SELECT 
      pm.meta_value as categoria,
      COUNT(*) as total_produtos,
      SUM(CASE WHEN pm2.meta_value = 'true' THEN 1 ELSE 0 END) as produtos_vendidos,
      AVG(CAST(pm3.meta_value AS DECIMAL(10,2))) as preco_medio
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'categorias'
    LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'vendido'
    LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'preco'
    WHERE p.post_type = 'produto' 
    AND p.post_status = 'publish'
    {$date_condition}
    GROUP BY pm.meta_value
    ORDER BY total_produtos DESC
  ";

  $resultados = $wpdb->get_results($query);
  
  $categorias = array();
  foreach ($resultados as $resultado) {
    $categorias[] = array(
      'categoria' => $resultado->categoria,
      'total_produtos' => intval($resultado->total_produtos),
      'produtos_vendidos' => intval($resultado->produtos_vendidos),
      'produtos_disponiveis' => intval($resultado->total_produtos) - intval($resultado->produtos_vendidos),
      'preco_medio' => floatval($resultado->preco_medio),
      'taxa_venda' => $resultado->total_produtos > 0 ? 
        round((intval($resultado->produtos_vendidos) / intval($resultado->total_produtos)) * 100, 2) : 0
    );
  }

  return $categorias;
}

function get_date_query_by_period($periodo) {
  $hoje = current_time('timestamp');
  
  switch ($periodo) {
    case '7dias':
      $dias = 7;
      break;
    case '30dias':
      $dias = 30;
      break;
    case '90dias':
      $dias = 90;
      break;
    case '6meses':
      $dias = 180;
      break;
    case '1ano':
      $dias = 365;
      break;
    default:
      $dias = 30;
  }

  return array(
    'after' => date('Y-m-d H:i:s', $hoje - ($dias * 24 * 60 * 60))
  );
}

function registrar_api_estatisticas_get() {
  register_rest_route('api/v1', '/estatisticas', array(
    array(
      'methods' => WP_REST_Server::READABLE,
      'callback' => 'api_estatisticas_get',
      'permission_callback' => function() {
        return is_user_logged_in();
      },
      'args' => array(
        'tipo' => array(
          'default' => 'geral',
          'enum' => array('geral', 'produtos', 'vendas', 'categorias')
        ),
        'periodo' => array(
          'default' => '30dias',
          'enum' => array('7dias', '30dias', '90dias', '6meses', '1ano', 'todos')
        )
      )
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_estatisticas_get');

?>
