---
layout: post
id: 16
title:  "Laravel 8.28에 AsArrayObject와 AsCollection 커스텀 케스트 추가"
subtitle: "\"AsArrayObject and AsCollection Custom Casts Added in Laravel 8.28\" 변역글"
description: "\"AsArrayObject and AsCollection Custom Casts Added in Laravel 8.28\"의 번역글 입니다."
type: "Laravel"
created_at: "2021-02-17"
updated_at: "2021-02-17"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/laravel-as-array-object-casts-featured.png"
order: 16
tags: ['laravel', 'laravel-news', 'translate', 'short-article']
comments: true
---

이번 주 릴리즈된 Laravel 8.28에는 Taylor Otwell가 기여한 두가지 새로운 Custom 캐스트 타입인 AsArrayObject, AsCollection가 있습니다. ArrayObject는 기존의 array와 JSON 캐스트에 비해 몇가지 장점이 있습니다.

[PR 설명](https://github.com/laravel/framework/pull/36245#issue-572631845)에 이번 캐스트가 Laravel 8에 작업 속도를 높일 수 있는 내용들이 있습니다.

> Laravel에는 JSON 텍스트를 배열 또는 컬렉션으로 캐스팅해주는 기능이 있습니다.

```php
$casts = ['options' => 'array'];
```

> 하지만, 몇가지 단점을 가지고 있습니다. 먼저 아래 코드는 간단한 array 캐스팅으론 사용할 수 없습니다.

```php
$user = User::find(1);
$user->options['foo'] = 'bar';
$user->save();
```

> 개발자들은 동작 할 거라고 예상하지만 이 코드는 작동하지 않습니다. 이 'array' 캐스트에 의해 반환된 배열의 특정 값을 변경 하는 것은 불가능합니다. 그렇기에 아래와 같이 작성해야합니다.

```php
$user = User::find(1);
$user->options = ['foo' => 'bar'];
$user->save();
```

> 그러나 새로운 캐스트는 Eloquent의 커스텀 캐스트 기능을 활용해, 더 이해하기 쉬운 객체 관리 와 캐싱을 구현합니다. `AsArrayObject` 캐스트는 JSON 문자열을 PHP의 `ArrayObject` 인스턴스로 변환합니다. 이 클래스는 PHP의 스텐다드 라이브러리를 포함되어 있어 객체가 배열처럼 작동하도록 합니다. 이러한 접근 방식은 아래와 같은 코드를 가능하게 합니다.

```php
// 모델에 선언된 casts...
$casts = ['options' => AsArrayObject::class];

// options를 변경...
$user = User::find(1);
$user->options['foo']['bar'] = 'baz';
$user->save();

// ArrayObject는 array_map과 같은 함수에선 사용할 수 없습니다.
// 그렇기에 array나 collection으로 변화하여 사용해야합니다.
$user->options->toArray();
$user->options->collect();
```

# Learn More
[PR#36245](https://github.com/laravel/framework/pull/36245)를 확인해 자세한 내용을 보길 권합니다.
여기엔 배열 데이터에 대한 Laravel 캐스트의 현재 상대와 새로운 ArrayObject및 Collection 캐스트의 이점을 훌륭하게 설명하고 있습니다.
PHP 문서에는 객체가 배열로 작동하도록 허용하는 내장 [ArrayObject](https://www.php.net/manual/en/class.arrayobject.php) 클래스(> PHP 5)에 대해서 자세히 알아 볼 수 있습니다.

# 마치며
설 때 저번에 말했던 laravel debug mode rce에 대해서 조사해보고 글을 쓰려고 했는데 정말 푹 쉬었네요ㅋㅋㅋ

그래도 이번 개인 프로젝트중에 custom 캐스트를 다루는 부분이 있는데 이게 생각보다 어려운 와중에 레퍼런스를 찾고 있었는데 아-듀 훌륭한 레퍼런스가 공개 된것같습니다.