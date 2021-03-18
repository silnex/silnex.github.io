---
layout: post
id: 19
title: "Laravel Octane"
subtitle: "WOW. That's insane!"
description: "Swoole과 RoadRunner와 같은 환경에서 제공되는 개-빠른 Laravel Octane"
type: "Laravel"
created_at: "2021-03-18"
updated_at: "2021-03-18"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/laravel-octane.jpg"
order: 19
tags: ["laravel", "laracon-2021", "laravel-news", "translate"]
comments: true
---

> [원본글 - Laravel Octane](https://laravel-news.com/laravel-octane)

Taylor Otwell은 Laracon Online 2021에서 Laravel Octane을 처음 선보였습니다.  
Laravel Octane은 [Swoole](https://www.swoole.co.uk/)와 [RoadRunner](https://roadrunner.dev/)와 같은 환경을 활용해 고성능의 Laravel을 실행하는 first-party 패키지 입니다.

Octane은 데모에서 4개의 스레드를 사용해 동시에 50이상의 요청을 처리하는 "hello world"에서 초당 6,000개의 처리속도를 보여줬습니다.

<blockquote class="twitter-tweet"><p lang="en" dir="ltr">WOW.<br><br>With the Octane Swool server running, 6000+ requests being handled by the framework.<br><br>That&#39;s insane! <a href="https://t.co/FGYoghib2D">pic.twitter.com/FGYoghib2D</a></p>&mdash; Laracon Online (@LaraconOnline) <a href="https://twitter.com/LaraconOnline/status/1372251742251802624?ref_src=twsrc%5Etfw">March 17, 2021</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

Here's an example of the command used during the demo as a rough benchmark example:

아래 커맨드는 데모중에 사용한 밴치마크 커맨드중 예시 입니다. :

```bash
wrk -t4 -c50 http://localhost:8000/hello-world
```

# 어떻게 동작하는 겁니까?

Octane의 실행 방법은 Swoole나 RoadRunner와 같은 환경이 제공된 상태에서 Artisan 콘솔 커맨드(`octane:start`)으로 사용해 볼 수 있습니다. 또한 CPU에서 제공되는 환경에 따라 사용할 기술과 스레드 수를 지정할 수 있습니다.

```bash
artisan octane:start --workers=8 --server=roadrunner
```

Swoole와 RoadRunner는 CPU 코어 수를 기반으로 여러 Worker 프로세스를 Fork(분기)시켜 PHP를 보다 효율적으로 실행할 수 있는 코루틴(coroutine)을 사용합니다.

Swoole/RoadRunner를 사용하면 메모리에 올려진 바이트코드를 사용해 오버해드를 최소화 할 수 있는 OPcache와 함께 PHP 앱의 startup 효율성을 높일 수 있습니다.

PHP 워커는 요청과 요청 사이에 active 상태를 유지하면서 새로운 요청을 받을 대기를 합니다. ("x" 옵션을 통해 요청 후 순환(Cycle) 시킬 수 있습니다.(?))  

프레임워크의 startup 시간을 제거하면서 무거운 HTTP 워크로드가 많은 어플리케이션에 많은 개선 효과가 있습니다.

# 목적은 무엇입니까?

일반적인 어플리케이션은 Octane 데모에서 보여준 이상한 request-per-second를 요구하지는 않습니다.  
하지만, 고가용성의 PHP 애플리케이션이 필요한 경우는 매우 흔하기에, Laravel은 프레임워크 단에서 Swoole와 Octan과 같은 도구들을 지원하는 특별한 포지션에 있습니다.

이러한 기술들에 대한 Laravel의 First-party 지원은 PHP 어플리케이션 스케일링에 대한 최신 기법들을 적용할 때 좋은 돌파구가 될 것입니다. 이러한 노력으로 PHP 생태계가 많은 혜택을 누리게 될 것입니다.

# 이 다음은?

Taylor가 말했듯이 Octane은 곧(아마도 몇 주내로) 출시를 기대하세요!  
Laravel News는 Octane의 사전 출시 베타 버전이 될 최초 출시를 발표 할 것입니다.

그 동안 여러분들은 [RoadRunner](https://roadrunner.dev/docs/intro-about)와 [Swoole PHP](https://www.swoole.co.uk/how-it-works)를 사용해 보며 익숙해지세요!

저희는 더 많은 Octane 소식을 기대하겠습니다!

# 마치며

예전에 Swoole를 사용해보면서 적은 레퍼런스와 오래된 문서들 때문에 많은 삽질을 한적이 있는데(결국은 Node로..) 이번에 Laravel에서 직접 지원한다는 이야기를 듣고 솔직히 많이 놀랐습니다!

이번 지원 발표로 더 많은 레퍼런스와 커뮤니티에서 활발한 이야기가 오고 갔으면 좋겠네요!