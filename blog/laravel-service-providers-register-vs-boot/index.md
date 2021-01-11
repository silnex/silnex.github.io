---
layout: post
id: 9
title:  "서비스 프로바이더에서 Boot와 Register의 차이와 사용시 주의할 점"
subtitle: "boot vs register in Service provider and Caution"
description: "boot vs register의 차이점과 사용시 주의해야 하는 부분에 대해서 알아봅니다."
type: "Laravel"
created_at: "2021-01-11"
updated_at: "2021-01-11"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/provider.png"
order: 9
tags: ['laravel', 'tip', 'service-provider', 'short-article']
comments: true
---

# Laravel Service provider
라라벨에선 [`Service provider`](https://laravel.com/docs/8.x/providers)를 통해 라라벨 어플리케이션이 실행되기 전에 필요한 데이터를 미리 등록, 가져올 수 있습니다.

# Service provider 생성
이러한 Service provider는 `php artisan make:provider MyCustomServiceProvider` 명령을 통해서 생성이 가능합니다.

그렇게 `artisan` 명령어를 통해 생성된 코드는 아래와 같습니다.
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MyCustomServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
```

## Register method vs Boot method
이렇게 생성된 `Service provider`에는 `register()`메소드와 `boot()`메소드가 존재하는데, 저는 항상 이 두 메소드가 햇갈려서 삽질을 해데 이번 글을 써봅니다.

먼저 `Service provider`의 실행 순서는

1. `register()` 메소드가 먼저 실행되고,
2. `boot()` 메소드가 실행됩니다.

다만 여기서 주의 해야하는건 `Service provider`에 있는 `register()`와 `boot()`가 순서대로 실행 되는것이 아니라,

'등록'되었거나, 'autodiscover'된 모든 `Service provider`의 `register()`가 실행된 후 그 다음 각각의 `boot()`메소드가 실행됩니다.

즉, 모든 `Service provider`가 등록(`register`)된 후 부팅(`boot`)되어집니다.  
이러한 순서로 인해 `register()`메소드에서 선언된 내용을 보장할 수 없게 되어집니다.  

## 본론으로 들어가면
위에 순서가 했갈리는 것도 있지만,  
이번 오류를 찾으면서 놓쳣던 것을 이야기해보려고 합니다.

> `register()`메소드에는 다른 이벤트 리스너나 라우트 또는 기타 기능의 일부등을 등록하거나, 사용하려고 해선 안됩니다.

이런 내용이 라라벨 공식 문서([번역](https://laravel.kr/docs/providers#writing-service-providers), [원문](https://laravel.com/docs/providers#writing-service-providers))에도 있고, [Taylor Otwell이 작성한 책](https://leanpub.com/laravel)에도 나와 있습니다.

이런 실수의 가장 좋은 예(?)를 찾으려고 멀리 갈 필요 없이 [Laravel의 Macro](/blog/laravel-macro-and-mixin/#macro-사용방법)글에 대문짝만 하게 박혀있습니다.

```php
/** app/Providers/AppServiceProvider.php */
// ...
use Illuminate\Database\Query\Builder;

class AppServiceProvider extends ServiceProvider
{
    // ...
    public function register()
    {
        Builder::macro('search', function (array $fields, string $searchString) {
            foreach ($fields as $field) {
                $this->orWhere($field, 'like', '%' . $searchString . '%');
            }

            return $this;
        });
    }
    // ...
}
```
<figcaption>자랑스럽게 박혀있는 실수</figcaption>

물론 실행하는 위치가 `AppServiceProvider`이다보니 `Builder` facade가 이미 등록되어 사용 할 수 있겟지만,  
Laravel에서 권장하는 바는 `Builder` facade가 등록(`register`) 되었을지 보장되지 않기 때문에 `boot()`메소드 안에 넣는것이 좋습니다.

## 마무리
처음엔 register와 boot의 차이점을 간단히 정리 할까하며 시작한 검색인데,  
여태까지 Service provider의 사용을 잘못 하고 있덧다는걸 알고나니 창피흐드..ㅎㅎ;;

<div style="text-align: right">Thumbnail designed by freepik</div>