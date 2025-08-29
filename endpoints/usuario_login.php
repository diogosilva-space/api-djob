<?php

function api_usuario_login($request) {
  // Validação dos campos obrigatórios
  if (empty($request['user_email']) || empty($request['user_pass'])) {
    return new WP_Error('campos_obrigatorios', 'Email e senha são obrigatórios.', array('status' => 400));
  }

  $user_email = sanitize_email($request['user_email']);
  $user_pass = $request['user_pass'];

  // Validar email
  if (!is_email($user_email)) {
    return new WP_Error('email_invalido', 'Email inválido.', array('status' => 400));
  }

  // Tentar autenticar o usuário
  $user = wp_authenticate($user_email, $user_pass);

  if (is_wp_error($user)) {
    return new WP_Error('credenciais_invalidas', 'Email ou senha incorretos.', array('status' => 401));
  }

  // Verificar se o usuário está ativo
  if (!$user->exists() || $user->ID === 0) {
    return new WP_Error('usuario_inexistente', 'Usuário não encontrado.', array('status' => 404));
  }

  // Atualizar último login
  update_user_meta($user->ID, 'ultimo_login', current_time('mysql'));

  // Buscar metadados do usuário
  $user_meta = get_user_meta($user->ID);

  // Preparar resposta de sucesso
  $response = array(
    'status' => 'success',
    'message' => 'Login realizado com sucesso',
    'usuario' => array(
      'id' => $user->ID,
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
      'avatar' => get_avatar_url($user->ID, array('size' => 150)),
      'ultimo_login' => current_time('mysql'),
      'preferencias' => array(
        'notificacoes_email' => isset($user_meta['notificacoes_email'][0]) ? $user_meta['notificacoes_email'][0] === 'true' : true,
        'notificacoes_push' => isset($user_meta['notificacoes_push'][0]) ? $user_meta['notificacoes_push'][0] === 'true' : true,
        'newsletter' => isset($user_meta['newsletter'][0]) ? $user_meta['newsletter'][0] === 'true' : false
      )
    ),
    'data_login' => current_time('c'),
    'token_info' => array(
      'note' => 'Use o token JWT do WordPress para autenticação em endpoints protegidos',
      'endpoint' => '/wp-json/jwt-auth/v1/token'
    )
  );

  return rest_ensure_response($response);
}

function registrar_api_usuario_login() {
  register_rest_route('api/v1', '/usuario/login', array(
    array(
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => 'api_usuario_login',
      'permission_callback' => '__return_true', // Qualquer um pode tentar fazer login
      'args' => array(
        'user_email' => array(
          'required' => true,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_email',
          'validate_callback' => function($param) {
            return is_email($param);
          }
        ),
        'user_pass' => array(
          'required' => true,
          'type' => 'string',
          'validate_callback' => function($param) {
            return strlen($param) > 0;
          }
        )
      )
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_usuario_login');

?>
