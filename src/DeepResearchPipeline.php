<?php
/**
 * DeepResearchPipeline.php - 多轮搜索与分析主类
 * 
 * 该类是深度研究功能的主要入口点，负责协调搜索和分析过程，
 * 实现多轮迭代式深入研究功能。
 */

namespace DeepResearch;

use DeepResearch\DTO\SearchResult;
use DeepResearch\DTO\AnalysisResult;
use DeepResearch\Util\Logger;

class DeepResearchPipeline
{
    /**
     * @var SearchExecutor 搜索执行器实例
     */
    private $searchExecutor;
    
    /**
     * @var AnalysisExecutor 分析执行器实例
     */
    private $analysisExecutor;
    
    /**
     * @var Logger 日志记录器实例
     */
    private $logger;
    
    /**
     * @var array 搜索历史记录
     */
    private $searchHistory = [];
    
    /**
     * @var array 分析结果集合
     */
    private $analysisCollection = [];
    
    /**
     * 构造函数
     * 
     * @param SearchExecutor $searchExecutor 搜索执行器实例
     * @param AnalysisExecutor $analysisExecutor 分析执行器实例
     * @param Logger|null $logger 可选的日志记录器实例
     */
    public function __construct(
        SearchExecutor $searchExecutor, 
        AnalysisExecutor $analysisExecutor, 
        ?Logger $logger = null
    ) {
        $this->searchExecutor = $searchExecutor;
        $this->analysisExecutor = $analysisExecutor;
        $this->logger = $logger ?? new Logger();
    }
    
    /**
     * 执行深度研究
     * 
     * @param string $initialQuery 初始查询关键词
     * @param string $originalQuestion 原始问题（上下文）
     * @param int $depth 研究深度（迭代次数）
     * @param callable|null $progressCallback 进度回调函数
     * @return array 包含所有分析结果的数组
     */
    public function executeResearch(
        string $initialQuery, 
        string $originalQuestion, 
        int $depth = 2, 
        ?callable $progressCallback = null
    ): array {
        // 规范化深度参数
        $maxDepth = 10; // 设置最大深度以防滥用
        $depth = min(max(2, (int)$depth), $maxDepth);
        
        $this->log("开始深度研究. 查询: '{$initialQuery}', 深度: {$depth}");
        $this->sendProgress($progressCallback, "准备开始深入研究，可能需要几分钟时间。");
        
        // 重置状态
        $this->searchHistory = [];
        $this->analysisCollection = [];
        
        $currentQuery = $initialQuery;
        $lastAnalysis = null;
        
        // 记录初始搜索查询
        $this->searchHistory[] = [
            'round' => 1,
            'query' => $initialQuery
        ];
        
        // 执行多轮迭代研究
        for ($i = 1; $i <= $depth; $i++) {
            $this->sendProgress($progressCallback, "深入研究第 {$i} 阶段。搜索: {$currentQuery}");
            $this->log("深度研究第 {$i} 轮: 搜索 '{$currentQuery}'");
            
            // 步骤1: 执行搜索
            $searchResult = $this->searchExecutor->executeSearch($currentQuery, true);
            
            if ($searchResult->hasError()) {
                $errorMsg = "深度研究第 {$i} 轮搜索失败: " . $searchResult->getError();
                $this->log($errorMsg);
                $this->sendProgress($progressCallback, $errorMsg);
                return ['error' => $errorMsg];
            }
            
            $resultCount = count($searchResult->getResults());
            $this->log("深度研究第 {$i} 轮: 找到 {$resultCount} 条结果");
            $this->sendProgress($progressCallback, "找到 {$resultCount} 条结果，准备进行专家分析。");
            
            // 步骤2: 分析结果
            $analysisResult = $this->analysisExecutor->analyzeResults(
                $searchResult,
                $originalQuestion,
                $lastAnalysis,
                $progressCallback,
                $i,         // 当前是第几轮
                $depth,     // 总共有多少轮
                $this->searchHistory // 搜索历史
            );
            
            if ($analysisResult->hasError()) {
                $errorMsg = "深度研究第 {$i} 轮分析失败: " . $analysisResult->getError();
                $this->log($errorMsg);
                $this->sendProgress($progressCallback, $errorMsg);
                return ['error' => $errorMsg];
            }
            
            $currentAnalysis = $analysisResult->getAnalysis();
            $this->analysisCollection[] = [
                'round' => $i,
                'analysis' => $currentAnalysis,
                'timestamp' => $analysisResult->getTimestamp()
            ];
            
            $lastAnalysis = $currentAnalysis; // 更新下一轮的上下文
            
            $this->log("深度研究第 {$i} 轮: 分析完成");
            $this->sendProgress($progressCallback, "深入研究第 {$i} 阶段已完成。");
            
            // 如果不是最后一轮，提取下一轮搜索关键词
            if ($i < $depth) {
                $nextQuery = $initialQuery; // 默认使用初始查询
                if (preg_match('/<NEXT_QUERY>(.*?)<\/NEXT_QUERY>/s', $currentAnalysis, $matches)) {
                    $suggestedQuery = trim($matches[1]);
                    if (!empty($suggestedQuery)) {
                        $nextQuery = $suggestedQuery;
                        $this->log("提取到下一轮搜索关键词: " . $nextQuery);
                        $this->sendProgress($progressCallback, "专家意见: 进一步研究 " . $nextQuery);
                    }
                } else {
                    $this->log("未找到下一轮搜索关键词标记，将继续使用初始查询");
                }
                $currentQuery = $nextQuery; // 更新下一轮搜索关键词
                
                // 将新的搜索关键词添加到历史记录中
                $this->searchHistory[] = [
                    'round' => $i + 1,
                    'query' => $nextQuery
                ];
            }
        }
        
        // 整合所有分析结果
        $finalAnalysis = $this->consolidateAnalysis();
        $this->sendProgress($progressCallback, "深入研究完成，已整合所有分析结果。");
        
        return [
            'analysis' => $finalAnalysis,
            'rounds' => $this->analysisCollection,
            'search_history' => $this->searchHistory
        ];
    }
    
    /**
     * 整合所有分析结果
     * 
     * @return string 整合后的分析结果
     */
    private function consolidateAnalysis(): string
    {
        $finalAnalysis = "深度研究总结 - 基于 " . count($this->analysisCollection) . " 轮迭代分析\n\n";
        
        foreach ($this->analysisCollection as $index => $analysis) {
            $finalAnalysis .= "第 " . ($index + 1) . " 轮分析:\n";
            $finalAnalysis .= $analysis['analysis'] . "\n\n";
        }
        
        return $finalAnalysis;
    }
    
    /**
     * 记录日志
     * 
     * @param string $message 日志消息
     */
    private function log(string $message): void
    {
        if ($this->logger) {
            $this->logger->log($message);
        }
    }
    
    /**
     * 发送进度更新
     * 
     * @param callable|null $callback 进度回调函数
     * @param string $message 进度消息
     */
    private function sendProgress(?callable $callback, string $message): void
    {
        if ($callback !== null && is_callable($callback)) {
            call_user_func($callback, $message);
        }
    }
    
    /**
     * 获取搜索历史
     * 
     * @return array 搜索历史记录
     */
    public function getSearchHistory(): array
    {
        return $this->searchHistory;
    }
    
    /**
     * 获取分析结果集合
     * 
     * @return array 分析结果集合
     */
    public function getAnalysisCollection(): array
    {
        return $this->analysisCollection;
    }
}
