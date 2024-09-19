<?php

$router = new \WPLTK\App\Services\Router('wpltk');

$permissions = ['install_plugins'];

/**
 * Snippets Management Routes
 */
$router->get('snippets', ['\WPLTK\App\Http\Controllers\SnippetsController', 'getSnippets'], $permissions)
    ->get('snippets/{id}', ['\WPLTK\App\Http\Controllers\SnippetsController', 'getSnippetDetails'], $permissions)
    ->post('snippets', ['\WPLTK\App\Http\Controllers\SnippetsController', 'createOrUpdateSnippet'], $permissions)
    ->patch('snippets', ['\WPLTK\App\Http\Controllers\SnippetsController', 'updateAllSnippetsStatus'], $permissions)
    ->patch('snippets/{id}', ['\WPLTK\App\Http\Controllers\SnippetsController', 'updateSnippetStatus'], $permissions)
    ->delete('snippets/{id}', ['\WPLTK\App\Http\Controllers\SnippetsController', 'deleteSnippet'], $permissions)
    ->delete('snippets', ['\WPLTK\App\Http\Controllers\SnippetsController', 'deleteAllSnippets'], $permissions);

/**
 * Settings Management Routes
 */
$router->get('settings', ['\WPLTK\App\Http\Controllers\SettingsController', 'getSettings'], $permissions)
    ->patch('settings', ['\WPLTK\App\Http\Controllers\SettingsController', 'saveSettings'], $permissions);

/**
 * Webhook Management Routes (Optional)
 */
$router->post('webhooks', ['\WPLTK\App\Http\Controllers\WebhookController', 'registerWebhook'], $permissions)
    ->delete('webhooks/{id}', ['\WPLTK\App\Http\Controllers\WebhookController', 'unregisterWebhook'], $permissions);

/**
 * Analytics & Logs Routes (Optional)
 */
$router->get('analytics/snippets', ['\WPLTK\App\Http\Controllers\AnalyticsController', 'getSnippetAnalytics'], $permissions)
    ->get('logs', ['\WPLTK\App\Http\Controllers\LogsController', 'getLogs'], $permissions);

/**
 * Authentication & User Management Routes (Optional)
 */
$router->post('auth/login', ['\WPLTK\App\Http\Controllers\AuthController', 'login'], $permissions)
    ->post('auth/logout', ['\WPLTK\App\Http\Controllers\AuthController', 'logout'], $permissions);
