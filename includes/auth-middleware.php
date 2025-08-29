<?php
/**
 * Middleware de Autenticação para API REST
 * Corrige problemas de autenticação nos endpoints
 */

// Verificar se o plugin JWT Authentication está ativo
if (!function_exists('jwt_auth_verify_token')) {
    // Fallback para autenticação básica se JWT não estiver disponível
    function jwt_auth_verify_token($token) {
        // Implementação básica de verificação de token
        // Em produção, use um plugin JWT adequado
        // Por enquanto, retorna false para forçar uso do plugin JWT
        return false;
    }
}

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
    
    // Verificar token JWT
    $usuario = jwt_auth_verify_token($token);
    
    if (!$usuario || is_wp_error($usuario)) {
        return false;
    }
    
    return $usuario;
}

/**
 * Middleware de autenticação para endpoints protegidos
 */
function middleware_autenticacao($request) {
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
        
        // Se não é uma requisição da API, permitir acesso
        if (empty($route) || strpos($route, '/api/v1/') !== 0) {
            return $result;
        }
        
        // Lista de endpoints que precisam de autenticação
        $endpoints_protegidos = array(
            '/api/v1/produto',
            '/api/v1/estatisticas',
            '/api/v1/transacao'
        );
        
        // Permitir acesso livre a endpoints públicos
        $endpoints_publicos = array(
            '/api/v1/documentacao',
            '/api/v1/usuario/login',
            '/api/v1/usuario',
            '/api/v1/produtos'
        );
        
        foreach ($endpoints_publicos as $endpoint) {
            if (strpos($route, $endpoint) === 0) {
                return $result; // Permitir acesso livre
            }
        }
        
        // Verificar autenticação apenas para endpoints protegidos
        foreach ($endpoints_protegidos as $endpoint) {
            if (strpos($route, $endpoint) === 0) {
                $auth_result = middleware_autenticacao($request);
                if (is_wp_error($auth_result)) {
                    return $auth_result;
                }
                break;
            }
        }
        
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
