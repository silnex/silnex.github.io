---
layout: post
id: 27
title: "self vs static"
subtitle: "self vs static"
description: "self 와 static 키워드의 차이점"
type: "PHP"
created_at: "2022-09-13"
updated_at: "2022-09-13"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/self-vs-static.png"
order: 27
tags: ['php', 'self', 'static', 'tip']
comments: true
---

# TL;TR

> `self` 키워드는 코드가 선언된 곳의 '클래스'를 가르키고,  
> `static` 키워드는 코드 위치에 관계없이 '인스턴스'를 가르킵니다.

# self 와 static

PHP에서 class를 사용할 때 사용하는 `self` 키워드와 `static` 키워드의 차이에 대해서 알아봅니다.

다만, `self` 의 비해 `static` 키워드는 변수, 메소드에도 사용되지만, `self` 와 `static` 모두 사용되는
 - `const` 변수 호출
 - 메소드 안에서 `new` 키워드로 생성

위 경우에 대해서 알아봅니다.

---

## Constant

`const` 로 선언한 값을 접근할 때,

```php
class ParentClass
{
    const MESSAGE = "ParentClass constant"

    public function echoSelfConst() { echo self::MESSAGE; }
    public function echoStaticConst() { echo static::MESSAGE; }
}

class A extends ParentClass
{
    const MESSAGE = "A constant";
}
```

`ParentClass`를 상속 받는 `A` 클래스가 있을 때,  
`ParentClass`와 `A`에 각기 다른 `MESSAGE` 상수가 선언 되어 있습니다.

### self

```php
(new A)->echoSelfConst(); // "ParentClass constant"
```

`self` 키워드로 접근한 `self::MESSAGE`는 `A` 인스턴스에서 접근 하더라도,  
메소드가 선언된 곳인 `ParentClass`의 `MESSAGE` 값 `"ParentClass constant"`를 출력합니다.

### static

```php
(new A)->echoStaticConst(); // "A constant"
```

`static` 키워드로 접근한 `static::MESSAGE`는 `A` 인스턴스에서 접근했기에,  
인스턴스의 클래스인 `A`의 `MESSAGE` 값 `"A constant"`를 출력합니다.


### 하지만..

`self` 키워드를 사용하더라도, `A` 클래스에서 메소드를 오버라이드 하면,

```php
class A extends ParentClass
{
    const MESSAGE = "A constant";
    public function echoSelfConst() { echo self::MESSAGE; }
}

(new A)->echoSelfConst(); // "A constant"
```

`self::MESSAGE` 는 선언된 클래스인 `A`의 `MESSAGE` 값 `"A constant"`를 출력 합니다.

---

## return `self` or `static`

메소드에서 `self`와 `static`을 반환 하는 경우가 많은데,   
이 경우도 `const`와 마찬가지로 코드의 위치와 인스턴스의 차이에 따라 달라집니다.

```php
class ParentClass
{
    public function message() { echo "ParentClass message()"; }

    public function returnSelf(): self { return (new self); }

    public function returnStatic(): static { return (new static); }
}

class A extends ParentClass
{
    public function message() { echo "A message()"; }
}
```

`ParentClass`를 상속 받는 `A` 클래스가 있을 때,  
`ParentClass`와 `A`에 각기 다른 `message` 메소드가 선언 되어 있습니다.

## self

```php
(new A)->returnSelf()->message(); // "ParentClass message()"
```

이때 `new` 키워드로 생성된 인스턴스 `A` 에서 실행된 `returnSelf()->message()`는  
`ParentClass`의 `message()` 메소드를 실행해 `"ParentClass message()"` 를 출력하게 됩니다.

### static

```php

(new A)->returnStatic()->message(); // "A message()"
```

이때 `new` 키워드로 생성된 인스턴스 `A` 에서 실행된 `returnStatic()->message()`는  
`A`클래스에 `message()` 메소드를 실행해 `"A message()"` 를 출력하게 됩니다.


### 하지만..

하지만, `self` 키워드는 선언된 코드 위치에 영향을 받기에 `A` 클래스에서 `returnSelf`를 오버라이드한다면,

```php
class A extends ParentClass
{
    public function message() { echo "A message()"; }
    public function returnSelf(): self { return (new self); }
}

(new A)->returnSelf()->message(); // "A message()"
```
`returnSelf()->message()` 는 선언된 클래스인  
`A`의 `message()` 메소드를 실행하게 되어 `"A constant"`를 출력 합니다.

> [예제 코드](demo.php)

### 마치며

가끔 self와 static을 잘못 써서 런타임 에러가 종종 발생해 기억하려고 오랜만에 글을 씁니다..ㅎ

[return type](#return-self-or-static) 같은 경우엔 예제를 `get_class((new A)->returnSelf())` 이렇게 써서 설명할까 하다가 제가 겪었던 그대로 쓰는 게 좋을 것 같아서 메소드 호출로 작성해 봤는데 이해가 잘 되었으면 하네요!
