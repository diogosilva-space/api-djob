<?php
function registrar_cpt_produto() {
  register_post_type('produto', array(
    'label' => 'Produto',
    'description' => 'Produto com estrutura completa de cores, imagens e categorias',
    'public' => true,
    'show_ui' => true,
    'capability_type' => 'post',
    'rewrite' => array('slug' => 'produto', 'with_front' => true),
    'query_var' => true,
    'supports' => array('custom-fields', 'author', 'title', 'editor', 'thumbnail'),
    'publicly_queryable' => true,
    'show_in_rest' => true,
    'rest_base' => 'produtos',
    'menu_icon' => 'dashicons-products',
    'has_archive' => true
  ));
}
add_action('init', 'registrar_cpt_produto');

// Registrar metadados personalizados para o produto
function registrar_meta_produto() {
  register_post_meta('produto', 'referencia', array(
    'type' => 'string',
    'single' => true,
    'show_in_rest' => true,
    'sanitize_callback' => 'sanitize_text_field'
  ));
  
  register_post_meta('produto', 'descricao', array(
    'type' => 'string',
    'single' => true,
    'show_in_rest' => true,
    'sanitize_callback' => 'sanitize_textarea_field'
  ));
  
  register_post_meta('produto', 'cores', array(
    'type' => 'string',
    'single' => true,
    'show_in_rest' => true,
    'sanitize_callback' => 'sanitize_textarea_field'
  ));
  
  register_post_meta('produto', 'imagens', array(
    'type' => 'string',
    'single' => true,
    'show_in_rest' => true,
    'sanitize_callback' => 'sanitize_textarea_field'
  ));
  
  register_post_meta('produto', 'categorias', array(
    'type' => 'string',
    'single' => true,
    'show_in_rest' => true,
    'sanitize_callback' => 'sanitize_text_field'
  ));
  
  register_post_meta('produto', 'informacoes_adicionais', array(
    'type' => 'string',
    'single' => true,
    'show_in_rest' => true,
    'sanitize_callback' => 'sanitize_textarea_field'
  ));
  
  register_post_meta('produto', 'preco', array(
    'type' => 'number',
    'single' => true,
    'show_in_rest' => true,
    'sanitize_callback' => 'floatval'
  ));
  
  register_post_meta('produto', 'vendido', array(
    'type' => 'string',
    'single' => true,
    'show_in_rest' => true,
    'default' => 'false',
    'sanitize_callback' => 'sanitize_text_field'
  ));
  
  register_post_meta('produto', 'usuario_id', array(
    'type' => 'string',
    'single' => true,
    'show_in_rest' => true,
    'sanitize_callback' => 'sanitize_text_field'
  ));
}
add_action('init', 'registrar_meta_produto');

// Adicionar colunas personalizadas na listagem admin
function adicionar_colunas_produto($columns) {
  $new_columns = array();
  foreach ($columns as $key => $value) {
    $new_columns[$key] = $value;
    if ($key === 'title') {
      $new_columns['referencia'] = 'Referência';
      $new_columns['preco'] = 'Preço';
      $new_columns['categorias'] = 'Categorias';
      $new_columns['status_venda'] = 'Status';
    }
  }
  return $new_columns;
}
add_filter('manage_produto_posts_columns', 'adicionar_colunas_produto');

// Preencher colunas personalizadas
function preencher_colunas_produto($column, $post_id) {
  switch ($column) {
    case 'referencia':
      echo get_post_meta($post_id, 'referencia', true);
      break;
    case 'preco':
      $preco = get_post_meta($post_id, 'preco', true);
      echo $preco ? 'R$ ' . number_format($preco, 2, ',', '.') : '-';
      break;
    case 'categorias':
      echo get_post_meta($post_id, 'categorias', true);
      break;
    case 'status_venda':
      $vendido = get_post_meta($post_id, 'vendido', true);
      echo $vendido === 'true' ? '<span style="color: red;">Vendido</span>' : '<span style="color: green;">Disponível</span>';
      break;
  }
}
add_action('manage_produto_posts_custom_column', 'preencher_colunas_produto', 10, 2);

// Tornar colunas ordenáveis
function tornar_colunas_ordenaveis($columns) {
  $columns['referencia'] = 'referencia';
  $columns['preco'] = 'preco';
  $columns['categorias'] = 'categorias';
  return $columns;
}
add_filter('manage_edit-produto_sortable_columns', 'tornar_colunas_ordenaveis');

?>