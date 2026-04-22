<?php
namespace app\event;

use think\facade\Route;
use think\facade\Event;
use think\facade\Db;
/*
 * AppInit事件类
 * @author wyh
 * @time 2022-05-26
 * */
class  AppInit
{
    public function handle()
    {
        # 注册应用命名空间
        if (config('idcsmart.root_namespace')){
            \app\common\lib\Loader::addNamespace(config('idcsmart.root_namespace'));
            \app\common\lib\Loader::register(); # 实现自动加载
        }
        # 支付接口路由
        Route::any('gateway/[:_plugin]/[:_controller]/[:_action]', "\\app\\event\\controller\\GatewayController@index");
        # 验证码接口路由 wyh 20240223 增加跨域
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        Route::any('captcha/[:_plugin]/[:_controller]/[:_action]', "\\app\\event\\controller\\CaptchaController@index")->allowCrossDomain([
            'Access-Control-Allow-Origin'        => $origin,
            'Access-Control-Allow-Credentials'   => 'true',
            'Access-Control-Max-Age'             => 600,
        ]);
        # 实名认证接口路由
        Route::any('certification/[:_plugin]/[:_controller]/[:_action]', "\\app\\event\\controller\\CertificationController@index");
        # 模板控制器路由(需要登录才能访问)
        Route::any(DIR_ADMIN.'/v1/template/[:_plugin]/[:_controller]/[:_action]', "\\app\\event\\controller\\TemplateController@index")
            ->allowCrossDomain([
                'Access-Control-Allow-Origin'        => $origin,
                'Access-Control-Allow-Credentials'   => 'true',
                'Access-Control-Max-Age'             => 600,
            ])
            ->middleware(\app\http\middleware\CheckAdmin::class)
            ->middleware(\app\http\middleware\ParamFilter::class);
        # 插件后台路由(官方默认路由需要登录才能访问)
        Route::any(DIR_ADMIN.'/addon', "\\app\\event\\controller\\AddonController@index")
            ->middleware(\app\http\middleware\CheckAdmin::class); // 参数 ?_plugin=client_care&_controller=client_care&_action=index
        # 插件前台路由(官方默认路由需要登录才能访问)
        Route::any('console/addon', "\\app\\event\\controller\\AddonHomeController@index")
            ->middleware(\app\http\middleware\CheckHome::class); // 参数 ?_plugin=205&_controller=client_care&_action=index
        # 模块后台路由(官方默认路由需要登录才能访问)
        Route::any('console/module/[:module]/[:controller]/[:method]', "\\app\\event\\controller\\ModuleController@index")
            ->middleware(\app\http\middleware\CheckAdmin::class);
        # 模块前台路由(官方默认路由需要登录才能访问)
        Route::any(DIR_ADMIN.'/module/[:module]/[:controller]/[:method]', "\\app\\event\\controller\\ModuleHomeController@index")
            ->middleware(\app\http\middleware\CheckHome::class);
        # 允许插件自定义路由(不管是否与系统冲突)
        $addonDir = WEB_ROOT . 'plugins/addon/';
        $addons = array_map('basename', glob($addonDir . '*', GLOB_ONLYDIR));
        # 获取已安装且启用的插件路由
        $fun = function ($value){
            return parse_name($value,1);
        };
        $addons = array_map($fun,$addons);
        // 启用插件列表缓存（缓存1小时）
        $cacheKey = \app\common\logic\CacheLogic::CACHE_PLUGIN_LIST;
        if ($addonsCache = idcsmart_cache($cacheKey)){
            $addons = json_decode($addonsCache,true);
        }else{
            $addons = Db::name('plugin')->whereIn('name',$addons)
                ->where('status',1)
                ->column('name');
            // 缓存1小时，避免插件安装/卸载后需要等待太久
            idcsmart_cache($cacheKey, json_encode($addons), 3600);
        }
        // 优化：合并所有路由文件为单文件
        $this->loadMergedRoutesOptimized($addonDir, $addons);
        // 插件升级时不加载类文件，防止升级后更新方法使用的是原来的
        $requestStr = request()->request()['s'] ?? '';
        $requestUri = request()->server()['REQUEST_URI'] ?? '';
        $requestMethod = request()->method() ?? '';
        if(stripos(ltrim($requestStr, '/'), DIR_ADMIN.'/v1/plugin/')===0 && in_array(substr($requestStr, strrpos($requestStr, '/')), ['/download', '/upgrade'])){
        }else if(stripos(ltrim($requestStr, '/'), DIR_ADMIN.'/v1/upstream/product')===0 && in_array($requestMethod, ['POST', 'PUT'])){
        }else if(stripos($requestUri, 'upgrade/upgrade.php')!==false){
        }else{
            # 获取插件注册钩子（已过滤无效类，直接使用）
            $systemHookPlugins = $this->getCacheHook();
            if (!empty($systemHookPlugins)) {
                foreach ($systemHookPlugins as $hookPlugin) {
                    # 监听(注册)插件钩子（缓存中已包含完整类名和方法名）
                    Event::listen($hookPlugin['name'], [$hookPlugin['class'], $hookPlugin['method']]);
                }
            }
        }
    }

    // 缓存插件钩子（优化：缓存时验证类存在性，存储完整类名和方法名）
    public function cacheHook()
    {
        $systemHookPlugins = Db::name('plugin_hook')->alias('a')
            ->field('a.name,a.plugin')
            ->leftjoin('plugin b', 'b.name=a.plugin')
            ->where('a.status',1)
            ->where('b.status',1)
            ->where('a.module','addon') # 仅插件
            ->order('b.hook_order', 'asc')
            ->select()->toArray();
        // 优化：在缓存时就验证类的存在性并预处理数据
        $validHooks = [];
        $checkedPlugins = []; // 缓存已检查的插件类，避免重复检查
        foreach ($systemHookPlugins as $hook) {
            $plugin = $hook['plugin'];
            // 如果该插件已经检查过不存在，跳过
            if (isset($checkedPlugins[$plugin]) && $checkedPlugins[$plugin] === false) {
                continue;
            }
            // 如果该插件还未检查过，进行类存在性检查
            if (!isset($checkedPlugins[$plugin])) {
                $class = get_plugin_class($plugin, 'addon');
                $checkedPlugins[$plugin] = class_exists($class) ? $class : false;
            }
            // 只缓存有效的钩子，并存储完整的类名和方法名
            if ($checkedPlugins[$plugin] !== false) {
                $validHooks[] = [
                    'name'   => $hook['name'],
                    'plugin' => $plugin,
                    'class'  => $checkedPlugins[$plugin],
                    'method' => parse_name($hook['name'], 1)
                ];
            }
        }
        return $validHooks;
    }

    // 获取插件钩子
    public function getCacheHook()
    {
        // 优化：使用缓存插件钩子（缓存1小时）
        $cacheKey = \app\common\logic\CacheLogic::CACHE_PLUGIN_HOOKS;
        $systemHookPlugins = idcsmart_cache($cacheKey);
        if (empty($systemHookPlugins)){
            $systemHookPlugins = $this->cacheHook();
            // 缓存1小时
            idcsmart_cache($cacheKey, $systemHookPlugins, 3600);
        }
        return $systemHookPlugins;
    }

    /**
     * 加载合并优化的路由文件（完全限定类名版本 + 降级处理）
     * @param string $addonDir 插件目录
     * @param array $addons 插件列表
     */
    private function loadMergedRoutesOptimized($addonDir, $addons)
    {
        $this->loadRoutesDirectly($addonDir, $addons);
//        // 生成缓存文件名（基于插件列表 MD5）
//        $pluginKey = md5(json_encode($addons));
//        $routeDir = IDCSMART_ROOT . 'runtime/route/';
//        $cacheFile = $routeDir . 'merged_fqn_' . $pluginKey . '.php';
//        $fallbackFlag = $routeDir . 'fallback_' . $pluginKey . '.lock';
//
//        // 确保 runtime/route 目录存在
//        if (!is_dir($routeDir)) {
//            mkdir($routeDir, 0755, true);
//        }
//
//        // 清理 runtime/route/ 目录下的旧缓存文件（只保留当前 MD5 的）
//        $this->cleanOldRouteCacheFiles($routeDir, $pluginKey);
//
//        // Level 1: 检查是否在降级黑名单中
//        if (is_file($fallbackFlag)) {
//            $fallbackTime = filemtime($fallbackFlag);
//            // 24小时内不再尝试缓存，直接降级
//            if (time() - $fallbackTime < 86400) {
//                $this->loadRoutesDirectly($addonDir, $addons);
//                return;
//            } else {
//                // 超过24小时，清除降级标志，重新尝试
//                @unlink($fallbackFlag);
//            }
//        }
//        // Level 2: 尝试加载缓存（带异常处理）
//        if (is_file($cacheFile)) {
//            if ($this->tryLoadRouteCache($cacheFile, $fallbackFlag, $addonDir, $addons)) {
//                return; // 缓存加载成功
//            }
//            // 加载失败，继续执行降级流程
//        }
//        // Level 3: 生成新缓存（带异常处理）
//        if (!$this->tryGenerateRouteCache($addonDir, $addons, $cacheFile, $fallbackFlag)) {
//            // 生成失败，降级到原始方式
//            $this->loadRoutesDirectly($addonDir, $addons);
//        }
    }
    
    /**
     * 清理 runtime/route/ 目录下的旧路由缓存文件
     * @param string $routeDir 路由缓存目录
     * @param string $currentPluginKey 当前插件列表的 MD5 键
     */
    private function cleanOldRouteCacheFiles($routeDir, $currentPluginKey)
    {
        static $cleaned = false;
        if ($cleaned) {
            return; // 每个请求只清理一次
        } 
        
        try {
            if (!is_dir($routeDir)) {
                return;
            }
            
            $files = glob($routeDir . '*');
            if (empty($files)) {
                return;
            }
            
            foreach ($files as $file) {
                if (!is_file($file)) {
                    continue;
                }
                
                $filename = basename($file);
                
                // 保留当前使用的缓存文件（包含当前 MD5）
                if (strpos($filename, $currentPluginKey) !== false) {
                    continue;
                }
                
                // 保留错误日志文件
                if ($filename === 'cache_error.log') {
                    continue;
                }
                
                // 处理临时文件（.tmp）
                if (strpos($filename, '.tmp') !== false) {
                    // 删除超过 5 分钟的临时文件（可能是之前生成失败的）
                    if (time() - filemtime($file) > 300) {
                        @unlink($file);
                    }
                    continue;
                }
                
                // 删除所有旧的路由缓存文件
                // 文件名格式：merged_fqn_{32位md5}.php 或 fallback_{32位md5}.lock
                if (preg_match('/^(merged_fqn_|fallback_)[a-f0-9]{32}\.(php|lock)$/', $filename)) {
                    @unlink($file);
                }
            }
            
        } catch (\Exception $e) {
            // 清理失败不影响主流程
            $this->logRouteCacheError('Clean old route cache files failed: ' . $e->getMessage());
        }
        
        $cleaned = true;
    }

    /**
     * 尝试加载路由缓存（Level 2 降级）
     * @return bool 加载成功返回 true，失败返回 false
     */
    private function tryLoadRouteCache($cacheFile, $fallbackFlag, $addonDir, $addons)
    {
        // 预检查：文件完整性
        $fileSize = filesize($cacheFile);
        if ($fileSize < 100) { // 文件太小，可能损坏
            $this->logRouteCacheError('Cache file too small: ' . $fileSize . ' bytes');
            @unlink($cacheFile);
            return false;
        }
        
        // 设置加载标志和错误处理
        $loadSuccess = false;
        $startTime = microtime(true);
        
        try {
            // 注册 shutdown 函数检测 fatal error
            register_shutdown_function(function() use (&$loadSuccess, $cacheFile, $fallbackFlag) {
                if (!$loadSuccess) {
                    $error = error_get_last();
                    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                        $this->logRouteCacheError('Fatal error when loading cache: ' . json_encode($error));
                        $this->triggerFallback($cacheFile, $fallbackFlag);
                    }
                }
            });
            
            // 尝试加载缓存
            include_once $cacheFile;
            
            // 加载 hooks 文件
            $this->loadAllHooksFiles();
            
            $loadSuccess = true;
            return true;
            
        } catch (\Throwable $e) {
            $this->logRouteCacheError('Exception when loading cache: ' . $e->getMessage());
            $this->triggerFallback($cacheFile, $fallbackFlag);
            return false;
        }
    }
    
    /**
     * 尝试生成路由缓存（Level 3 降级）
     * @return bool 生成成功返回 true，失败返回 false
     */
    private function tryGenerateRouteCache($addonDir, $addons, $cacheFile, $fallbackFlag)
    {
        try {
            $content = "<?php\n";
            $content .= "// Route cache (FQN) generated at " . date('Y-m-d H:i:s') . "\n";
            $content .= "// Auto-generated - DO NOT EDIT\n";
            $content .= "// All classes converted to FQN (Fully Qualified Name)\n\n";
            
            // 提取共享变量
            $content .= "\$origin = \$_SERVER['HTTP_ORIGIN'] ?? '';\n\n";
            
            // 1. 合并所有插件路由
            foreach ($addons as $addon) {
                $addon = parse_name($addon);
                $routeFile = $addonDir . $addon . '/route.php';
                
                if (is_file($routeFile)) {
                    $routeContent = file_get_contents($routeFile);
                    $routeContent = $this->convertToFullyQualifiedNames($routeContent);
                    $content .= "\n// === Plugin: {$addon} ===\n";
                    $content .= $routeContent . "\n";
                }
            }
            
            // 2. 合并 Server 模块路由
            $serverDir = WEB_ROOT . 'plugins/server/';
            if (is_dir($serverDir)) {
                $servers = array_map('basename', glob($serverDir . '*', GLOB_ONLYDIR));
                foreach ($servers as $server) {
                    $routeFile = $serverDir . $server . '/route.php';
                    if (is_file($routeFile)) {
                        $routeContent = file_get_contents($routeFile);
                        $routeContent = $this->convertToFullyQualifiedNames($routeContent);
                        $content .= "\n// === Server Module: {$server} ===\n";
                        $content .= $routeContent . "\n";
                    }
                }
            }
            
            // 3. 合并 Reserver 模块路由
            $reserverDir = WEB_ROOT . 'plugins/reserver/';
            if (is_dir($reserverDir)) {
                $reservers = array_map('basename', glob($reserverDir . '*', GLOB_ONLYDIR));
                foreach ($reservers as $reserver) {
                    $routeFile = $reserverDir . $reserver . '/route.php';
                    if (is_file($routeFile)) {
                        $routeContent = file_get_contents($routeFile);
                        $routeContent = $this->convertToFullyQualifiedNames($routeContent);
                        $content .= "\n// === Reserver Module: {$reserver} ===\n";
                        $content .= $routeContent . "\n";
                    }
                }
            }

            // 原子写入（先写临时文件，再重命名）
            // 注意：runtime/route 目录已在 loadMergedRoutesOptimized 中创建
            $tempFile = $cacheFile . '.tmp';
            file_put_contents($tempFile, $content);

            // 验证生成的文件语法正确
            if (!$this->validatePHPSyntax($tempFile)) {
                @unlink($tempFile);
                $this->logRouteCacheError('Generated cache file has syntax errors');
                $this->triggerFallback($cacheFile, $fallbackFlag);
                return false;
            }

            // 重命名为正式文件
            rename($tempFile, $cacheFile);
            
            // 立即加载
            include_once $cacheFile;
            
            // 加载 hooks 文件
            $this->loadAllHooksFiles();
            
            return true;
            
        } catch (\Throwable $e) {
            $this->logRouteCacheError('Exception when generating cache: ' . $e->getMessage());
            $this->triggerFallback($cacheFile, $fallbackFlag);
            return false;
        }
    }
    
    /**
     * 降级方式：直接加载各个插件路由文件（Level 4 降级）
     */
    private function loadRoutesDirectly($addonDir, $addons)
    {
        // 加载所有插件路由
        foreach ($addons as $addon) {
            $addon = parse_name($addon);
            $routeFile = $addonDir . $addon . '/route.php';
            if (is_file($routeFile)) {
                include_once $routeFile;
            }
        }
        
        // 加载所有模块 hooks 和路由
        $this->loadAllHooksFiles();
        
        // Server 模块路由
        $serverDir = WEB_ROOT . 'plugins/server/';
        if (is_dir($serverDir)) {
            $servers = array_map('basename', glob($serverDir . '*', GLOB_ONLYDIR));
            foreach ($servers as $server) {
                $routeFile = $serverDir . $server . '/route.php';
                if (is_file($routeFile)) {
                    include_once $routeFile;
                }
            }
        }
        
        // Reserver 模块路由
        $reserverDir = WEB_ROOT . 'plugins/reserver/';
        if (is_dir($reserverDir)) {
            $reservers = array_map('basename', glob($reserverDir . '*', GLOB_ONLYDIR));
            foreach ($reservers as $reserver) {
                $routeFile = $reserverDir . $reserver . '/route.php';
                if (is_file($routeFile)) {
                    include_once $routeFile;
                }
            }
        }
    }

    /**
     * 将路由文件内容转换为完全限定类名（FQN）版本
     * @param string $content 原始路由文件内容
     * @return string 转换后的内容
     */
    private function convertToFullyQualifiedNames($content)
    {
        // 1. 移除 <?php 标签
        $content = preg_replace('/^<\?php\s*/i', '', $content);
        
        // 2. 解析所有 use 语句，建立类名映射表
        $classMap = $this->parseUseStatements($content);
        
        // 3. 移除所有 use 语句（包括注释、文档块）
        $content = preg_replace('/^\s*\/\*\*[\s\S]*?\*\/\s*$/m', '', $content); // 移除文档注释
        $content = preg_replace('/^\s*use\s+[^;]+;\s*$/m', '', $content); // 移除 use 语句
        
        // 4. 替换所有简短类名为完全限定名
        foreach ($classMap as $shortName => $fullName) {
            // 使用负向后顾和负向前瞻确保精确匹配
            // 匹配: ClassName:: 或 new ClassName 或 ClassName::class
            // 不匹配: \ClassName 或 $ClassName 或 'ClassName' 或 "ClassName"
            $pattern = '/(?<![\\\\\w\'"])(' . preg_quote($shortName, '/') . ')(?=::|\s|;|\)|,|\()/';
            $content = preg_replace($pattern, '\\' . $fullName, $content);
        }
        
        return $content;
    }
    
    /**
     * 解析 use 语句，建立类名映射表
     * @param string $content 文件内容
     * @return array ['ShortName' => 'Full\\Namespace\\ClassName']
     */
    private function parseUseStatements($content)
    {
        $classMap = [];
        
        // 匹配所有 use 语句
        // 支持格式：
        // use Namespace\ClassName;
        // use Namespace\ClassName as Alias;
        preg_match_all('/^\s*use\s+([^;]+);/m', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $useStatement) {
                $useStatement = trim($useStatement);
                
                // 检查是否有别名
                if (strpos($useStatement, ' as ') !== false) {
                    // use Namespace\ClassName as Alias
                    list($fullName, $alias) = explode(' as ', $useStatement);
                    $fullName = trim($fullName);
                    $alias = trim($alias);
                    $classMap[$alias] = $fullName;
                } else {
                    // use Namespace\ClassName
                    $fullName = trim($useStatement);
                    $shortName = substr($fullName, strrpos($fullName, '\\') + 1);
                    $classMap[$shortName] = $fullName;
                }
            }
        }
        
        return $classMap;
    }
    
    /**
     * 触发降级（创建降级标志文件）
     */
    private function triggerFallback($cacheFile, $fallbackFlag)
    {
        // 删除失效的缓存文件
        @unlink($cacheFile);
        @unlink($cacheFile . '.tmp');
        
        // 创建降级标志文件（24小时内不再尝试）
        file_put_contents($fallbackFlag, json_encode([
            'time' => date('Y-m-d H:i:s'),
            'reason' => 'Route cache failed',
        ]));
    }
    
    /**
     * 记录路由缓存错误日志
     */
    private function logRouteCacheError($message)
    {
//        $routeDir = IDCSMART_ROOT . 'runtime/route/';
//        if (!is_dir($routeDir)) {
//            mkdir($routeDir, 0755, true);
//        }
//
//        $logFile = $routeDir . 'cache_error.log';
//        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
//        @file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * 验证 PHP 文件语法是否正确
     * @param string $file 文件路径
     * @return bool
     */
    private function validatePHPSyntax($file)
    {
        // 使用 php -l 命令检查语法（如果可用）
//        $phpBinary = PHP_BINARY;
//        if (!empty($phpBinary) && is_executable($phpBinary)) {
//            $output = [];
//            $returnVar = 0;
//            exec($phpBinary . ' -l ' . escapeshellarg($file) . ' 2>&1', $output, $returnVar);
//
//            if ($returnVar === 0) {
//                return true;
//            } else {
//                $this->logRouteCacheError('Syntax validation failed: ' . implode("\n", $output));
//                return false;
//            }
//        }
        
        // 如果无法使用 php -l，尝试简单的语法检查
        $content = file_get_contents($file);
        
        // 检查是否有明显的语法错误
        if (strpos($content, '<?php') === false) {
            return false; // 缺少 PHP 开始标签
        }
        
        // 简单的括号匹配检查
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        if ($openBraces !== $closeBraces) {
            $this->logRouteCacheError('Brace mismatch: open=' . $openBraces . ', close=' . $closeBraces);
            return false;
        }
        
        return true; // 无法完全验证，返回 true
    }

    /**
     * 加载所有 hooks 文件（必须单独 include，包含事件注册逻辑）
     */
    private function loadAllHooksFiles()
    {
        // Server 模块 hooks
        $serverDir = WEB_ROOT . 'plugins/server/';
        $servers = array_map('basename', glob($serverDir . '*', GLOB_ONLYDIR));
        foreach ($servers as $server) {
            $hooksFile = $serverDir . $server . '/hooks.php';
            if (is_file($hooksFile)) {
                include_once $hooksFile;
            }
        }
        // Reserver 模块 hooks
        $reserverDir = WEB_ROOT . 'plugins/reserver/';
        $reservers = array_map('basename', glob($reserverDir . '*', GLOB_ONLYDIR));
        foreach ($reservers as $reserver) {
            $hooksFile = $reserverDir . $reserver . '/hooks.php';
            if (is_file($hooksFile)) {
                include_once $hooksFile;
            }
        }
        // 模板控制器 hooks
        $templateDir = WEB_ROOT . 'web/';
        $templates = array_map('basename', glob($templateDir . '*', GLOB_ONLYDIR));
        foreach ($templates as $template) {
            $hooksFile = $templateDir . $template . '/controller/hooks.php';
            if (is_file($hooksFile)) {
                include_once $hooksFile;
            }
        }
    }

}
