---
layout: post
id: 22
title: "Laravel HTTP 비동기 요청"
subtitle: "도큐먼트에도, PHP Doc에도 없는 따끈따끈한 기능"
description: "HTTP Client async request"
type: "Laravel"
created_at: "2021-05-23"
updated_at: "2021-05-23"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/async.png"
order: 22
tags: ["laravel", "http", "async", "new-feature"]
comments: true
---

기존의 PHP에서 `curl_exec`나 `file_get_contents` 등을 이용해 요청하는 Http 요청은 모두 동기적인 방식이다보니  
여러개의 요청을 할 때는 순서대로 하나하나 씩 처리를 하다보니 많은 시간이 소요됩니다.

예를들어 아래 처럼 3가지의 요청이 있다고 생각해봅시다.

1. 7초 짜리 요청
2. 10초 짜리 요청
3. 8초 짜리 요청

이때 Sync(동기) 방식은 7초 + 10초 + 8초 이렇게 총 25초가 걸리게됩니다.

하지만 Async(비동기) 방식은 3가지의 요청을 동시에 처리하기에 가장 오래걸리는 `2. 10초 짜리 요청`이 완료되는 시점에 끝나게 됩니다.

# Laravel Http Client

[비동기 기능을 추가한 PR](https://github.com/laravel/framework/pull/36948)

Laravel 7 버전 이상부터 [`Http` 파사드](https://laravel.kr/docs/http-client)를 사용해서
쉽게 Http 요청 할 수 있게 되었으며, 비동기 요청 또한 지원합니다.
~~(정확히는 Guzzle에서 지원하는거지만...)~~

다만 아직 laravel-news나 문서 상에서 언급도 없고 PHP Doc등 정적분석 도구를 위한 주석도 없기에
사용하려면 PR를 직접 보지않는 이상 어려워 여기에 정리합니다.

## 동기(Sync) 요청

기본적인 동기요청은 아래와 같이 할수 있습니다.

```php
$response = Http::get('https://silnex.kr/', [
    'i AM' => 'silnex',
]);
```

다만, 위에서 언급했다 싶이 로직상에서 이러한 요청이 많아진다면, 걸리는 시간은 요청에 횟수 만큼 늘어납니다.

## 비동기(Async) 요청

비동기 요청은 다음과 같이 할 수 있습니다.

```php
$promise = Http::async()->get($url);
```

위 코드를 실행하면, `GuzzleHttp\Promise\Promise` 클래스가 반환되면서, 실제 요청은 이루어 지지 않습니다.  
이때, 요청 후 원하는 동작을 실행하고 싶다면 `then()` 메소드를 이용하면됩니다.

```php
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Client\Response;

$promise = Http::async()->get($url)->then(
    function (Response|TransferException $response) {
        // work something
    }
);

// do something

$response = $promise->wait();
```

`$promise`를 선언 후 필요한 시점에 `wait()`으로 Response를 받을 수 있습니다.

### 음...? Wait...

먼가... 비동긴데 비동기 같지가 않다...

내가 기대한 비동기는 여러개의 요청을 동시에 날려서 응답 받는건데..


## Http::pool() 메소드

동시에 여러 요청을 **병렬적(Parallel)**으로 보낼 땐 `pool()` 메소드를 사용하면 됩니다.

다만, [PR](https://github.com/laravel/framework/pull/36948)에서 이야기 중인 것처럼 아직 더 나은 방향으로 패치를 진행하려는 것 같습니다.

```php
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;

$start = time();

$responses = Http::pool(fn (Pool $pool) => [
    $pool->get('localhost/?sleep=7')->then(function (Response|TransferException $response) {
        echo time() - $start . ' ';
    }),
    $pool->get('localhost/?sleep=10')->then(function (Response|TransferException $response) {
        echo time() - $start . ' ';
    }),
    $pool->get('localhost/?sleep=8')->then(function (Response|TransferException $response) {
        echo time() - $start . ' ';
    }),
]);

// Output: 7 8 10
```

## 마치면서

아무 생각 없이 Guzzle Wrapper인 Http 도 async 되지 않을까 하고 소스를 보는 중에 async를 지원하는 부분을 찾아서 자기 전에 짧게나마 쓰려고 했지만, Parallel 과 Async의 차이점을 염두에 두지 않고 쓰다 보니 중구난방이 되어벼렸네요;

### PS

아 참고로 Guzzle에서 병렬적(Parallel)으로 요청을 보내려면 아래처럼 쓸 수 있습니다.

```php
$client = new \GuzzleHttp\Client;

$start = time();

$promises = [
    '1' => $client->getAsync('localhost/?sleep=7')->then(fn () => dump(time() - $start)),
    '2' => $client->getAsync('localhost/?sleep=10')->then(fn () => dump(time() - $start)),
    '3' => $client->getAsync('localhost/?sleep=8')->then(fn () => dump(time() - $start)),
];

$responses = \GuzzleHttp\Promise\Utils::settle($promises)->wait();
// Output: 7 8 10
```