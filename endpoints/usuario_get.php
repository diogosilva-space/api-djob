<?php

function api_usuario_get($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;

  if($user_id <= 0) {
    return new WP_Error('nao_autenticado', 'Usuário não autenticado.', array('status' => 401));
  }

  // Buscar metadados do usuário
  $user_meta = get_user_meta($user_id);
  
  // Preparar resposta estruturada
  $response = array(
    'id' => $user_id,
    'user_login' => $user->user_login,
    'display_name' => $user->display_name,
    'first_name' => $user->first_name,
    'last_name' => $user->last_name,
    'user_email' => $user->user_email,
    'user_registered' => $user->user_registered,
    'role' => $user->roles[0] ?? 'subscriber',
    'status' => 'active',
    'endereco' => array(
      'cep' => isset($user_meta['cep'][0]) ? $user_meta['cep'][0] : '',
      'rua' => isset($user_meta['rua'][0]) ? $user_meta['rua'][0] : '',
      'numero' => isset($user_meta['numero'][0]) ? $user_meta['numero'][0] : '',
      'bairro' => isset($user_meta['bairro'][0]) ? $user_meta['bairro'][0] : '',
      'cidade' => isset($user_meta['cidade'][0]) ? $user_meta['cidade'][0] : '',
      'estado' => isset($user_meta['estado'][0]) ? $user_meta['estado'][0] : '',
      'complemento' => isset($user_meta['complemento'][0]) ? $user_meta['complemento'][0] : ''
    ),
    'telefone' => isset($user_meta['telefone'][0]) ? $user_meta['telefone'][0] : '',
    'cpf_cnpj' => isset($user_meta['cpf_cnpj'][0]) ? $user_meta['cpf_cnpj'][0] : '',
    'data_nascimento' => isset($user_meta['data_nascimento'][0]) ? $user_meta['data_nascimento'][0] : '',
    'genero' => isset($user_meta['genero'][0]) ? $user_meta['genero'][0] : '',
    'avatar' => get_avatar_url($user_id, array('size' => 150)),
    'ultimo_login' => isset($user_meta['ultimo_login'][0]) ? $user_meta['ultimo_login'][0] : '',
    'preferencias' => array(
      'notificacoes_email' => isset($user_meta['notificacoes_email'][0]) ? $user_meta['notificacoes_email'][0] === 'true' : true,
      'notificacoes_push' => isset($user_meta['notificacoes_push'][0]) ? $user_meta['notificacoes_push'][0] === 'true' : true,
      'newsletter' => isset($user_meta['newsletter'][0]) ? $user_meta['newsletter'][0] === 'true' : false
    ),
    'estatisticas' => array(
      'total_produtos' => count_user_posts($user_id, 'produto'),
      'produtos_vendidos' => get_produtos_vendidos_count($user_id),
      'total_transacoes' => get_transacoes_count($user_id),
      'data_cadastro' => $user->user_registered
    )
  );

  return rest_ensure_response($response);
}

// Função auxiliar para contar produtos vendidos
function get_produtos_vendidos_count($user_id) {
  $args = array(
    'post_type' => 'produto',
    'post_status' => 'publish',
    'author' => $user_id,
    'meta_query' => array(
      array(
        'key' => 'vendido',
        'value' => 'true',
        'compare' => '='
      )
    ),
    'posts_per_page' => -1,
    'fields' => 'ids'
  );
  
  $query = new WP_Query($args);
  return $query->found_posts;
}

// Função auxiliar para contar transações
function get_transacoes_count($user_id) {
  $user_login = get_user_by('id', $user_id)->user_login;
  
  $args = array(
    'post_type' => 'transacao',
    'post_status' => 'publish',
    'meta_query' => array(
      'relation' => 'OR',
      array(
        'key' => 'vendedor_id',
        'value' => $user_login,
        'compare' => '='
      ),
      array(
        'key' => 'comprador_id',
        'value' => $user_login,
        'compare' => '='
      )
    ),
    'posts_per_page' => -1,
    'fields' => 'ids'
  );
  
  $query = new WP_Query($args);
  return $query->found_posts;
}

function registrar_api_usuario_get() {
  register_rest_route('api/v1', '/usuario', array(
    array(
      'methods' => WP_REST_Server::READABLE,
      'callback' => 'api_usuario_get',
      'permission_callback' => function() {
        return is_user_logged_in();
      },
      'args' => array(
        'campos' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field',
          'description' => 'Campos específicos a retornar (separados por vírgula)'
        )
      )
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_usuario_get');

?>