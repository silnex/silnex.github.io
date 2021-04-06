---
layout: post
id: 21
title: "Laravel Octane Beta 릴리즈"
subtitle: ""
description: "Swoole과 RoadRunner 환경에서 제공되는 개-빠른 Laravel Octane"
type: "Laravel"
created_at: "2021-04-07"
updated_at: "2021-04-07"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/laravel-octane.png"
order: 21
tags: ["laravel", "laravel-octane", "laravel-news", "translate"]
comments: true
---

> [이전 Laravel Octane 소개글](/blog/laravel-octane)

> [원본글 - The Laravel Octane Beta is Available](https://laravel-news.com/laravel-octane-beta)

Swoole와 RoadRunner 환경에서 고성능 Laravel을 실행할 수 있는 패키지인 [Laravel Octane](/blog/laravel-octane) 베타 버전이 출시되었습니다!

Laracon 데모에서 Octane이 6,000 req/s를 보여줬습니다! 아직 베타 버전이지만, Laravel팀은 Jetstream, Horizon, Spark 등과 같은 패키지와 호환성을 지키기위해 노력하고 있습니다.

<blockquote class="twitter-tweet"><p lang="en" dir="ltr">Octane compatibility on first-party packages is looking good. ✅ <a href="https://t.co/RA1yV0y03t">pic.twitter.com/RA1yV0y03t</a></p>&mdash; Taylor Otwell 🪐 (@taylorotwell) <a href="https://twitter.com/taylorotwell/status/1379145886677266432?ref_src=twsrc%5Etfw">April 5, 2021</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

그리고 우리는 Laravel 커뮤니티에서 [외부 패키지에 대한 Octane 테스트 중](https://twitter.com/themsaid/status/1379101925250043906)입니다. 강조하지만, Octane은 베타 상태입니다. [지금하고 있는 노력](https://twitter.com/taylorotwell/status/1378025903163342853)들은 Laravel 자체와 Octane이 정상적으로 작동하는지 확인하는 것이 목표 입니다.

A recurring theme we've noticed from the early feedback is that service providers need to use the $app within the closure instead of the $this->app instance:
초기 피드백을 통해 알게 된 반복적 테마는 서비스 공급업체가 $this->app 인스턴스 대신 $app을 클로즈업 내에서 사용해야 한다는 것입니다.

초반 반복적인 피드백을 통해 알게된 것은 service provider가 `$this->app` 인스턴스 대신 `$app`을 클로저 안에서 사용해야 한다는 것 입니다.:

```php
// Octane 에서는 동작하지 않습니다.
$this->app->bind(CacheManager::class, function () {
    return new CacheManager($this->app);
});

// 대신에 전달되는 `$app`을 사용할 수 있습니다.
$this->app->bind(CacheManager::class, function ($app) {
    return new CacheManager($app);
});
```

Octane 패키지에 대해 자세히 알아보고 싶다면 [Github에서 소스코드](https://github.com/laravel/octane)를 확인할 수 있습니다.  
Octane과 다른 어플리케이션이나 패키지를 테스트하면서 문제가 생긴다면 자세한 정보와 함께 리포트를 남겨주세요.  
Octane의 도큐먼트에 참고 섹션에는 패키지 관리자가 Octane과 호환되도록 돕는 세부 정보들이 포함되어 있습니다.

# 마치면서
기대를 한몸에 받고 있는 Octane이 드디여 공개가 되었습니다!

자기전 가볍게 laravel news를 읽다가 발견해서 후다닥 번역하다보니 의역이 넘치는 것 같네요ㅎㅎ;

다시 본론으로 돌아가면, 처음 공개 되었을 땐 대부분의 지원되는 패키지가 대부분 Unknown 으로 되어있었는데 그 몇시간 사이에 모든 패키지가 지원된다고 변경 되어있네요;; ㄷㄷ

그리고 트위터에서 Debugbar라든지 그외 라라벨에서 많이 사용되는 패키지들의 지원 여부가 계속 올라오고 있네요!

또 새로운 것 들을 테스트해 볼 생각에 가슴이 두근두근 하네요!