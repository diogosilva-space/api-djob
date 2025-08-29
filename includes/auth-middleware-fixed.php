<?php
/**
 * Middleware de Autenticação CORRIGIDO para API REST
 * Versão que funciona corretamente com o plugin JWT
 */

/**
 * Verifica se o usuário está autenticado via JWT
 * VERSÃO CORRIGIDA que funciona com o plugin JWT
 */
function verificar_autenticacao_jwt($request) {
    $auth_header = $request->get_header('Authorization');
    
    if (!$auth_header) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('JWT: Header Authorization não encontrado');
        }
        return false;
    }
    
    // Extrair token do header Authorization
    if (strpos($auth_header, 'Bearer ') === 0) {
        $token = substr($auth_header, 7);
    } else {
        $token = $auth_header;
    }
    
    if (empty($token)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('JWT: Token vazio após extração');
        }
        return false;
    }
    
    // Debug: Log do token recebido
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('JWT Token recebido: ' . substr($token, 0, 50) . '...');
    }
    
    // MÉTODO PRINCIPAL: Verificação direta do token JWT
    $usuario = verificar_token_jwt_direto($token);
    if ($usuario) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('JWT Token validado com sucesso via verificação direta');
        }
        return $usuario;
    }
    
    // MÉTODO FALLBACK: Tentar usar o plugin JWT se disponível
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
    
    // Se não conseguiu autenticar, retornar false
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('JWT Token falhou em todos os métodos de validação');
    }
    return false;
}

/**
 * Verifica token JWT de forma direta (MÉTODO PRINCIPAL)
 * VERSÃO CORRIGIDA que funciona com tokens do plugin JWT
 */
function verificar_token_jwt_direto($token) {
    try {
        // Decodificar o token JWT
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT: Token não tem 3 partes');
            }
            return false;
        }
        
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        
        if (!$payload) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT: Payload não pode ser decodificado');
            }
            return false;
        }
        
        // Debug: Log do payload
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('JWT Payload: ' . print_r($payload, true));
        }
        
        // Verificar se o token não expirou
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT Token expirado');
            }
            return false;
        }
        
        // Verificar se o usuário existe - MÚLTIPLAS FORMATOS SUPORTADOS
        $user_id = null;
        
        // Formato 1: payload.data.user.id (plugin JWT padrão)
        if (isset($payload['data']['user']['id'])) {
            $user_id = $payload['data']['user']['id'];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT: User ID encontrado em data.user.id: ' . $user_id);
            }
        }
        // Formato 2: payload.user_id
        elseif (isset($payload['user_id'])) {
            $user_id = $payload['user_id'];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT: User ID encontrado em user_id: ' . $user_id);
            }
        }
        // Formato 3: payload.data.id
        elseif (isset($payload['data']['id'])) {
            $user_id = $payload['data']['id'];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT: User ID encontrado em data.id: ' . $user_id);
            }
        }
        // Formato 4: payload.sub (padrão JWT)
        elseif (isset($payload['sub'])) {
            $user_id = $payload['sub'];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT: User ID encontrado em sub: ' . $user_id);
            }
        }
        // Formato 5: payload.iss (se contiver ID)
        elseif (isset($payload['iss']) && is_numeric($payload['iss'])) {
            $user_id = $payload['iss'];
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT: User ID encontrado em iss: ' . $user_id);
            }
        }
        
        if (!$user_id) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT: User ID não encontrado no payload');
                error_log('JWT: Payload completo: ' . print_r($payload, true));
            }
            return false;
        }
        
        // Buscar usuário no WordPress
        $user = get_user_by('ID', $user_id);
        if ($user && $user->exists()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('JWT: Usuário encontrado: ' . $user->user_login . ' (ID: ' . $user_id . ')');
            }
            return $user;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('JWT: Usuário não encontrado para ID: ' . $user_id);
        }
        
        return false;
        
    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('JWT: Erro na verificação direta: ' . $e->getMessage());
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
        
        // Se não é uma requisição da API, permitir acesso
        if (empty($route) || strpos($route, '/api/v1/') !== 0) {
            return $result;
        }
        
        // Lista de endpoints que precisam de autenticação OBRIGATÓRIA
        $endpoints_protegidos = array(
            '/api/v1/produto',           // POST - Criar produto
            '/api/v1/estatisticas',      // GET - Estatísticas
            '/api/v1/transacao',         // POST/GET - Transações
            '/api/v1/usuario'            // GET/PUT - Dados do usuário
        );
        
        // Lista de endpoints PÚBLICOS (sem autenticação)
        $endpoints_publicos = array(
            '/api/v1/documentacao',      // GET - Documentação
            '/api/v1/usuario/login',     // POST - Login
            '/api/v1/usuario'            // POST - Criar usuário
        );
        
        // Lista de endpoints que podem ser acessados com ou sem autenticação
        $endpoints_opcionais = array(
            '/api/v1/produtos'           // GET - Listar produtos (público)
        );
        
        // Permitir acesso livre a endpoints públicos
        foreach ($endpoints_publicos as $endpoint) {
            if (strpos($route, $endpoint) === 0) {
                return $result; // Permitir acesso livre
            }
        }
        
        // Permitir acesso livre a endpoints opcionais
        foreach ($endpoints_opcionais as $endpoint) {
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
        error_log('Route: ' . $request->get_route());
        error_log('Method: ' . $request->get_method());
        error_log('Headers: ' . print_r($request->get_headers(), true));
        error_log('Authorization: ' . $request->get_header('Authorization'));
        error_log('=======================');
    }
}
?>
