---
layout: post
id: 23
title: "Laravel HTTP Client Test"
subtitle: "라라벨 HTTP 테스트"
description: "라라벨 HTTP 클라이언트 테스트"
type: "Laravel"
created_at: "2021-09-21"
updated_at: "2021-09-21"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/http-testing.png"
order: 23
tags: ["laravel", "test", "http", "tip"]
comments: true
---

# Laravel HTTP Client Test

라라벨에는 HTTP Request를 쉽게 구현할 수 있는 [HTTP Client](https://laravel.com/docs/8.x/http-client)를 지원합니다.

외부 API에 대한 테스트를 우리가 진행할 필요는 없지만, 테스트 환경에서 프로덕션과 같은 API에 요청을 직접할 수 없거나, 테스트 속도를 높이기 위해서 응답값을 직접 지정해야줘야 하는 경우일 때, 응답값을 직접 지정할 수 있는 테스트를 지원합니다.

간단한 API테스트의 경우엔 기존의 문서에 대한 내용만으로도 충분하지만,  
제가 격은 검색이 필요할 때 키워드가 기존의 `HTTP test`와 `HTTP Client test`가 겹치는 문제와 그 외 테스트를 작성하면서 격은 문제에 대한 해결 방법에 대해서 내용을 정리해둡니다.

> 이전 글에서 [HTTP Async](https://silnex.github.io/blog/laravel-http-client-async/)에 대해서도 정리해 두었습니다!

## HTTP Client Fake Basic Usage

> 기본적인 사용법을 이미 알면 [여기](#하지만)로 넘어가시면 됩니다

기본적으로 HTTP 응답을 제어하기 위해선 [`Http::fake()`](https://laravel.com/docs/http-client#testing) 메소드를 사용합니다.

### Fake 응답 설정

```php
// callback
Http::fake(function () {
    return Http::response();
});
```

`callback`을 전달하면 모든 요청에 대해서 전달된 `callback`이 실행되고,

```php
// array
Http::fake([
    'api.silnex.kr/*' => Http::response(),
]);
```

`array` 로 `[url => response]` 과 같이 전달 하면, `url` 과 일치하는 경우 `response` 이 실행됩니다.

또 `url`은 `*` 표기 방식으로 `url`패턴을 지정해 줄 수 있습니다.

### Assert Request

```php
Http::fake();

// HTTP 요청
Http::post('http://api.silnex.kr/users', [
    'name' => 'silnex',
]);

// HTTP 요청이 왔는지 확인
Http::assertSent(function ($request) {
    return $request->url() === 'http://api.silnex.kr/users' &&
        $request['name'] === 'silnex';
});
```

`Http::assert..` 을 사용해서 요청이 전달되었는지, 아니면 전달되지 않았는지 확인할 수 있습니다.

이 외에도 `Http::sequence()` 등을 여러번 요청할 때 이용해서 여러번 요청할 때 정해진 순서대로 응답 하도록 설정할 수 있습니다. 자세한

사용법이 간단하고, laravel을 쓰는 입장에선 쉽고 빠르게 테스트를 작성할 수 있어서 테스트하기에 좋은 도구 입니다.

## 하지만..

제가 격은 문제는 2가지인데, 첫번째 문제는 `타입` 두번째 문제는 `타임아웃` 입니다.

도큐먼트에도 없고 위에서 말한것처럼 키워드가 겹쳐서 검색도 잘 안되서 둘 다 일일히 코드를 보면 찾아 갔는데,  
`타입` 문제야 IDE에서 어느정도 해결해주거나, 아니면 직접 실행하고 `get_class`를 찍던 하면 되는데, `타임아웃`(Http client error test) 문제에 경우엔 진짜 별에별 시도를 했었습니다.

아래에선 이 문제를 해결한 방법들에 대한 내용을 정리 해보았습니다.

# 타입 문제

Fake 메소드를 사용할 때 callback으로 전달되는 타입이 정확하지 않아 IDE에서 자동완성이 안되거나, 잘못 타입을 잘못 설정해서 테스트가 깨지는 경우가 종종 있습니다.

아래에 callback 파라미터에 대해서 타입과 내용을 상세하게 정리해 놓았습니다.

```php
use \Illuminate\Http\Client\Request;
use \GuzzleHttp\Promise\PromiseInterface;

Http::fake([
    '*' => function (Request $request, array $options): PromiseInterface {
        // $options 은 설정값에 따라 달라질 수 있습니다.
        $options = [
            0 => "http_errors",
            1 => "laravel_data",
            2 => "on_stats",
            3 => "synchronous",
            4 => "handler",
            5 => "cookies",
            6 => "allow_redirects",
            7 => "decode_content",
            8 => "verify",
            9 => "idn_conversion",
        ];

        return Http::response();
    }
])
```

`\Illuminate\Http\Client\Request`는 그나마 이런 타입이겠거니 짐작이 가서 괜찮은데, return 타입이 `\GuzzleHttp\Promise\PromiseInterface`가 아니라 `Illuminate\Http\Client\Response` 으로 설정해놓고 한참을 헤맸습니다.

# Timeout 문제 (Http client error test)

서비스 중 유저가 제공한 feed에 대해 요청이 올 때 마다 요청을 전달 해야하는 부분이 있는데,  
대략 흐름이 아래와 코드와 같습니다.

```php
// FeedCheckController@update

try {
    $user = User::find($request->id);
    // ...
    Http::timeout(1)->get($user->feed_url);
    // ...
} catch (\Illuminate\Http\Client\ConnectionException $exception) {
    // 커넥션 발생시 에러 처리
}
```

유저 피드에 대한 응답이 지연되어지거나 에러가 발생한 경우에 에러 정보를 저장하고 여러 로직등을 실행하고 있습니다.

위와 같이 네트워크 딴에 문제가 생기 경우에 대한 `통합 테스트`를 하고 싶을 때, 문제가 발생합니다.

1. 실행 중에 다른 API를 호출해야 해서 모든 Request를 Fake로 잡을 수 없다는 문제
2. `Http::fake()`에서 강제로 Timeout을 일으키는 방법이 없음

위 두가지 문제 때문에 어떻게 테스트 해야지하는지에 대한 부분이 어려웠습니다.

이걸 해결하려고 여러가지 방법들을 시도해 보았습니다만, 제가 찾은 가장 깔끔한 방법을 설명하겠습니다.  
<del>테스트용 `route`를 만들어서 `sleep(10)`을 줘서 테스트 했던건 비밀..</del>

## 해결 방법

### 문제 1. 실행 중에 다른 API를 호출해야 해서 모든 Request를 Fake로 잡을 수 없다는 문제

왜인지 도큐먼트엔 해당 부분에 대한 설명이 없는데,

```php
Http::fake([
    'test1.com' => function () {
        return Http::response();
    },
    'test2.com' => function () {
        return Http::response();
    },
])
```

이와 같이 각각의 url에 대해서 `callback`으로 지정해 줄 수 있습니다.

이는 `sequence`로 처리할 때에도 동일하게 동일합니다.

### 문제 2. `Http::fake()`에서 강제로 Timeout(Error)을 일으키는 방법이 없음

HTTP 요청이 제대로 갔나 테스트할 때는 `assertSent`를 사용하거나, `assertNotSent`를 사용할 수 있지만,

애초에 타임아웃 같은 네트워크 레벨딴의 문제로 에러가 나는 경우를 통합 테스트에선 진행방법에 대한 설명이 없습니다.

해결 방법은 `callback` 으로 호출할 때, `\Illuminate\Http\Client\ConnectionException` 예외를 발생 시키면 됩니다.

```php
// tests/Feature/FeedControllerTest

$user = User::factory()->withFeedUrl()->create();
Http::fake(function () use ($user) {
    throw new \Illuminate\Http\Client\ConnectionException("cURL error 28: Operation timed out after 1000 milliseconds with 0 bytes received (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for {$user->feed_url}")
});
```

그럼 들수있는 의문점이 `callback` 부분에 `sleep(5)`을 넣으면 안되? 라고 할 수 있지만,  
Laravel 테스트 환경에서 http fake callback에서 sleep으로 멈출 때 test 실행 환경도 멈추게되니 결국은 5초 이후에 응답을 정상적으로 callback의 return값을 받게 됩니다.

다만, 주의할 점은 `after 1000 milliseconds` 에서 `1000`가 실제 서비스에선 실행때 마다 다르게 표시되니 에러 응답의 full text로 검증하는건 피하는 것이 좋습니다.

### 최종

결국 다른 API를 먼저 호출하고, 그 다음 타임아웃이 나는 경우는 아래와 같은 코드로 테스트 할 수 있습니다.

```php
// ...

$user = User::factory()->withFeedUrl()->create();

Http::fake([
    'first.com' => Http::response(),
    'second.com' => function () use ($user) {
        throw new \Illuminate\Http\Client\ConnectionException("cURL error 28: Operation timed out after 1000 milliseconds with 0 bytes received (see https://curl.haxx.se/libcurl/c/libcurl-errors.html) for {$user->feed_url}")
    },
]);

// ...
```

# 마치며

오랜만입니다! 거의 4달만의 글이네요.

첨삭을 한다고 했는데도 여전히 글은 중구난방이군요

한달의 하나 씩이라도 올릴려고 했는데, 진짜 이번 4달동안 미친듯이 바빳던것같습니다.<del>지금도 바쁘지만..</del>

길었던 병역도 끝이나고 자유롭나 했지만, 국방부는 절 놓아줘도 일은 절 놓아주질 않습니다ㅎㅎ

그래도 이번 작업할때 여지껏 공부한 내용들을 써먹는다는 느낌이여서 즐겁기도 했고, 한편으론 아직도 많이 부족함을 느끼네요ㅜ <del>특히 DevOps...</del>

이번 글은 각각의 API를 통신하는 과정에서 Timeout 등을 테스트 할 때 진짜 어떻게 해야할지에 대한 설명이 도큐먼트와 구글링 만으론 찾기가 힘들어서 여기 정리해봅니다.

영어로 써서 더 많은 사람들이 봤음하지만, 구글 번역기가 잘 해주겠죠..ㅎㅎ

다음번엔 HTTP 말고 좀 더 재미난 주제로 찾아 올 수 있었으면 합니다!