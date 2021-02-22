---
layout: post
id: 17
title:  "PHP Enums"
subtitle: "PHP 8.1 RFC를 통과한 PHP Enums"
description: "PHP 8.1 RFC를 통과한 PHP Enums"
type: "PHP"
created_at: "2021-02-22"
updated_at: "2021-02-22"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/php-enum.png"
order: 17
tags: ['php', '8.1', 'rfc', 'enum', 'new-feature']
comments: true
draft: true
---

PHP에서는 기존까지 별도의 Enum class를 지원하지 안호 따로 아래 처럼 class의 const(상수)를 선언해 사용했었습니다.

```php
class Status
{
    const NOT_WORKING = 0;
    const WORKING = 1;
}
```

하지만 며칠 전(2월 17일) 투표를 완료한 [PHP rfc:enumerations](https://wiki.php.net/rfc/enumerations)에서 최종적으로  
Enum(열거형) 기능을 PHP 8.1에 추가되기로 결정 되었습니다.

근본 적으로 `enum`은 `class` 와 동일하게 취급되며, 상속, 인터페이스 등의 기능을 공유합니다.

몇가지 예시와 함께 enum의 기능에 대해서 알아 보겠습니다!

> 아래 예시는 [stitcher.io](https://stitcher.io/blog/php-enums) 블로그에서 발췌 했습니다.

# PHP Enum

사용 방법은 `class`를 선언과 비슷합니다. `enum` 키워드 옆에 enum의 이름을 쓰고,  
각각의 enum 들은 `case`로 선언 할 수 있습니다.

```php
enum Status
{
    case DRAFT;
    case PUBLISHED;
    case ARCHIVED;
}
```

이렇게 보면 기존의 `class`를 이용하는 것과 단순히 키워드만 다른 것 처럼 보지만,  
가장 큰 장점은 이를 타입처럼 사용할 수 있다는 것 입니다.

```php
class BlogPost
{
    public function __construct(
        public Status $status, 
    ) {}
}

$post = new BlogPost(Status::DRAFT);
```

만약 `$status` 인자에 다른 `int`이나 `string`등이 들어가게 되면 `TypeError`를 출력하게 됩니다.

# Enum method

기본적으로 `enum`은 class로 취급하기에, `enum` 안에 메소드를 넣을 수 있습니다!

```php
enum Status
{
    case DRAFT;
    case PUBLISHED;
    case ARCHIVED;

    public function color(): string
    {
        return match($this) 
        {
            Status::DRAFT => 'grey',   
            Status::PUBLISHED => 'green',   
            Status::ARCHIVED => 'red',   
        };
    }
}
```

여기서 중요한 건 `Status::SOMETHING`의 값이 '상수'나 '스칼라'값이 아닌 객체인겁니다.  
즉, 이를 이용해 아래처럼 사용할 수 있습니다.

```php
$status = Status::ARCHIVED;

$status->color(); // 'red'
```

또한 Enum method 는 `static method`로 사용이 가능하고 `self`키워드 또한 사용할 수 있습니다.

# Enum values - 일명 "Backed enums"

"지원되는" 열거형(?) 혹은 "유동적인" 열거형(?) 처럼 어떻게 해석 해야할지 애매하지만,  
`enum`의 각 `case`가 별도의 값을 가질 수 있습니다.

이와 반대로 아무런 값을 [위의 예제](#php-enum) 처럼 값을 가지지 않는 열거형을 "Pure enums"라고 부릅니다.

이와 같은 것들은 데이터베이스의 연결 정보 등을 다룰 때 유용하게 사용 될 수 있습니다.

```php
enum Status: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
// OR
enum Status: int
{
    case DRAFT = 1;
    case PUBLISHED = 2;
    case ARCHIVED = 3;
}
```

Enum의 타입은 