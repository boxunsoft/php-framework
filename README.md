# PHP框架

一个简单的PHP MVC框架

## Usage

### Install

In composer.json
```composer.json
...
  "require": {
    "boxunsoft/php-framework": "^4.0"
  },
  "autoload": {
    "psr-4": {
      "Bx\\": "src/"
    }
  }
...
```

### 应用目录
```dir
env                                         // 环境配置
  |-develop
      |- app.php
  |-test
      |- app.php
  |-release
config                                      // 系统配置
  |- router                                     // 路由
       |- appname.php
public                                      // 入口
  |- appname
       |- index.php
src
  |- App                                    // 多应用
       |- AppName                               // 应用一
           |- Controller                            // 应用控制器
                |- Index.php
           |- View                                  // 应用视图,可自行选择模板引擎
                |- Index.phtml
  |- Model                                  // 存放模型
  |- Library                                // 存放自定义类库
  
  
```

#### public/appname/index.php

> 目录和文件名必须小写

```php
use Alf\Kernel;

$rootPath = (dirname(__DIR__), 3);
require $rootPath . '/vendor/autoload.php';

$Kernel = Kernel::getInstance();
$app->Kernel($rootPath, 'AppName');
```

#### App/AppName/Controller/Index.php

```php
namespace Bx\App\AppName\Controller;

use Alf\Controller;

class Index extends Controller
{
    public function main()
    {
        $response = [
            'name' => 'index',
            'message' => 'Index::main()'
        ];
        $this->response()->success($response);
    }
}
```

## 安全建议

```
// 只允许同域名iframe嵌套
header('X-Frame-Options: SAMEORIGIN');
// 禁止浏览器用MIME-sniffing解析资源类型
header('X-Content-Type-Options: nosniff');
// 启用XSS保护
header('X-XSS-Protection: 1; mode=block');
```

## 引用

> * symfony/http-foundation
> * nikic/fast-route
> * symfony/console
