<?php
/**
 * AnalysisResult.php - 分析结果数据传输对象
 * 
 * 该类封装了分析操作的结果数据，包括分析内容、
 * 时间戳、错误信息等。
 */

namespace DeepResearch\DTO;

class AnalysisResult
{
    /**
     * @var string 分析内容
     */
    private $analysis;
    
    /**
     * @var string 时间戳
     */
    private $timestamp;
    
    /**
     * @var string|null 错误信息
     */
    private $error;
    
    /**
     * 构造函数
     * 
     * @param string $analysis 分析内容
     * @param string $timestamp 时间戳
     * @param string|null $error 错误信息
     */
    public function __construct(
        string $analysis,
        string $timestamp,
        ?string $error = null
    ) {
        $this->analysis = $analysis;
        $this->timestamp = $timestamp;
        $this->error = $error;
    }
    
    /**
     * 获取分析内容
     * 
     * @return string 分析内容
     */
    public function getAnalysis(): string
    {
        return $this->analysis;
    }
    
    /**
     * 获取时间戳
     * 
     * @return string 时间戳
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }
    
    /**
     * 获取错误信息
     * 
     * @return string|null 错误信息
     */
    public function getError(): ?string
    {
        return $this->error;
    }
    
    /**
     * 检查是否有错误
     * 
     * @return bool 是否有错误
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }
    
    /**
     * 提取下一轮搜索关键词
     * 
     * @param string $defaultQuery 默认查询
     * @return string 下一轮搜索关键词
     */
    public function extractNextQuery(string $defaultQuery): string
    {
        if (preg_match('/<NEXT_QUERY>(.*?)<\/NEXT_QUERY>/s', $this->analysis, $matches)) {
            $suggestedQuery = trim($matches[1]);
            if (!empty($suggestedQuery)) {
                return $suggestedQuery;
            }
        }
        
        return $defaultQuery;
    }
    
    /**
     * 转换为数组
     * 
     * @return array 数组表示
     */
    public function toArray(): array
    {
        return [
            'analysis' => $this->analysis,
            'timestamp' => $this->timestamp,
            'error' => $this->error
        ];
    }
}
