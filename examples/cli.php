<?php
/**
 * cli.php - 示例CLI调用入口
 * 
 * 该文件展示了如何在命令行环境中使用深度研究功能。
 * 使用方法: php cli.php "你的研究问题" [研究深度]
 */

// 自动加载
require_once __DIR__ . '/../vendor/autoload.php';

// 导入类
use DeepResearch\DeepResearchPipeline;
use DeepResearch\SearchExecutor;
use DeepResearch\AnalysisExecutor;
use DeepResearch\Util\Logger;

// 检查命令行参数
if ($argc < 2) {
    echo "用法: php cli.php \"你的研究问题\" [研究深度]\n";
    echo "例如: php cli.php \"量子计算的最新进展\" 3\n";
    exit(1);
}

// 获取参数
$question = $argv[1];
$depth = isset($argv[2]) ? (int)$argv[2] : 2;

// 创建日志目录
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// 创建日志记录器
$logger = new Logger($logDir . '/cli_research.log');
$logger->info("开始深度研究: {$question}");

try {
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
    
    // 进度回调函数
    $progressCallback = function($message) {
        echo "[进度] " . $message . PHP_EOL;
    };
    
    // 执行深度研究
    echo "开始对 \"{$question}\" 进行深度研究 (深度: {$depth})...\n";
    $result = $pipeline->executeResearch($question, $question, $depth, $progressCallback);
    
    // 检查是否有错误
    if (isset($result['error'])) {
        echo "研究过程中出错: " . $result['error'] . "\n";
        exit(1);
    }
    
    // 输出研究结果
    echo "\n============ 深度研究结果 ============\n\n";
    echo $result['analysis'] . "\n";
    
    // 保存结果到文件
    $outputFile = 'research_result_' . date('Ymd_His') . '.txt';
    file_put_contents($outputFile, $result['analysis']);
    echo "\n结果已保存到文件: {$outputFile}\n";
    
    // 输出搜索历史
    echo "\n============ 搜索历史 ============\n\n";
    foreach ($result['search_history'] as $history) {
        echo "第 {$history['round']} 轮: \"{$history['query']}\"\n";
    }
    
    $logger->info("深度研究完成: {$question}");
    
} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
    $logger->error("深度研究异常: " . $e->getMessage());
    exit(1);
}
