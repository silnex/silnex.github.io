---
layout: post
title:  "Laravel의 Macro"
subtitle: "With Mixin"
type: "Laravel"
blog: true
text: true
draft: true
author: "silnex"
post-header: true
order: 1
tags: ['laravel', 'macro', 'mixin', 'tip']
---

# Laravel Macro 소개
Laravel에는 기존의 존재하는 다양한 메소드 들을 제공하지만, 내가 원하는 기능을 하는 메소드를 넣기위해서 일일히 Model을 확장해 작업하기는 쉽지 않습니다.  
그렇기에 Laravel에선 이러한 메소드를 확장 없이 등록하여 사용할 수 있는 Macro 기능을 제공합니다.

다만 모든 클래스에 대해서 등록하지는 않고 아래의 Class들 에서 등록이 가능합니다.

- Illuminate\Database\Eloquent\Builder
- Illuminate\Database\Eloquent\Query
- Illuminate\Support\Collection
- Illuminate\Support\Str
- Illuminate\Http\UploadedFile
- Illuminate\Http\RedirectResponse
- Illuminate\Http\Request
- Illuminate\Routing\ResponseFactory
- Illuminate\Routing\UrlGenerator
- Illuminate\Routing\Router
- ...

물론 위의 Class를 이용한 Facades, Helper, Class들 도 매크로 등록이 가능합니다. 
 - Illuminate\Support\Facades\URL
 - Illuminate\Http\Response
 - Illuminate\Support\Facades\Route
 - ...

# Laravel Macro의 사용법

```yaml
posts:
  id: integer
  title: string
  content: string
```

만일 위와같은 `Post` 모델에서 Like를 이용해 `title` 값을 검색하는 구문을 만들고 싶다면, 기존의 Eloquent로는 아래와 같이 사용해야 할겁니다.

```php
App\Models\Post::where('title', 'like', '%' . $searchString . '%')->get();
```

하지만 만일 이러한 검색이 `User` 모델에도 적용된다면, 똑같은 코드를 아래 처럼 반복 해야 되겠죠.  
그리고, 만약 `title` 뿐 아니라 `content`까지 같이 검색해야 한다면 코드는 더욱 복잡해 질 것입니다.

```php
App\Models\Post::query() // 이건 보기 코드 좋으라고 부리는 기교(?) 입니다 ㅎ..
  ->orWhere('title', 'like', '%' . $searchString . '%')
  ->orWhere('content', 'like', '%' . $searchString . '%')->get();
```

## Macro 출동

이럴 때 매크로를 사용하면 아래처럼 깔쌈하게 처리할 수 있습니다.

```php
App\Models\Post::search(['title', 'content'], $searchString)->get();
```

물론 다른 `User` 모델에서도 사용할 수 있고, 아래 예시 처럼 Relation에서도 사용할 수 있습니다. 
```php
$user->posts()->search(['title', 'content'], $str)->get();
```

그럼 Macro에 대해서 한번 알아보도록 하죠!

## Macro 사용방법

그렇다면 Macro는 어떻게 사용할 수 있을까요? 
Macro는 동적으로 메소드를 등록하는 방식이기에 `AppServiceProvider`와 같이 서비스를 시작 하는 과정에서 등록해주어야 합니다.

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

위와 같이 등록했다면, 이제 `Query` Class를 사용하는 모든 곳에서 `->search(...)`을 사용할 수 있습니다.

## Laravel Mixin

이러한 메크로가 정말 편리하다는 것은 알겠지만, 이런 코드가 점점 늘어날 수 록 `AppServiceProvider`의 `register`에서만 관리한다는건 복잡한 일이 될 수 밖에 없습니다.

이러한 복잡성을 피하기 위한 방법이 2가지가 있습니다.

### 새로운 Service Provider 추가

```php
//
```

### Mixin

```php
//
```

---

## Macro 동작 방식

위에서도 설명했듯이 Macro는 동적으로 메소드를 추가 하는 것 입니다.  
아래의 Laravel의 코드를 보면,

```php
/**
  * Register a custom macro.
  *
  * @param  string  $name
  * @param  object|callable  $macro
  * @return void
  */
public static function macro($name, $macro)
{
    static::$macros[$name] = $macro;
}
```

이 처럼 Macro의 `$name` 변수와 실행할 콜백 함수 혹은 객체를 담은 `$macro` 변수를 받아 `$macros` 라는 property(변수)에 추가합니다.

이렇게 추가된 콜백 함수 혹은 객체는 php의 magic method인 `__call` 혹은 `__callStatic` 에서 실행 되게 되어지는데, 

```php
public function __call($method, $parameters)
{
    if (! static::hasMacro($method)) {
        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }

    $macro = static::$macros[$method];

    if ($macro instanceof Closure) {
        $macro = $macro->bindTo($this, static::class);
    }

    return $macro(...$parameters);
}
```

현재 호출된 함수의 이름과 동일한 `$macros`의 클로저의 존재여부를 확인한 후 (`$macro instanceof Closure`)  
만일 존재 한다면, Closure의 [`bindTo`](https://www.php.net/manual/en/closure.bindto.php) 메소드를 통해 객체를 전달하고,  
마지막으로 익명함수로 파라미터와 함께 실행 되어집니다.

이 부분 까지 읽으셧다면, 정말 관심이 많으신 분이시겠지만,  
이 부분이 이해되지 않으신다고 하셔도 [PHP Magic 메소드](https://www.php.net/manual/en/language.oop5.magic.php)와 PHP의 [기본적](https://www.php.net/manual/en/language.oop5.static.php)으로 [제공되어지는 부분](https://www.php.net/manual/en/class.closure.php)을 제외하고 생각하신다면, 좀 더 이해가 편하실 수도(?) 있습니다.