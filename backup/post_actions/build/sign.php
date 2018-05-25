<?php

/**
 * 网站签名代码 for 自动发卡系统 and 免签系统
 * 可用于网站源码的来源跟踪
 * 理论上基于Think.Admin.开发的都能使用
 * @author zhangjianwei
 * @date   2018-05-24
 * @update 2018-05-25
 */

if (empty($argv[1]) || empty($argv[2]) || empty($argv[3])) {
    echo "usage: php {$argv[0]} [project_path] [vsign] [project_id]\n";
    echo "example: php {$argv[0]} /data/webroot/zidongfaka 'local.faka.cn|20180524' 5\n";
    exit;
}

define('ROOT_PATH', rtrim($argv[1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

$authFile = ROOT_PATH . 'extend/hook/AccessAuth.php';
$configFile = ROOT_PATH . 'application/extra/deploy_unique.php';


$vsign = $argv[2];
$projectId = $projectId[2];
echo "using project dir: " . ROOT_PATH . "\n";
echo "using vsign: $vsign\n";
echo "using project_id: $projectId\n";

$configResult = modifyConfigFile($configFile, $vsign);
echo "config result: $configResult\n";

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
    $code = <<<'EOT'
    try {
        if (cache('auth_domain') !== 1) {
            $c = file_get_contents("https://zuy.cn/api.php?m=auth&a=index&prj_id={{$project_id}}&domain={$_SERVER['HTTP_HOST']}&vsign={{$vsign}}");
            $res = json_decode($c, true);
            if ($res == false || $res['status'] == -1) {
                exit(isset($res['info']) ? $res['info'] : '未知错误 403-1');
            }
            cache('auth_domain', 1, 3600);
        }
    } catch (\Exception $e) {
        exit(isset($res['info']) ? $res['info'] : '未知错误 403-2');
    }
EOT;
    global $projectId;
    $code = str_replace('{{$project_id}}', $projectId, $code);
    $code = str_replace('{{$vsign}}', $vsign, $code);
    echo "========= code generated =========\n";
    echo $code . PHP_EOL;
    echo "========= code generated =========\n";
    return base64_encode($code);
}