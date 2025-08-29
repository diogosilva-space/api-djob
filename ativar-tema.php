<?php
/**
 * Ativador de Tema para API DJOB
 * Este arquivo força a ativação do tema e carrega o functions.php
 */

// Verificar se o WordPress está carregado
if (!file_exists('wp-config.php')) {
    echo "❌ WordPress não encontrado neste diretório";
    exit;
}

// Carregar WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>🔧 Ativador de Tema - API DJOB</h1>";

// Verificar se o usuário está logado como administrador
if (!current_user_can('manage_options')) {
    echo "<p>❌ Você precisa estar logado como administrador para executar este script.</p>";
    echo "<p><a href='/wp-admin/'>🔐 Fazer Login</a></p>";
    exit;
}

// Verificar o tema atual
$current_theme = wp_get_theme();
echo "<h2>1. Tema Atual</h2>";
echo "📁 Nome: " . $current_theme->get('Name') . "<br>";
echo "📁 Diretório: " . $current_theme->get_stylesheet() . "<br>";
echo "📁 Template: " . $current_theme->get_template() . "<br>";

// Verificar se o tema 'api' está disponível
$themes = wp_get_themes();
$api_theme = null;
foreach ($themes as $theme_slug => $theme) {
    if ($theme->get('Name') === 'API DJOB' || $theme_slug === 'api') {
        $api_theme = $theme;
        break;
    }
}

if ($api_theme) {
    echo "<h2>2. Tema API Encontrado</h2>";
    echo "✅ Nome: " . $api_theme->get('Name') . "<br>";
    echo "✅ Diretório: " . $api_theme->get_stylesheet() . "<br>";
    
    // Ativar o tema se não estiver ativo
    if ($current_theme->get_stylesheet() !== $api_theme->get_stylesheet()) {
        echo "<h2>3. Ativando Tema API</h2>";
        switch_theme($api_theme->get_stylesheet());
        echo "✅ Tema ativado com sucesso!<br>";
        
        // Recarregar a página após ativação
        echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
        echo "<p>🔄 Recarregando página em 2 segundos...</p>";
    } else {
        echo "<h2>3. Tema Já Está Ativo</h2>";
        echo "✅ O tema API já está ativo<br>";
    }
} else {
    echo "<h2>2. Tema API Não Encontrado</h2>";
    echo "❌ O tema 'api' não foi encontrado<br>";
    echo "📁 Temas disponíveis:<br>";
    foreach ($themes as $theme_slug => $theme) {
        echo "   - " . $theme->get('Name') . " ($theme_slug)<br>";
    }
}

// Verificar se o functions.php está sendo carregado
echo "<h2>4. Verificação do Functions.php</h2>";
$template_dir = get_template_directory();
echo "📁 Diretório do tema: $template_dir<br>";

$functions_file = $template_dir . '/functions.php';
if (file_exists($functions_file)) {
    echo "✅ functions.php encontrado<br>";
    
    // Tentar incluir o functions.php manualmente
    echo "<h2>5. Carregando Functions.php Manualmente</h2>";
    try {
        include_once($functions_file);
        echo "✅ functions.php carregado manualmente<br>";
        
        // Verificar se as funções foram carregadas
        if (function_exists('get_produto_id_by_slug')) {
            echo "✅ Função get_produto_id_by_slug() carregada<br>";
        } else {
            echo "❌ Função get_produto_id_by_slug() não carregada<br>";
        }
        
        if (function_exists('registrar_middleware_autenticacao')) {
            echo "✅ Função registrar_middleware_autenticacao() carregada<br>";
        } else {
            echo "❌ Função registrar_middleware_autenticacao() não carregada<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Erro ao carregar functions.php: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ functions.php não encontrado<br>";
}

// Verificar se há erros no log
echo "<h2>6. Verificação de Logs</h2>";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    echo "✅ Arquivo de log encontrado: $log_file<br>";
    $log_content = file_get_contents($log_file);
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -5);
    echo "📝 Últimas 5 linhas do log:<br>";
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

echo "<hr>";
echo "<p><a href='/wp-admin/'>🔐 Painel Admin</a></p>";
echo "<p><a href='/wp-json/'>🌐 API WordPress</a></p>";
echo "<p><a href='/'>🏠 Início</a></p>";
echo "<p><a href='/teste-endpoints.php'>🧪 Testar Endpoints</a></p>";
?>
