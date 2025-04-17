<?php
/**
 * SearchResult.php - 搜索结果数据传输对象
 * 
 * 该类封装了搜索操作的结果数据，包括搜索结果列表、
 * 查询信息、时间戳、错误信息等。
 */

namespace DeepResearch\DTO;

class SearchResult
{
    /**
     * @var array 搜索结果列表
     */
    private $results;
    
    /**
     * @var string 搜索查询
     */
    private $query;
    
    /**
     * @var string 时间戳
     */
    private $timestamp;
    
    /**
     * @var string|null 错误信息
     */
    private $error;
    
    /**
     * @var string 搜索模式
     */
    private $mode;
    
    /**
     * 构造函数
     * 
     * @param array $results 搜索结果列表
     * @param string $query 搜索查询
     * @param string $timestamp 时间戳
     * @param string|null $error 错误信息
     * @param string $mode 搜索模式
     */
    public function __construct(
        array $results,
        string $query,
        string $timestamp,
        ?string $error = null,
        string $mode = 'standard'
    ) {
        $this->results = $results;
        $this->query = $query;
        $this->timestamp = $timestamp;
        $this->error = $error;
        $this->mode = $mode;
    }
    
    /**
     * 获取搜索结果列表
     * 
     * @return array 搜索结果列表
     */
    public function getResults(): array
    {
        return $this->results;
    }
    
    /**
     * 获取搜索查询
     * 
     * @return string 搜索查询
     */
    public function getQuery(): string
    {
        return $this->query;
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
     * 获取搜索模式
     * 
     * @return string 搜索模式
     */
    public function getMode(): string
    {
        return $this->mode;
    }
    
    /**
     * 检查是否为深度研究模式
     * 
     * @return bool 是否为深度研究模式
     */
    public function isDeepResearch(): bool
    {
        return $this->mode === 'deep_research';
    }
    
    /**
     * 转换为数组
     * 
     * @return array 数组表示
     */
    public function toArray(): array
    {
        return [
            'results' => $this->results,
            'query' => $this->query,
            'timestamp' => $this->timestamp,
            'error' => $this->error,
            'mode' => $this->mode
        ];
    }
}
