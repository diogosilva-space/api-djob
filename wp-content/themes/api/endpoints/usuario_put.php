<?php

function api_usuario_put($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;

  if($user_id <= 0) {
    return new WP_Error('nao_autenticado', 'Usuário não autenticado.', array('status' => 401));
  }

  $response = array();
  $errors = array();

  // Atualizar dados básicos do usuário
  if (isset($request['display_name']) && !empty($request['display_name'])) {
    $display_name = sanitize_text_field($request['display_name']);
    wp_update_user(array(
      'ID' => $user_id,
      'display_name' => $display_name,
      'first_name' => $display_name
    ));
    $response['display_name'] = $display_name;
  }

  if (isset($request['first_name']) && !empty($request['first_name'])) {
    $first_name = sanitize_text_field($request['first_name']);
    wp_update_user(array(
      'ID' => $user_id,
      'first_name' => $first_name
    ));
    $response['first_name'] = $first_name;
  }

  if (isset($request['last_name']) && !empty($request['last_name'])) {
    $last_name = sanitize_text_field($request['last_name']);
    wp_update_user(array(
      'ID' => $user_id,
      'last_name' => $last_name
    ));
    $response['last_name'] = $last_name;
  }

  // Atualizar email (com validação de unicidade)
  if (isset($request['user_email']) && !empty($request['user_email'])) {
    $new_email = sanitize_email($request['user_email']);
    $current_email = $user->user_email;
    
    if ($new_email !== $current_email) {
      $email_exists = email_exists($new_email);
      if ($email_exists && $email_exists !== $user_id) {
        $errors[] = 'Email já está em uso por outro usuário.';
      } else {
        wp_update_user(array(
          'ID' => $user_id,
          'user_email' => $new_email
        ));
        $response['user_email'] = $new_email;
      }
    }
  }

  // Atualizar senha (se fornecida)
  if (isset($request['user_pass']) && !empty($request['user_pass'])) {
    $new_password = $request['user_pass'];
    
    // Validação básica de senha
    if (strlen($new_password) < 6) {
      $errors[] = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
      wp_update_user(array(
        'ID' => $user_id,
        'user_pass' => $new_password
      ));
      $response['password_updated'] = true;
    }
  }

  // Atualizar endereço
  $endereco_fields = array('cep', 'rua', 'numero', 'bairro', 'cidade', 'estado', 'complemento');
  foreach ($endereco_fields as $field) {
    if (isset($request['endereco'][$field])) {
      $value = sanitize_text_field($request['endereco'][$field]);
      update_user_meta($user_id, $field, $value);
      if (!isset($response['endereco'])) $response['endereco'] = array();
      $response['endereco'][$field] = $value;
    }
  }

  // Atualizar campos individuais de endereço (compatibilidade)
  if (isset($request['cep'])) {
    $cep = sanitize_text_field($request['cep']);
    update_user_meta($user_id, 'cep', $cep);
    $response['cep'] = $cep;
  }

  if (isset($request['rua'])) {
    $rua = sanitize_text_field($request['rua']);
    update_user_meta($user_id, 'rua', $rua);
    $response['rua'] = $rua;
  }

  if (isset($request['numero'])) {
    $numero = sanitize_text_field($request['numero']);
    update_user_meta($user_id, 'numero', $numero);
    $response['numero'] = $numero;
  }

  if (isset($request['bairro'])) {
    $bairro = sanitize_text_field($request['bairro']);
    update_user_meta($user_id, 'bairro', $bairro);
    $response['bairro'] = $bairro;
  }

  if (isset($request['cidade'])) {
    $cidade = sanitize_text_field($request['cidade']);
    update_user_meta($user_id, 'cidade', $cidade);
    $response['cidade'] = $cidade;
  }

  if (isset($request['estado'])) {
    $estado = sanitize_text_field($request['estado']);
    update_user_meta($user_id, 'estado', $estado);
    $response['estado'] = $estado;
  }

  // Atualizar outros campos
  if (isset($request['telefone'])) {
    $telefone = sanitize_text_field($request['telefone']);
    update_user_meta($user_id, 'telefone', $telefone);
    $response['telefone'] = $telefone;
  }

  if (isset($request['cpf_cnpj'])) {
    $cpf_cnpj = sanitize_text_field($request['cpf_cnpj']);
    update_user_meta($user_id, 'cpf_cnpj', $cpf_cnpj);
    $response['cpf_cnpj'] = $cpf_cnpj;
  }

  if (isset($request['data_nascimento'])) {
    $data_nascimento = sanitize_text_field($request['data_nascimento']);
    update_user_meta($user_id, 'data_nascimento', $data_nascimento);
    $response['data_nascimento'] = $data_nascimento;
  }

  if (isset($request['genero'])) {
    $genero = sanitize_text_field($request['genero']);
    update_user_meta($user_id, 'genero', $genero);
    $response['genero'] = $genero;
  }

  // Atualizar preferências
  if (isset($request['preferencias'])) {
    $preferencias = $request['preferencias'];
    
    if (isset($preferencias['notificacoes_email'])) {
      $value = $preferencias['notificacoes_email'] ? 'true' : 'false';
      update_user_meta($user_id, 'notificacoes_email', $value);
      $response['preferencias']['notificacoes_email'] = $preferencias['notificacoes_email'];
    }
    
    if (isset($preferencias['notificacoes_push'])) {
      $value = $preferencias['notificacoes_push'] ? 'true' : 'false';
      update_user_meta($user_id, 'notificacoes_push', $value);
      $response['preferencias']['notificacoes_push'] = $preferencias['notificacoes_push'];
    }
    
    if (isset($preferencias['newsletter'])) {
      $value = $preferencias['newsletter'] ? 'true' : 'false';
      update_user_meta($user_id, 'newsletter', $value);
      $response['preferencias']['newsletter'] = $preferencias['newsletter'];
    }
  }

  // Atualizar último login
  update_user_meta($user_id, 'ultimo_login', current_time('mysql'));

  // Verificar se houve erros
  if (!empty($errors)) {
    return new WP_Error('validacao', 'Erros de validação encontrados.', array(
      'status' => 400,
      'errors' => $errors
    ));
  }

  // Preparar resposta final
  $final_response = array(
    'status' => 'success',
    'message' => 'Usuário atualizado com sucesso',
    'usuario_id' => $user_id,
    'campos_atualizados' => $response,
    'data_atualizacao' => current_time('c')
  );

  return rest_ensure_response($final_response);
}

function registrar_api_usuario_put() {
  register_rest_route('api/v1', '/usuario', array(
    array(
      'methods' => WP_REST_Server::EDITABLE,
      'callback' => 'api_usuario_put',
      'permission_callback' => '__return_true',
      'args' => array(
        'display_name' => array(
          'required' => false,
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
        'user_email' => array(
          'required' => false,
          'type' => 'string',
          'sanitize_callback' => 'sanitize_email',
          'validate_callback' => function($param) {
            return is_email($param);
          }
        ),
        'user_pass' => array(
          'required' => false,
          'type' => 'string',
          'validate_callback' => function($param) {
            return strlen($param) >= 6;
          }
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
        ),
        'preferencias' => array(
          'required' => false,
          'type' => 'object',
          'properties' => array(
            'notificacoes_email' => array('type' => 'boolean'),
            'notificacoes_push' => array('type' => 'boolean'),
            'newsletter' => array('type' => 'boolean')
          )
        )
      )
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_usuario_put');

?>