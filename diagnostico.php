<?php
/**
 * Diagnóstico Simples - API DJOB
 */

echo "<h1>🔍 Diagnóstico Simples - API DJOB</h1>";

// 1. Verificar se o WordPress está presente
echo "<h2>1. Verificação do WordPress</h2>";
if (file_exists('wp-config.php')) {
    echo "✅ wp-config.php encontrado<br>";
} else {
    echo "❌ wp-config.php não encontrado<br>";
    exit;
}

if (file_exists('wp-load.php')) {
    echo "✅ wp-load.php encontrado<br>";
} else {
    echo "❌ wp-load.php não encontrado<br>";
    exit;
}

// 2. Tentar carregar o WordPress
echo "<h2>2. Carregando WordPress</h2>";
try {
    require_once('wp-config.php');
    require_once('wp-load.php');
    echo "✅ WordPress carregado com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar WordPress: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Verificar se as funções básicas estão disponíveis
echo "<h2>3. Verificação de Funções Básicas</h2>";
if (function_exists('get_template_directory')) {
    echo "✅ get_template_directory() disponível<br>";
    $template_dir = get_template_directory();
    echo "📁 Diretório do tema: $template_dir<br>";
} else {
    echo "❌ get_template_directory() não disponível<br>";
    exit;
}

// 4. Verificar se o tema está ativo
echo "<h2>4. Verificação do Tema</h2>";
$current_theme = wp_get_theme();
echo "📁 Tema atual: " . $current_theme->get('Name') . "<br>";
echo "📁 Diretório: " . $current_theme->get_stylesheet() . "<br>";

// 5. Verificar se o functions.php existe
echo "<h2>5. Verificação do Functions.php</h2>";
$functions_file = $template_dir . '/functions.php';
if (file_exists($functions_file)) {
    echo "✅ functions.php encontrado em: $functions_file<br>";
    
    // Ler o conteúdo do functions.php
    $content = file_get_contents($functions_file);
    echo "📏 Tamanho do arquivo: " . strlen($content) . " bytes<br>";
    echo "📝 Primeiras 200 caracteres:<br>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 200)) . "</pre>";
    
} else {
    echo "❌ functions.php não encontrado em: $functions_file<br>";
    
    // Listar arquivos no diretório do tema
    echo "📁 Arquivos no diretório do tema:<br>";
    if (is_dir($template_dir)) {
        $files = scandir($template_dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "   - $file<br>";
            }
        }
    } else {
        echo "❌ Diretório do tema não é válido<br>";
    }
}

// 6. Verificar se há erros de sintaxe
echo "<h2>6. Verificação de Sintaxe</h2>";
if (file_exists($functions_file)) {
    $syntax_check = shell_exec("php -l " . escapeshellarg($functions_file) . " 2>&1");
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "✅ Sintaxe PHP válida<br>";
    } else {
        echo "❌ Erro de sintaxe PHP:<br>";
        echo "<pre>" . htmlspecialchars($syntax_check) . "</pre>";
    }
}

// 7. Verificar se o tema está sendo carregado
echo "<h2>7. Verificação de Carregamento do Tema</h2>";
if (function_exists('wp_get_theme')) {
    $theme = wp_get_theme();
    if ($theme->exists()) {
        echo "✅ Tema existe e está sendo carregado<br>";
        
        // Verificar se o functions.php está sendo incluído
        if (function_exists('get_template_directory')) {
            $template_dir = get_template_directory();
            $functions_file = $template_dir . '/functions.php';
            
            if (file_exists($functions_file)) {
                echo "✅ Arquivo functions.php existe no tema ativo<br>";
                
                // Tentar incluir manualmente
                echo "<h2>8. Inclusão Manual do Functions.php</h2>";
                try {
                    include_once($functions_file);
                    echo "✅ functions.php incluído manualmente<br>";
                    
                    // Verificar se as funções foram carregadas
                    if (function_exists('get_produto_id_by_slug')) {
                        echo "✅ Função get_produto_id_by_slug() carregada<br>";
                    } else {
                        echo "❌ Função get_produto_id_by_slug() não carregada<br>";
                    }
                    
                } catch (Exception $e) {
                    echo "❌ Erro ao incluir functions.php: " . $e->getMessage() . "<br>";
                }
            }
        }
    } else {
        echo "❌ Tema não existe ou não está sendo carregado<br>";
    }
}

echo "<hr>";
echo "<p><a href='/wp-admin/'>🔐 Painel Admin</a></p>";
echo "<p><a href='/wp-json/'>🌐 API WordPress</a></p>";
echo "<p><a href='/'>🏠 Início</a></p>";
?>
