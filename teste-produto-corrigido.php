<?php
/**
 * Teste do Endpoint de Produtos CORRIGIDO
 * Verifica se o erro 500 foi resolvido
 */

// Configurações - CREDENCIAIS REAIS DO USUÁRIO DJOB
$api_base_url = 'https://api.djob.com.br/wp-json';
$username = 'diogosilva@djob.com.br';
$password = 'QYuV037Num9rzUkb';

echo "=== 🧪 TESTE DO ENDPOINT DE PRODUTOS CORRIGIDO ===\n\n";
echo "👤 Usuário: {$username}\n";
echo "🌐 Servidor: {$api_base_url}\n";
echo "📅 Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "🎯 Objetivo: Verificar se o erro 500 foi resolvido\n\n";

// Função para testar endpoints
function test_endpoint($url, $method = 'GET', $data = null, $token = null, $description = '') {
    $ch = curl_init();
    
    $headers = array(
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: Produto-Corrigido-Test/1.0'
    );
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return array(
            'status' => 'ERROR',
            'body' => 'cURL Error: ' . $error,
            'description' => $description
        );
    }
    
    return array(
        'status' => $http_code,
        'body' => $response,
        'description' => $description
    );
}

// Função para exibir resultado do teste
function display_test_result($test_name, $response, $expected_status = null) {
    echo "🔍 {$test_name}:\n";
    echo "   Status: {$response['status']}\n";
    
    if ($expected_status && $response['status'] === $expected_status) {
        echo "   ✅ RESULTADO: CORRETO (Status {$expected_status})\n";
    } elseif ($expected_status) {
        echo "   ❌ RESULTADO: INCORRETO (Esperado: {$expected_status}, Obtido: {$response['status']})\n";
    } else {
        echo "   📊 RESPOSTA: " . substr($response['body'], 0, 150) . "...\n";
    }
    
    // Mostrar resposta completa para status de erro
    if ($response['status'] !== 200 && $response['status'] !== 201) {
        echo "   📋 RESPOSTA COMPLETA: " . $response['body'] . "\n";
    }
    
    echo "\n";
}

// ===== TESTE 1: OBTER TOKEN JWT =====
echo "📋 TESTE 1: Obtenção de Token JWT\n";
echo str_repeat("=", 50) . "\n";

$jwt_data = array(
    'username' => $username,
    'password' => $password
);
$response = test_endpoint($api_base_url . '/jwt-auth/v1/token', 'POST', $jwt_data, null, 'Obter Token JWT');
display_test_result('Obter Token JWT', $response, 200);

// Extrair token
$jwt_response = json_decode($response['body'], true);
$token = null;

if ($response['status'] === 200 && isset($jwt_response['token'])) {
    $token = $jwt_response['token'];
    echo "🎉 TOKEN JWT OBTIDO COM SUCESSO!\n";
    echo "Token: " . substr($token, 0, 50) . "...\n";
    echo "Usuário: " . ($jwt_response['user_display_name'] ?? $username) . "\n\n";
} else {
    echo "⚠️  AVISO: Não foi possível obter token JWT.\n";
    echo "📋 Resposta: " . $response['body'] . "\n\n";
    exit("❌ Não é possível continuar sem token JWT válido\n");
}

// ===== TESTE 2: TESTE CRÍTICO - CRIAÇÃO DE PRODUTO =====
echo "🚨 TESTE 2: Criação de Produto (CRÍTICO)\n";
echo str_repeat("=", 60) . "\n";

// 2.1 Testar criação de produto com dados completos
echo "🔍 Teste 2.1: Criação de produto com dados completos\n";
$produto_completo = array(
    'referencia' => 'TESTE_CORRIGIDO_' . time(),
    'nome' => 'Produto Teste Corrigido ' . date('H:i:s'),
    'descricao' => 'Descrição do produto teste após correção do erro 500',
    'preco' => 99.99,
    'categorias' => 'Teste'
);
$response = test_endpoint($api_base_url . '/api/v1/produto', 'POST', $produto_completo, $token, 'Criar Produto - Dados Completos');
display_test_result('Criar Produto - Dados Completos', $response, 201);

// ===== TESTE 3: VERIFICAÇÃO DE RESPOSTA =====
echo "📋 TESTE 3: Verificação da Resposta\n";
echo str_repeat("-", 50) . "\n";

if ($response['status'] === 201) {
    echo "🎉 PRODUTO CRIADO COM SUCESSO!\n";
    
    // Decodificar resposta para verificar estrutura
    $response_data = json_decode($response['body'], true);
    if ($response_data) {
        echo "📋 Estrutura da resposta:\n";
        echo "   - ID: " . ($response_data['id'] ?? 'N/A') . "\n";
        echo "   - Slug: " . ($response_data['slug'] ?? 'N/A') . "\n";
        echo "   - Status: " . ($response_data['status'] ?? 'N/A') . "\n";
        echo "   - Message: " . ($response_data['message'] ?? 'N/A') . "\n";
    }
    
    echo "\n✅ O erro 500 foi RESOLVIDO com sucesso!\n";
    
} elseif ($response['status'] === 500) {
    echo "🚨 ERRO 500 AINDA PERSISTE!\n";
    echo "   A correção não foi aplicada ou há outro problema.\n";
    
} else {
    echo "⚠️  STATUS INESPERADO: {$response['status']}\n";
    echo "   Resposta: " . $response['body'] . "\n";
}

echo "\n";

// ===== TESTE 4: VERIFICAÇÃO DE SEGURANÇA =====
echo "📋 TESTE 4: Verificação de Segurança\n";
echo str_repeat("-", 50) . "\n";

// 4.1 Testar criação sem token (deve retornar 401)
echo "🔍 Teste 4.1: Tentativa de criação sem token\n";
$response = test_endpoint($api_base_url . '/api/v1/produto', 'POST', $produto_completo, null, 'Criar Produto sem Token (deve retornar 401)');
display_test_result('Criar Produto sem Token', $response, 401);

// 4.2 Testar com dados inválidos (deve retornar 400)
echo "🔍 Teste 4.2: Tentativa com dados inválidos\n";
$produto_invalido = array(
    'nome' => 'Produto Inválido'  // Faltando campos obrigatórios
);
$response = test_endpoint($api_base_url . '/api/v1/produto', 'POST', $produto_invalido, $token, 'Criar Produto com Dados Inválidos (deve retornar 400)');
display_test_result('Criar Produto com Dados Inválidos', $response, 400);

// ===== RESUMO FINAL =====
echo "📊 RESUMO FINAL - ENDPOINT DE PRODUTOS CORRIGIDO\n";
echo str_repeat("=", 70) . "\n";

echo "🎯 OBJETIVO: Verificar se erro 500 foi resolvido\n";
echo "🔍 STATUS: " . (isset($response['status']) ? "Status {$response['status']}" : "Não testado") . "\n\n";

if (isset($response['status']) && $response['status'] === 201) {
    echo "🎉 SUCESSO TOTAL!\n";
    echo "   ✅ Erro 500 RESOLVIDO\n";
    echo "   ✅ Endpoint de produtos funcionando perfeitamente\n";
    echo "   ✅ Sistema JWT funcionando perfeitamente\n";
    echo "   🚀 SUA API DJOB ESTÁ 100% FUNCIONAL!\n";
    
} elseif (isset($response['status']) && $response['status'] === 500) {
    echo "🚨 PROBLEMA PERSISTE:\n";
    echo "   ❌ Erro 500 ainda ocorre\n";
    echo "   🔍 Verificar se correção foi aplicada no servidor\n";
    echo "   📋 Verificar logs do WordPress\n";
    
} else {
    echo "⚠️  STATUS INESPERADO:\n";
    echo "   🔍 Verificar resposta completa\n";
    echo "   📋 Analisar logs do WordPress\n";
}

echo "\n=== 🎉 FIM DO TESTE - ENDPOINT DE PRODUTOS CORRIGIDO ===\n";
?>
