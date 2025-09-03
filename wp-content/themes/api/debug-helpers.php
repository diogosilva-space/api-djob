<?php
/**
 * Debug helpers para desenvolvimento
 */

if (!function_exists('dd')) {
    /**
     * Dump and die - similar ao Laravel
     */
    function dd($var, $die = true) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}

if (!function_exists('log_debug')) {
    /**
     * Log personalizado para debug - funciona no Docker
     */
    function log_debug($message, $data = null) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            $log_message = date('[Y-m-d H:i:s] ') . $message;
            
            if ($data !== null) {
                $log_message .= ' - ' . print_r($data, true);
            }
            
            $log_message .= "\n";
            
            // Tentar usar o arquivo de log configurado do WordPress
            if (defined('WP_DEBUG_LOG_FILE')) {
                file_put_contents(WP_DEBUG_LOG_FILE, $log_message, FILE_APPEND | LOCK_EX);
            } else {
                // Fallback para o arquivo padrão
                $log_file = ABSPATH . 'wp-content/debug-logs/debug.log';
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            }
        }
    }
}

if (!function_exists('console_log')) {
    /**
     * Output para console do navegador
     */
    function console_log($data) {
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }
}

if (!function_exists('log_simple')) {
    /**
     * Log super simples - apenas uma linha
     */
    function log_simple($message) {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            $log_message = date('[Y-m-d H:i:s] ') . $message . "\n";
            
            // Tentar usar o arquivo de log configurado do WordPress
            if (defined('WP_DEBUG_LOG_FILE')) {
                file_put_contents(WP_DEBUG_LOG_FILE, $log_message, FILE_APPEND | LOCK_EX);
            } else {
                // Fallback para o arquivo padrão
                $log_file = ABSPATH . 'wp-content/debug-logs/debug.log';
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            }
        }
    }
}

// Debug de queries SQL
add_action('shutdown', function() {
    if (defined('SAVEQUERIES') && SAVEQUERIES) {
        global $wpdb;
        log_debug('Total queries: ' . count($wpdb->queries));
        
        foreach ($wpdb->queries as $query) {
            log_debug('Query: ' . $query[0] . ' - Time: ' . $query[1]);
        }
    }
});