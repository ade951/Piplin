<?php

/**
 * 网站签名代码 for 自动发卡系统 and 免签系统 and 支付API系统
 * 可用于网站源码的来源跟踪
 * 理论上基于Think.Admin.开发的都能使用
 * @author zhangjianwei
 * @date   2018-05-24
 * @update 2018-05-25
 */

//这段代码可以方便手工执行
//$argv[1] = '/data/webroot/MianQianZhiFu/';
//$argv[2] = 'www.glpay.com|MianQianZhiFu|201805251747';
//$argv[3] = '6';

//这段代码可以方便手工执行
//$argv[1] = '/data/webroot/zuyapi/';
//$argv[2] = 'pay.tianniu.cc|zuyapi|201805251853';
//$argv[3] = '8';

if (empty($argv[1]) || empty($argv[2]) || empty($argv[3])) {
    echo "usage: php {$argv[0]} [project_path] [vsign] [project_id]\n";
    echo "example: php {$argv[0]} /data/webroot/zidongfaka 'local.faka.cn|20180524' 5\n";
    exit;
}

define('ROOT_PATH', rtrim($argv[1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

$vsign = $argv[2];
$projectId = $argv[3];
echo "using project dir: " . ROOT_PATH . "\n";
echo "using vsign: $vsign\n";
echo "using project_id: $projectId\n";

/**
 * 这里的projectId对应发布系统中的项目id，目前已有的系统：
 * 5 = 自动发卡
 * 6 = 免签支付
 * 8 = 支付API
 */
if (in_array($projectId, [5, 6])) {
    //适合基于 Think.Admin 开发的系统
    $authFile = ROOT_PATH . 'extend/hook/AccessAuth.php';
    $configFile = ROOT_PATH . 'application/extra/deploy_unique.php';
} elseif (in_array($projectId, [8])) {
    //适合基于 ThinkPHP3.2 开发的系统
    $authFile = ROOT_PATH . 'core/Library/Behavior/CheckAuthBehavior.class.php';
    $configFile = ROOT_PATH . 'Application/Common/Conf/deploy.php';
} else {
    throw new Exception('项目未定义');
}


$configResult = modifyConfigFile($configFile, $vsign);
echo "config result: $configResult\n\n";

$authResult = modifyAuthFile($authFile, $vsign);
echo "auth result: $authResult\n";

if ($configResult && $authResult) {
    echo "sign success\n\n";
} else {
    echo "sign error!\n\n";
}

/**
 * 修改配置文件
 * @param $filename
 * @param $vsign
 * @return bool
 * @throws Exception
 */
function modifyConfigFile($filename, $vsign)
{
    if (!is_file($filename)) {
        throw new Exception("配置文件不存在");
    }
    $config = (require $filename);
    if (!is_array($config)) {
        throw new Exception("配置文件错误");
    }
    $config['vhash'] = md5($vsign);
    $content = "<?php\n\nreturn " . var_export($config, true) . ";";
    echo "↓↓↓↓↓↓↓↓↓↓ vhash generated ↓↓↓↓↓↓↓↓↓↓\n";
    echo $content . PHP_EOL;
    echo "↑↑↑↑↑↑↑↑↑↑ vhash generated ↑↑↑↑↑↑↑↑↑↑\n";
    return (file_put_contents($filename, $content) > 0);
}

/**
 * 修改Auth文件
 * @param $filename
 * @param $vsign
 * @return bool
 * @throws Exception
 */
function modifyAuthFile($filename, $vsign)
{
    $content = file_get_contents($filename);
    $pattern = '/(protected function auth\(\)[ \r\n\t]*\{[ \r\n\t]*eval\(base64_decode\(\')([^\']+)(\'\)\);[ \r\n\t]*}[ \r\n\t]*})/';
    $isMatched = preg_match($pattern, $content, $matches);
    if (!$isMatched) {
        throw new Exception('代码有变更，请联系管理员');
    }

    $newCode = genNewCode($vsign);
    $replace = '$1' . $newCode . '$3';
    $newContent = preg_replace($pattern, $replace, $content);

    return (file_put_contents($filename, $newContent) > 0);
}


function genNewCode($vsign)
{
    global $projectId;
    if (in_array($projectId, [5, 6])) {
        //TP5里面的缓存函数
        $cacheFunction = 'cache';
    } elseif (in_array($projectId, [8])) {
        //TP3里面的缓存函数
        $cacheFunction = 'S';
    } else {
        throw new Exception('项目未定义');
    }
    $code = <<<'EOT'
    try {
        if ({{$cacheFunction}}('auth_domain') !== 1) {
            $c = file_get_contents("https://zuy.cn/api.php?m=auth&a=index&prj_id={{$project_id}}&domain={$_SERVER['HTTP_HOST']}&vsign={{$vsign}}");
            $res = json_decode($c, true);
            if ($res == false || $res['status'] == -1) {
                exit(isset($res['info']) ? $res['info'] : '未知错误 403-1');
            }
            {{$cacheFunction}}('auth_domain', 1, 3600);
        }
    } catch (\Exception $e) {
        exit(isset($res['info']) ? $res['info'] : '未知错误 403-2');
    }
EOT;
    $code = str_replace('{{$project_id}}', $projectId, $code);
    $code = str_replace('{{$vsign}}', $vsign, $code);
    $code = str_replace('{{$cacheFunction}}', $cacheFunction, $code);
    echo "↓↓↓↓↓↓↓↓↓↓ code generated ↓↓↓↓↓↓↓↓↓↓\n";
    echo $code . PHP_EOL;
    echo "↑↑↑↑↑↑↑↑↑↑ code generated ↑↑↑↑↑↑↑↑↑↑\n";
    return base64_encode($code);
}