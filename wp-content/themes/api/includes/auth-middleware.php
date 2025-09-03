<?php
/**
 * Middleware de Autenticação para API REST
 * Corrige problemas de autenticação nos endpoints
 */

/**
 * Verifica se o usuário está autenticado via JWT
 */
function verificar_autenticacao_jwt($request) {
    $auth_header = $request->get_header('Authorization');
    
    if (!$auth_header) {
        return false;
    }
    
    // Extrair token do header Authorization
    if (strpos($auth_header, 'Bearer ') === 0) {
        $token = substr($auth_header, 7);
    } else {
        $token = $auth_header;
    }
    
    if (empty($token)) {
        return false;
    }
    
    // Debug: Log do token recebido
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('JWT Token recebido: ' . substr($token, 0, 50) . '...');
    }
    
    // Método 1: Plugin JWT padrão do WordPress (PRINCIPAL)
    if (function_exists('jwt_auth_verify_token')) {
        $usuario = jwt_auth_verify_token($token);
        if ($usuario && !is_wp_error($usuario)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT Token validado com sucesso via plugin oficial');
            }
            return $usuario;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT Token falhou na validação via plugin oficial: ' . print_r($usuario, true));
            }
        }
    } else {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Função jwt_auth_verify_token não encontrada');
        }
    }
    
    // Método 2: Verificação direta do token JWT (FALLBACK)
    $usuario = verificar_token_jwt_direto($token);
    if ($usuario) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('JWT Token validado via verificação direta');
        }
        return $usuario;
    }
    
    // Se não conseguiu autenticar, retornar false
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('JWT Token falhou em todos os métodos de validação');
    }
    return false;
}

/**
 * Verifica token JWT de forma direta (fallback)
 */
function verificar_token_jwt_direto($token) {
    try {
        // Decodificar o token JWT
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        
        if (!$payload) {
            return false;
        }
        
        // Verificar se o token não expirou
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT Token expirado');
            }
            return false;
        }
        
        // Verificar se o usuário existe
        $user_id = null;
        if (isset($payload['data']['user']['id'])) {
            $user_id = $payload['data']['user']['id'];
        } elseif (isset($payload['user_id'])) {
            $user_id = $payload['user_id'];
        } elseif (isset($payload['data']['id'])) {
            $user_id = $payload['data']['id'];
        } elseif (isset($payload['sub'])) {
            $user_id = $payload['sub'];
        }
        
        if (!$user_id) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT Token não contém user_id válido');
            }
            return false;
        }
        
        $user = get_user_by('ID', $user_id);
        if ($user && $user->exists()) {
            return $user;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Usuário não encontrado para ID: ' . $user_id);
        }
        
        return false;
    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Erro na verificação direta do JWT: ' . $e->getMessage());
        }
        return false;
    }
}

/**
 * Middleware de autenticação para endpoints protegidos
 */
function middleware_autenticacao($request) {
    // Debug: Log da requisição
    if (defined('WP_DEBUG') && WP_DEBUG) {
        debug_autenticacao($request);
    }
    
    $usuario = verificar_autenticacao_jwt($request);
    
    if (!$usuario) {
        return new WP_Error(
            'nao_autenticado',
            'Token de autorização inválido ou ausente',
            array('status' => 401)
        );
    }
    
    // Armazenar usuário autenticado na request para uso posterior
    $request->set_param('usuario_autenticado', $usuario);
    
    return true;
}

/**
 * Obtém o usuário autenticado da request
 */
function obter_usuario_autenticado($request) {
    $usuario = $request->get_param('usuario_autenticado');
    
    if (!$usuario) {
        // Fallback: tentar verificar novamente
        $usuario = verificar_autenticacao_jwt($request);
    }
    
    return $usuario;
}

/**
 * Verifica se o usuário tem permissão para acessar o recurso
 */
function verificar_permissao_usuario($request, $recurso_id = null) {
    $usuario = obter_usuario_autenticado($request);
    
    if (!$usuario) {
        return false;
    }
    
    // Se não há recurso específico, apenas verificar se está autenticado
    if (!$recurso_id) {
        return true;
    }
    
    // Verificar se o usuário é o proprietário do recurso
    $post = get_post($recurso_id);
    
    if (!$post) {
        return false;
    }
    
    // Verificar se o usuário é o autor do post
    if ($post->post_author == $usuario->ID) {
        return true;
    }
    
    // Verificar se o usuário é administrador
    if (current_user_can('manage_options')) {
        return true;
    }
    
    return false;
}

/**
 * Função helper para obter ID do usuário autenticado
 */
function obter_id_usuario_autenticado($request) {
    $usuario = obter_usuario_autenticado($request);
    return $usuario ? $usuario->ID : 0;
}

/**
 * Função helper para obter login do usuário autenticado
 */
function obter_login_usuario_autenticado($request) {
    $usuario = obter_usuario_autenticado($request);
    return $usuario ? $usuario->user_login : '';
}

/**
 * Registra o middleware de autenticação
 */
function registrar_middleware_autenticacao() {
    // Adicionar filtros para endpoints que precisam de autenticação
    add_filter('rest_pre_dispatch', function($result, $server, $request) {
        // Verificar se é uma requisição da API REST
        $route = $request->get_route();
        $method = $request->get_method(); // GET, POST, PUT, DELETE, etc.
        
        // Se não é uma requisição da API, permitir acesso
        if (empty($route) || strpos($route, '/api/v1/') !== 0) {
            return $result;
        }
        
        // Configuração por ROTA + MÉTODO HTTP
        $configuracao_rotas = array(
            // Rota: /api/v1/produto
            '/api/v1/produto' => array(
                'GET' => 'publico',      // Buscar produto único - PÚBLICO
                'POST' => 'protegido',   // Criar produto - PROTEGIDO
                'PUT' => 'protegido',    // Atualizar produto - PROTEGIDO
                'DELETE' => 'protegido'  // Deletar produto - PROTEGIDO
            ),
            
            // Rota: /api/v1/produtos
            '/api/v1/produtos' => array(
                'GET' => 'publico'       // Listar produtos - PÚBLICO
            ),
            
            // Rota: /api/v1/usuario
            '/api/v1/usuario' => array(
                'GET' => 'protegido',    // Dados do usuário - PROTEGIDO
                'POST' => 'publico',     // Criar usuário - PÚBLICO
                'PUT' => 'protegido'     // Atualizar usuário - PROTEGIDO
            ),
            
            // Rota: /api/v1/usuario/login
            '/api/v1/usuario/login' => array(
                'POST' => 'publico'      // Login - PÚBLICO
            ),
            
            // Rota: /api/v1/documentacao
            '/api/v1/documentacao' => array(
                'GET' => 'publico'       // Documentação - PÚBLICO
            ),
            
            // Rota: /api/v1/estatisticas
            '/api/v1/estatisticas' => array(
                'GET' => 'protegido'     // Estatísticas - PROTEGIDO
            ),
            
            // Rota: /api/v1/transacao
            '/api/v1/transacao' => array(
                'GET' => 'protegido',    // Listar transações - PROTEGIDO
                'POST' => 'protegido'    // Criar transação - PROTEGIDO
            )
        );
        
        // Verificar se a rota está configurada
        foreach ($configuracao_rotas as $rota_config => $metodos) {
            if (strpos($route, $rota_config) === 0) {
                // Verificar se o método está configurado para esta rota
                if (isset($metodos[$method])) {
                    $tipo_acesso = $metodos[$method];
                    
                    if ($tipo_acesso === 'publico') {
                        // Permitir acesso livre
                        return $result;
                    } elseif ($tipo_acesso === 'protegido') {
                        // Verificar autenticação
                        $auth_result = middleware_autenticacao($request);
                        if (is_wp_error($auth_result)) {
                            return $auth_result;
                        }
                        return $result;
                    }
                }
                break;
            }
        }
        
        // Se não encontrou configuração específica, permitir acesso
        return $result;
    }, 10, 3);
}

// Registrar o middleware quando a API REST for inicializada
add_action('rest_api_init', 'registrar_middleware_autenticacao');

// Função para ativar o middleware manualmente se necessário
function ativar_middleware_autenticacao() {
    add_action('rest_api_init', 'registrar_middleware_autenticacao');
}

/**
 * Função para debug de autenticação
 */
function debug_autenticacao($request) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('=== DEBUG AUTENTICAÇÃO ===');
        error_log('Headers: ' . print_r($request->get_headers(), true));
        error_log('Authorization: ' . $request->get_header('Authorization'));
        error_log('Usuario: ' . print_r(obter_usuario_autenticado($request), true));
        error_log('=======================');
    }
}
?>
