<?php
/**
 * Logger.php - 日志工具类
 * 
 * 该类提供日志记录功能，支持将日志写入文件或自定义处理。
 */

namespace DeepResearch\Util;

class Logger
{
    /**
     * @var string 日志文件路径
     */
    private $logFile;
    
    /**
     * @var callable|null 自定义日志处理函数
     */
    private $logHandler;
    
    /**
     * @var bool 是否启用日志
     */
    private $enabled;
    
    /**
     * 构造函数
     * 
     * @param string|null $logFile 日志文件路径
     * @param callable|null $logHandler 自定义日志处理函数
     * @param bool $enabled 是否启用日志
     */
    public function __construct(
        ?string $logFile = null,
        ?callable $logHandler = null,
        bool $enabled = true
    ) {
        $this->logFile = $logFile;
        $this->logHandler = $logHandler;
        $this->enabled = $enabled;
    }
    
    /**
     * 记录日志
     * 
     * @param string $message 日志消息
     * @param string $level 日志级别
     * @return bool 是否成功记录
     */
    public function log(string $message, string $level = 'INFO'): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] [{$level}] {$message}";
        
        // 如果有自定义处理函数，则调用
        if ($this->logHandler !== null && is_callable($this->logHandler)) {
            call_user_func($this->logHandler, $formattedMessage);
            return true;
        }
        
        // 如果有日志文件，则写入文件
        if ($this->logFile !== null) {
            return (bool)file_put_contents($this->logFile, $formattedMessage . PHP_EOL, FILE_APPEND);
        }
        
        return false;
    }
    
    /**
     * 记录信息级别日志
     * 
     * @param string $message 日志消息
     * @return bool 是否成功记录
     */
    public function info(string $message): bool
    {
        return $this->log($message, 'INFO');
    }
    
    /**
     * 记录警告级别日志
     * 
     * @param string $message 日志消息
     * @return bool 是否成功记录
     */
    public function warning(string $message): bool
    {
        return $this->log($message, 'WARNING');
    }
    
    /**
     * 记录错误级别日志
     * 
     * @param string $message 日志消息
     * @return bool 是否成功记录
     */
    public function error(string $message): bool
    {
        return $this->log($message, 'ERROR');
    }
    
    /**
     * 记录调试级别日志
     * 
     * @param string $message 日志消息
     * @return bool 是否成功记录
     */
    public function debug(string $message): bool
    {
        return $this->log($message, 'DEBUG');
    }
    
    /**
     * 设置日志文件路径
     * 
     * @param string $logFile 日志文件路径
     * @return self 当前实例
     */
    public function setLogFile(string $logFile): self
    {
        $this->logFile = $logFile;
        return $this;
    }
    
    /**
     * 设置自定义日志处理函数
     * 
     * @param callable $logHandler 自定义日志处理函数
     * @return self 当前实例
     */
    public function setLogHandler(callable $logHandler): self
    {
        $this->logHandler = $logHandler;
        return $this;
    }
    
    /**
     * 启用日志
     * 
     * @return self 当前实例
     */
    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }
    
    /**
     * 禁用日志
     * 
     * @return self 当前实例
     */
    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }
}
