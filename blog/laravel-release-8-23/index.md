---
layout: post
id: 12
title:  "[번역] 라라벨 8.23 릴리즈"
subtitle: "Laravel 8.23 Released 글의 번역글입니다."
description: "[번역] 라라벨 8.23 릴리즈"
type: "Laravel"
created_at: "2021-01-21"
updated_at: "2021-01-22"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/laravel8.jpg"
order: 12
tags: ['laravel', 'laravel-news', 'release', 'translate', 'short-article']
comments: true
---

> [원본글 - Laravel 8.23 Released](https://laravel-news.com/laravel-8-23-0)

Laravel 팀에서 laravel 8.23을 발표 했습니다.  
이번 발표에는 새로운 `sole()` 쿼리 빌더 메소드와, `throw_if`와 `throw_unless`의 개선사항과 최신 변경사항들이 8.x 브랜치에 추가되었습니다.

# `sole` 메소드 쿼리 빌더에 추가

Sole was added to the query builder, which was described in the initial pull request as follows:

Sole이 쿼리빌더에 추가되었습니다. 이는 [pull 요청](https://github.com/laravel/framework/pull/35869)에 다음과 같이 설명 되어있습니다.

Django의 [`get()`](https://docs.djangoproject.com/en/3.1/topics/db/queries/#retrieving-a-single-object-with-get)과 Rails의 [`.sole`과 `find_sole_by`](https://github.com/rails/rails/blob/master/activerecord/CHANGELOG.md) 과 유사합니다.

`DB::table('products')->where('ref', '#123')->sole()`는 기준과 일지하지 않는 유일한 레코드를 반환합니다. 레코드가 발견되지 않으면, `RecordsNotFoundException` 예외를 발생 시키고, 여러개의 레코드가 발견되면 `MultipleRecordsFoundException` 예외를 발생시킵니다.

이 특징은 초기에 `Mohamed Said`의 의해 기여 되어졌고, `Mior Muhammad Zaki` 와 `Rodrigo Pedra Brum`의 의해 내용이 업데이트 되었습니다.  
이 기능과 관련된 커밋 및 풀 요청에 대한 자세한 내용은 릴리즈 정보를 참조하세요.

# `throw_if`와 `throw_unless`의 기본 파라미터 추가

`Sjors Ottjes`가 메시지를 두 번째 매개 변수로 전달하는 기능을 제공했습니다.

```php
// Currently:
throw_if(
    $sometingIsWrong,
    new RuntimeException('something wrong with user '.$user->id)
);

// With this PR:
throw_if(
    $sometingIsWrong,
    'something wrong with user '.$user->id
);

// If the message is an existing class, that will be used as the exception
throw_if($somethingIsWrong, LogicException::class);
```

# 릴리즈 노트

아래의 새로운 기능 및 업데이트의 전체 목록은 GitHub [8.22.0과 8.23.0](https://github.com/laravel/framework/compare/v8.22.0...v8.23.0)에서 차이점을 확인할 수 있습니다.   
다음 릴리스 정보는 [변경 로그](https://github.com/laravel/framework/blob/208c3976f186dcdfa0a434f4092bae7d32928465/CHANGELOG-8.x.md#v8230-2021-01-19)에서 직접 가져온 것입니다.

# v8.23.0
## Added
 - `Illuminate\Database\Concerns\BuildsQueries::sole()` 추가 ([#35869]((https://github.com/laravel/framework/pull/35869)), [29c7dae](https://github.com/laravel/framework/commit/29c7dae9b32af2abffa7489f4758fd67905683c3), [#35908]((https://github.com/laravel/framework/pull/35908)), [#35902]((https://github.com/laravel/framework/pull/35902)), [#35912]((https://github.com/laravel/framework/pull/35912)))
 - `throw_if` / `throw_unless에` 기본 매개 변수 추가 ([#35890]((https://github.com/laravel/framework/pull/35890)))
 - TeamSpeak3 URI 체계에 대한 유효성 검사 지원 추가 ([#35933]((https://github.com/laravel/framework/pull/35933)))

## Fixed
 - 인라인 블레이드 클래스 구성 요소의 추가 공간 수정 ([#35874]((https://github.com/laravel/framework/pull/35874)))
 - 요청 제한 미들웨어의 고정 직렬화 ([f3d4dcb](https://github.com/laravel/framework/commit/f3d4dcb21dc66824611fdde95c8075b694825bf5), [#35916]((https://github.com/laravel/framework/pull/35916)))
## Changed
 - 특정 시더를 테스트에 사용하도록 허용 `Illuminate\Foundation\Testing\RefreshDatabase::migrateFreshUsing()` ([#35864]((https://github.com/laravel/framework/pull/35864)))
 - Collection과 LazyCollection의 reduce 메서드에서도 `$key`를 클로저에 전달합니다. ([#35878](https://github.com/laravel/framework/pull/35878))

# 마치며
릴리즈노트에 글을 보면 이번글의 `sole()` 메소드 처럼 꿀팁들이 간혹 들어 있곤합니다.  
근데 이 글을 번역하기 시작하면 메번 릴리즈 노트 때 마다 글을 번역해야 될 것 같은 느낌이 들어 살짝 피해 왔는데 이번 `sole()` 메소드가 나름 참신해 번역해 보았습니다.   
~~사실 글 주제가 떨어진건 안 비밀~~