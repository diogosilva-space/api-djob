<?php
/**
 * Exemplo de como usar as funções de log
 * 
 * Este arquivo mostra as diferentes formas de fazer logs
 * no sistema de API
 */

// Exemplo 1: Log simples (recomendado para logs rápidos)
log_simple('Usuário fez login');
log_simple('Produto criado com sucesso');
log_simple('Erro na validação dos dados');

// Exemplo 2: Log com dados adicionais
log_debug('Dados do usuário', $user_data);
log_debug('Resposta da API', $api_response);
log_debug('Erro de validação', $validation_errors);

// Exemplo 3: Log condicional (só aparece se WP_DEBUG estiver ativo)
if (defined('WP_DEBUG') && WP_DEBUG) {
    log_simple('Debug: Processando requisição');
}

// Exemplo 4: Log com informações de contexto
log_simple('API: GET /usuario - IP: ' . $_SERVER['REMOTE_ADDR']);
log_simple('API: POST /produto - User ID: ' . get_current_user_id());

// Exemplo 5: Log de erro
try {
    // código que pode dar erro
    $result = some_function();
} catch (Exception $e) {
    log_simple('ERRO: ' . $e->getMessage());
}

// Exemplo 6: Log de performance
$start_time = microtime(true);
// ... código ...
$end_time = microtime(true);
log_simple('Performance: ' . round(($end_time - $start_time) * 1000, 2) . 'ms');

// Exemplo 7: Log de debug com dump (para desenvolvimento)
if (defined('WP_DEBUG') && WP_DEBUG) {
    dd($variable); // Para e mostra a variável
    dd($array, false); // Mostra mas não para a execução
}

// Exemplo 8: Log para console do navegador (apenas para frontend)
console_log('Dados enviados para o console do navegador');
?>
