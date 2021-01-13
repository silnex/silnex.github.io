---
layout: post
id: 10
title:  "기능(Fixture) 테스트 VS 유닛(Unit) 테스트"
subtitle: "Laravel의 Fixture 테스트와 Unit 테스트의 다른점"
description: "기능(Fixture) 테스트와 유닛(Unit) 테스트의 차이점을 확실히 하고, 개인적으로 쌓인 유닛 테스트에 대한 오해를 풀어 보려고 합니다."
type: "Laravel"
created_at: "2021-01-13"
updated_at: "2021-01-13"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/test.jpg"
order: 10
tags: ['laravel', 'tip', 'test', 'phpunit', 'short-article']
comments: true
---
이번 글을 쓰게된건 매번 기능(Fixture) 테스트와 유닛(Unit) 테스트의 차이점을 확실히 하고,  
개인적으로 쌓인 유닛 테스트에 대한 오해를 풀어 보려고 합니다.

# Laravel 코드상의 차이점
Laravel 에선 다음과 같은 TEST 디렉토리 구조를 가집니다.
```
tests`
  └ Fixture
    └ ExampleTest.php
  └ Unit
    └ ExampleTest.php
  └ CreatesApplication.php
  └ TestCase.php
```

기능 테스트와 유닛 테스트를 나눠 놓고 진행하는데, 두 테스트의 가장 큰 차이는 확장 하는 부모 클래스인데,  
기능 테스트는 `Tests\TestCase`를 상속 받고,   
유닛 테스트는 `PHPUnit\Framework\TestCase`의 테스트를 상속 받습니다.

이 두 `TestCase`의 차이는 Laravel의 `bootstrap`과정을 거치냐의 차이이고, 이 때문에 유닛 테스트의 `TestCase`는 Laravel의 helper, ORM 등을 사용할 수가 없습니다.

하지만 `bootstrap`과정이 없기 때문에 그만큼 테스트 속도가 빠르다는 장점도 있죠.

# 의미론적 차이점

## 기능 테스트
기능 테스트는 "종단간의 테스트" 즉, `request`와 `response`의 테스트를 의미합니다.

즉, `/post/1/edit`에 글을 작성한 `User`만 들어갈 수 있다. 기능이 있고 이러한 기능을 테스트를 하는 경우에 기능 테스트를 만드는 것입니다.

## 유닛 테스트
유닛 테스트는 "단위 테스트" 라고 번역 되는 만큼 가장 작은 테스트를 의미합니다.

예를 들어, `Post`의 `password`가 설정 되어있을 때 `isHasPassword()` 값이 `true` 여야 하는 이런 작은 테스트 들을 말합니다.

## 마치며
저의 경우엔 코드상의 차이만으로 받아드리고, 저는 기능 테스트에는 `Tests\TestCase`를 상속받는 테스트가,  
유닛 테스트에는 `PHPUnit\Framework\TestCase`를 상속받는 테스트가 들어야한다 라는 작은 오해(?)가 생겨 버렸습니다.

하지만 아시다 싶이 Laravel의 기능을 테스트한다는 것에서 Laravel의 helper 들을 안 쓸 수가 없습니다.

그러다보니 점점 기능 테스트 디렉터리만 커지다 보니 이게 맞나 싶어 찾아본 내용들을 정리해 보았습니다.

그리고 막상정리 하다보니 테스트를 가볍게 유지하면서 Laravel의 helper, orm등을 쓰는 방안들을 찾을 수 있었습니다. 이러한 부분도 나중에 살짝 다뤄 볼 수 있었음 좋겠습니다.