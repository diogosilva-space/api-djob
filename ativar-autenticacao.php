<?php
/**
 * Arquivo para ativar autenticação da API
 * Execute este arquivo quando quiser ativar o sistema de autenticação
 */

// Verificar se o WordPress está carregado
if (!defined('ABSPATH')) {
    echo '<h1>Ativação de Autenticação</h1>';
    echo '<p>Este arquivo deve ser executado no contexto do WordPress.</p>';
    echo '<p>Para ativar a autenticação, adicione ao functions.php:</p>';
    echo '<pre>add_action("init", "ativar_middleware_autenticacao");</pre>';
    exit;
}

// Ativar o middleware de autenticação
if (function_exists('ativar_middleware_autenticacao')) {
    ativar_middleware_autenticacao();
    echo '<h1>✅ Autenticação Ativada!</h1>';
    echo '<p>O sistema de autenticação foi ativado com sucesso.</p>';
    echo '<p>Agora os endpoints protegidos requerem autenticação JWT.</p>';
} else {
    echo '<h1>❌ Erro na Ativação</h1>';
    echo '<p>O middleware de autenticação não foi encontrado.</p>';
    echo '<p>Verifique se o arquivo includes/auth-middleware.php existe.</p>';
}

echo '<hr>';
echo '<p><a href="/wp-admin/">← Voltar ao Painel Admin</a></p>';
echo '<p><a href="/wp-json/api/v1/documentacao">Ver Documentação da API</a></p>';
?>
