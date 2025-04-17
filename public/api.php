<?php
/**
 * api.php - 示例HTTP API接口入口
 * 
 * 该文件展示了如何创建一个HTTP API接口来提供深度研究功能。
 * 使用方法: 通过POST请求访问此文件，提供query和depth参数。
 */

// 设置响应头
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理OPTIONS请求（用于CORS预检）
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => '只支持POST请求方法']);
    exit;
}

// 自动加载
require_once __DIR__ . '/../vendor/autoload.php';

// 导入类
use DeepResearch\DeepResearchPipeline;
use DeepResearch\SearchExecutor;
use DeepResearch\AnalysisExecutor;
use DeepResearch\Util\Logger;

try {
    // 获取请求体
    $requestBody = file_get_contents('php://input');
    $requestData = json_decode($requestBody, true);
    
    // 验证请求参数
    if (!isset($requestData['query']) || empty($requestData['query'])) {
        http_response_code(400);
        echo json_encode(['error' => '缺少必要参数: query']);
        exit;
    }
    
    $query = $requestData['query'];
    $depth = isset($requestData['depth']) ? (int)$requestData['depth'] : 2;
    
    // 创建日志目录
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // 创建日志记录器
    $logger = new Logger($logDir . '/api_research.log');
    $logger->info("API请求: {$query}, 深度: {$depth}");
    
    // 加载配置
    $config = require_once __DIR__ . '/../config/config.php';
    
    // 创建搜索执行器
    $searchExecutor = new SearchExecutor(
        $config['search']['api_key'],
        $logger,
        $config['search']['api_url']
    );
    
    // 创建分析执行器 (使用DashScope)
    $analysisExecutor = new AnalysisExecutor(
        $config['analysis_dashscope']['api_key'],
        $config['analysis_dashscope']['api_url'],
        $config['analysis_dashscope']['provider'],
        $config['analysis_dashscope']['model'],
        $logger
    );
    
    // 创建深度研究管道
    $pipeline = new DeepResearchPipeline($searchExecutor, $analysisExecutor, $logger);
    
    // 进度存储
    $progressMessages = [];
    
    // 进度回调函数
    $progressCallback = function($message) use (&$progressMessages) {
        $progressMessages[] = $message;
    };
    
    // 执行深度研究
    $result = $pipeline->executeResearch($query, $query, $depth, $progressCallback);
    
    // 检查是否有错误
    if (isset($result['error'])) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $result['error'],
            'progress' => $progressMessages
        ]);
        exit;
    }
    
    // 返回成功结果
    echo json_encode([
        'success' => true,
        'query' => $query,
        'depth' => $depth,
        'analysis' => $result['analysis'],
        'search_history' => $result['search_history'],
        'progress' => $progressMessages,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    $logger->info("API请求完成: {$query}");
    
} catch (Exception $e) {
    $logger->error("API异常: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => '服务器内部错误: ' . $e->getMessage()
    ]);
}
