<?php
/**
 * Arquivo de teste para verificar endpoints da API
 * Acesse: https://api.djob.com.br/teste-endpoints.php
 */

// Verificar se o WordPress está carregado
if (!file_exists('wp-config.php')) {
    echo "❌ WordPress não encontrado neste diretório";
    exit;
}

// Carregar WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>🧪 Teste de Endpoints da API</h1>";

// Verificar se o functions.php foi carregado
echo "<h2>1. Verificação do Functions.php</h2>";
if (function_exists('get_template_directory')) {
    echo "✅ get_template_directory() está disponível<br>";
    $template_dir = get_template_directory();
    echo "📁 Diretório do tema: $template_dir<br>";
} else {
    echo "❌ get_template_directory() não está disponível<br>";
}

// Verificar se os custom post types foram registrados
echo "<h2>2. Verificação de Custom Post Types</h2>";
$post_types = get_post_types([], 'objects');
foreach ($post_types as $post_type => $post_type_object) {
    if (in_array($post_type, ['produto', 'transacao'])) {
        echo "✅ Custom Post Type '$post_type' registrado<br>";
        echo "   - Labels: " . $post_type_object->labels->name . "<br>";
    }
}

// Verificar se os endpoints da API estão registrados
echo "<h2>3. Verificação de Endpoints da API</h2>";
$rest_server = rest_get_server();
$routes = $rest_server->get_routes();

$api_routes = [];
foreach ($routes as $route => $handlers) {
    if (strpos($route, '/api/v1/') === 0) {
        $api_routes[] = $route;
    }
}

if (empty($api_routes)) {
    echo "❌ Nenhum endpoint /api/v1/ encontrado<br>";
    echo "🔍 Verificando se o rest_api_init foi executado...<br>";
    
    // Verificar se as funções dos endpoints existem
    $endpoint_files = [
        'documentacao_get.php',
        'usuario_login.php',
        'produto_post.php',
        'estatisticas_get.php'
    ];
    
    foreach ($endpoint_files as $file) {
        $file_path = $template_dir . "/endpoints/$file";
        if (file_exists($file_path)) {
            echo "✅ Arquivo $file existe<br>";
        } else {
            echo "❌ Arquivo $file não encontrado em $file_path<br>";
        }
    }
} else {
    echo "✅ Endpoints da API encontrados:<br>";
    foreach ($api_routes as $route) {
        echo "   - $route<br>";
    }
}

// Verificar se o middleware de autenticação está ativo
echo "<h2>4. Verificação do Middleware de Autenticação</h2>";
if (function_exists('registrar_middleware_autenticacao')) {
    echo "✅ Função registrar_middleware_autenticacao() existe<br>";
} else {
    echo "❌ Função registrar_middleware_autenticacao() não existe<br>";
}

if (function_exists('ativar_middleware_autenticacao')) {
    echo "✅ Função ativar_middleware_autenticacao() existe<br>";
} else {
    echo "❌ Função ativar_middleware_autenticacao() não existe<br>";
}

// Verificar se o JWT está funcionando
echo "<h2>5. Verificação do JWT</h2>";
if (function_exists('jwt_auth_verify_token')) {
    echo "✅ Função jwt_auth_verify_token() existe<br>";
} else {
    echo "❌ Função jwt_auth_verify_token() não existe<br>";
}

// Verificar se há erros no log
echo "<h2>6. Verificação de Logs</h2>";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    echo "✅ Arquivo de log encontrado: $log_file<br>";
    $log_content = file_get_contents($log_file);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -10);
    echo "📝 Últimas 10 linhas do log:<br>";
    echo "<pre>";
    foreach ($recent_lines as $line) {
        if (!empty(trim($line))) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "❌ Arquivo de log não encontrado<br>";
}

echo "<h2>7. Ações Recomendadas</h2>";
echo "<ul>";
echo "<li>Verificar se o tema está ativo no WordPress</li>";
echo "<li>Verificar se não há erros de sintaxe no functions.php</li>";
echo "<li>Verificar se os arquivos estão sendo incluídos corretamente</li>";
echo "<li>Verificar se o rest_api_init está sendo executado</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='/wp-admin/'>🔐 Acessar Painel Admin</a></p>";
echo "<p><a href='/wp-json/'>🌐 API WordPress</a></p>";
echo "<p><a href='/'>🏠 Voltar ao Início</a></p>";
?>
