# php-framework

A MVC framework with PHP

## Usage

### Install

In composer.json
```composer.json
...
  "require": {
    "boxunsoft/php-framework": "2.0.*"
  },
  "autoload": {
    "psr-4": {
      "Ala\\": "src/"
    }
  }
...
```

### App Directorys
```dir
src
  |- Application
       |- AppName
           |- Controller
                |- Index.php
           |- View
                |- Index.phtml
  |- public
       |- appname
            |- index.php
  
  
```

#### public/appname/index.php

> path name and file name must be lower

```php
$rootPath = dirname(dirname(dirname(__DIR__)));
require $rootPath . '/vendor/autoload.php';

$app = Alf\Application::getInstance();
$app->main($rootPath, 'AppName');
```

#### Application/AppName/Controller/Index.php

```php
namespace Ala\Application\AppName\Controller;

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

## Documents

> writing...

## Thank

Standing on the shoulders of giants. Thank All.
