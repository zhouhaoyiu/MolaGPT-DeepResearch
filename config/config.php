<?php
/**
 * config.php - API密钥和URL配置
 * 
 * 该文件包含深度研究功能所需的API密钥和URL配置。
 * 实际使用时，建议将此文件替换为环境变量或其他安全的配置方式。
 */

return [
    // 搜索API配置
    'search' => [
        'provider' => 'exa',
        'api_key' => 'your-exa-api-key', // 替换为实际的Exa API密钥
        'api_url' => 'https://api.exa.ai/search'
    ],
    
    // 分析API配置 - DashScope
    'analysis_dashscope' => [
        'provider' => 'dashscope',
        'api_key' => 'your-dashscope-api-key', // 替换为实际的DashScope API密钥
        'api_url' => 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions',
        'model' => 'qwen-plus-latest'
    ],
    
    // 分析API配置 - OpenAI
    'analysis_openai' => [
        'provider' => 'openai',
        'api_key' => 'your-openai-api-key', // 替换为实际的OpenAI API密钥
        'api_url' => 'https://api.openai.com/v1/chat/completions',
        'model' => 'gpt-4o'
    ],
    
    // 日志配置
    'logging' => [
        'enabled' => true,
        'log_file' => __DIR__ . '/../logs/deep_research.log'
    ],
    
    // 深度研究配置
    'research' => [
        'default_depth' => 3,
        'max_depth' => 10
    ]
];
