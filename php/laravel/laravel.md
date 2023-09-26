# 路由

## 	1.添加路由

```
	Route::get('/order', [HomeController::*class*, "getOrder"])->middleware('benchmark');
```

## 	2.中间件

### 			创建中间件

```
php artisan make:middleware Benchmark
				
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\Translation\t;

class Benchmark
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 前置
        $sTime = microtime(true);
        $response = $next($request);

        $runTime = microtime(true) - $sTime;
        Log::info("Benchmark", [
            'url' => $request->url(),
            'input' => $request->input(),
            'time' => "$runTime ms",
        ]);
        //后置
        return $response;
    }
}


Http/Kernel.php 
1.配置全局中间件
  protected $middleware = [
      Benchmark::class
    ];
2.路由中间件
	  protected $routeMiddleware = [
        'benchmark'=>Benchmark::class
    ];
3.控制器的构造方法
public function __construct()
    {

        // 设置白名单 黑名单
//        $this->middleware("benchmark", [
//            'only' => ['getOrder']
//        ]);
        // 中间件传参数
        $this->middleware("benchmark:test,test1", [
            'only' => ['getOrder']
        ]);
    }

```

