<?php
/**
 * Frontend da API Sistema de E-commerce
 * 
 * Este arquivo serve como ponto de entrada para o tema
 * e redireciona para a documentação da API
 */

// Verificar se o WordPress está carregado
if (!defined('ABSPATH')) {
    // Se não estiver no WordPress, mostrar página básica
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>API Sistema de E-commerce - DJOB</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
            .container { max-width: 800px; margin: 0 auto; }
            .header { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
            .endpoint { background: #fff; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .method { display: inline-block; padding: 4px 8px; border-radius: 3px; color: white; font-weight: bold; font-size: 12px; }
            .get { background: #28a745; }
            .post { background: #007bff; }
            .put { background: #ffc107; color: #000; }
            .delete { background: #dc3545; }
            .auth { background: #6f42c1; }
            .public { background: #17a2b8; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🚀 API Sistema de E-commerce - DJOB</h1>
                <p>Sistema de API REST para gerenciamento de produtos, transações e usuários.</p>
            </div>

            <h2>📚 Endpoints Disponíveis</h2>
            
            <div class="endpoint">
                <span class="method public">GET</span>
                <strong>/wp-json/api/v1/documentacao</strong>
                <p>Documentação completa da API</p>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/wp-json/api/v1/usuario/login</strong>
                <p>Autenticação de usuário (JWT)</p>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <strong>/wp-json/api/v1/produtos</strong>
                <p>Listar produtos (público)</p>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/wp-json/api/v1/produto</strong>
                <p>Criar produto (autenticado)</p>
            </div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <strong>/wp-json/api/v1/estatisticas</strong>
                <p>Estatísticas do sistema (autenticado)</p>
            </div>

            <div class="endpoint">
                <span class="method post">POST</span>
                <strong>/wp-json/api/v1/transacao</strong>
                <p>Criar transação (autenticado)</p>
            </div>

            <h2>🔗 Links Úteis</h2>
            <ul>
                <li><a href="/wp-admin/">🔐 Painel Administrativo</a></li>
                <li><a href="/wp-json/">🌐 API WordPress</a></li>
                <li><a href="/wp-json/api/v1/documentacao">📖 Documentação da API</a></li>
            </ul>

            <h2>🔐 Autenticação</h2>
            <p>Para endpoints protegidos, inclua o header:</p>
            <code>Authorization: Bearer {seu_token_jwt}</code>
            
            <p>Obtenha o token fazendo login em <code>/wp-json/api/v1/usuario/login</code></p>
        </div>
    </body>
    </html>
    <?php
} else {
    // Se estiver no WordPress, usar o tema padrão
    get_header();
    
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            get_template_part('template-parts/content', get_post_type());
        }
    } else {
        get_template_part('template-parts/content', 'none');
    }
    
    get_footer();
}
?>