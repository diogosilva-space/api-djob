<?php

function api_usuario_post($request) {
  // Validação dos campos obrigatórios
  $campos_obrigatorios = ['user_email', 'user_pass', 'display_name'];
  foreach ($campos_obrigatorios as $campo) {
    if (empty($request[$campo])) {
      return new WP_Error('campo_obrigatorio', "Campo '$campo' é obrigatório.", array('status' => 400));
    }
  }

  // Sanitização e validação dos dados
  $user_email = sanitize_email($request['user_email']);
  $user_pass = $request['user_pass'];
  $display_name = sanitize_text_field($request['display_name']);
  $first_name = isset($request['first_name']) ? sanitize_text_field($request['first_name']) : $display_name;
  $last_name = isset($request['last_name']) ? sanitize_text_field($request['last_name']) : '';

  // Validações adicionais
  if (!is_email($user_email)) {
    return new WP_Error('email_invalido', 'Email inválido.', array('status' => 400));
  }

  if (strlen($user_pass) < 6) {
    return new WP_Error('senha_fraca', 'A senha deve ter pelo menos 6 caracteres.', array('status' => 400));
  }

  if (strlen($display_name) < 2) {
    return new WP_Error('nome_invalido', 'O nome deve ter pelo menos 2 caracteres.', array('status' => 400));
  }

  // Verificar se o usuário já existe
  $user_exists = username_exists($user_email);
  $email_exists = email_exists($user_email);

  if ($user_exists || $email_exists) {
    return new WP_Error('usuario_existente', 'Email já está cadastrado.', array('status' => 409));
  }

  // Criar o usuário
  $user_id = wp_create_user($user_email, $user_pass, $user_email);

  if (is_wp_error($user_id)) {
    return $user_id;
  }

  // Atualizar dados básicos
  wp_update_user(array(
    'ID' => $user_id,
    'display_name' => $display_name,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'role' => 'subscriber'
  ));

  // Atualizar metadados de endereço
  $endereco_fields = array('cep', 'rua', 'numero', 'bairro', 'cidade', 'estado', 'complemento');
  foreach ($endereco_fields as $field) {
    if (isset($request['endereco'][$field]) && !empty($request['endereco'][$field])) {
      $value = sanitize_text_field($request['endereco'][$field]);
      update_user_meta($user_id, $field, $value);
    }
  }

  // Atualizar campos individuais de endereço (compatibilidade)
  if (isset($request['cep'])) {
    update_user_meta($user_id, 'cep', sanitize_text_field($request['cep']));
  }
  if (isset($request['rua'])) {
    update_user_meta($user_id, 'rua', sanitize_text_field($request['rua']));
  }
  if (isset($request['numero'])) {
    update_user_meta($user_id, 'numero', sanitize_text_field($request['numero']));
  }
  if (isset($request['bairro'])) {
    update_user_meta($user_id, 'bairro', sanitize_text_field($request['bairro']));
  }
  if (isset($request['cidade'])) {
    update_user_meta($user_id, 'cidade', sanitize_text_field($request['cidade']));
  }
  if (isset($request['estado'])) {
    update_user_meta($user_id, 'estado', sanitize_text_field($request['estado']));
  }

  // Atualizar outros campos opcionais
  if (isset($request['telefone'])) {
    update_user_meta($user_id, 'telefone', sanitize_text_field($request['telefone']));
  }

  if (isset($request['cpf_cnpj'])) {
    update_user_meta($user_id, 'cpf_cnpj', sanitize_text_field($request['cpf_cnpj']));
  }

  if (isset($request['data_nascimento'])) {
    update_user_meta($user_id, 'data_nascimento', sanitize_text_field($request['data_nascimento']));
  }

  if (isset($request['genero'])) {
    $genero = sanitize_text_field($request['genero']);
    if (in_array($genero, array('masculino', 'feminino', 'outro', 'prefiro_nao_informar'))) {
      update_user_meta($user_id, 'genero', $genero);
    }
  }

  // Configurar preferências padrão
  update_user_meta($user_id, 'notificacoes_email', 'true');
  update_user_meta($user_id, 'notificacoes_push', 'true');
  update_user_meta($user_id, 'newsletter', 'false');
  update_user_meta($user_id, 'ultimo_login', current_time('mysql'));

  // Buscar dados do usuário criado
  $user = get_user_by('id', $user_id);
  $user_meta = get_user_meta($user_id);

  // Preparar resposta
  $response = array(
    'status' => 'success',
    'message' => 'Usuário criado com sucesso',
    'usuario' => array(
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
      'preferencias' => array(
        'notificacoes_email' => true,
        'notificacoes_push' => true,
        'newsletter' => false
      )
    ),
    'data_criacao' => current_time('c')
  );

  return rest_ensure_response($response);
}

function registrar_api_usuario_post() {
  register_rest_route('api/v1', '/usuario', array(
    array(
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => 'api_usuario_post',
      'permission_callback' => '__return_true', // Qualquer um pode criar usuário
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
            return strlen($param) >= 6;
          }
        ),
        'display_name' => array(
          'required' => true,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field',
          'validate_callback' => function($param) {
            return strlen($param) >= 2;
          }
        ),
        'first_name' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'last_name' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'endereco' => array(
          'required' => false,
          'type' => 'object',
          'properties' => array(
            'cep' => array('type' => 'string'),
            'rua' => array('type' => 'string'),
            'numero' => array('type' => 'string'),
            'bairro' => array('type' => 'string'),
            'cidade' => array('type' => 'string'),
            'estado' => array('type' => 'string'),
            'complemento' => array('type' => 'string')
          )
        ),
        'telefone' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'cpf_cnpj' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'data_nascimento' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ),
        'genero' => array(
          'required' => false,
          'type' => 'string',
          'enum' => array('masculino', 'feminino', 'outro', 'prefiro_nao_informar')
        )
      )
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_usuario_post');

?>