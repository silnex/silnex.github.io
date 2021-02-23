---
layout: post
id: 17
title: "PHP Enums"
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
tags: ["php", "8.1", "rfc", "enum", "new-feature"]
comments: true
---

PHP에서는 기존까지 별도의 Enum class를 지원하지 않아 아래의 예시 처럼 class의 const(상수)를 선언해 사용했었습니다.

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

> 아래 예시는 [stitcher.io](https://stitcher.io/blog/php-enums)와 [php.watch](https://php.watch/versions/8.1#enums)에서 발췌 했습니다.

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

여기서 중요한 건 `Status::SOMETHING`의 값이 '상수'나 '스칼라'값이 아닌 '객체'입니다.

즉, 이를 이용해 아래처럼 사용할 수 있습니다.

```php
$status = Status::ARCHIVED;

$status->color(); // 'red'
```

또한, `enum` 객체는 `read-only` 속성인 `name`을 가지고 있어 이를 통해 `enum`의 이름을 가져올 수 있습니다.

```php
echo Status::ARCHIVED->name; // print 'ARCHIVED'
```

마지막으로, Enum의 이름은 반드시 고유해야 하지만 대소문자를 구분합니다.

```php
enum Status
{
    case foo;
    case Foo;
    case fOO;
}
```

# Enum values - 일명 "Backed enums"

"지원되는" 열거형(?) 혹은 "유동적인" 열거형(?) 처럼 어떻게 불러야할지 애매하지만,  
`enum`의 각 `case`가 별도의 값을 가질 수 있습니다.

이와 반대로 아무런 값을 [위의 예제](#php-enum) 처럼 값을 가지지 않는 열거형을 "Pure enums"라고 부릅니다.

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

다만, Enum의 타입은 `string` 혹은 `int` 만 가능하며, `int|string` 처럼 union 타입은 가질 수 없습니다.

## "Backed enums"의 직렬화

각각의 케이스는 객체이기 때문에 위에서 이야기한 `name` 속성을 가지고 있습니다.  
하지만 "Backed Enum"은 `read-only`의 속성인 `value`도 가지고 있어, 아래와 같이 선언된 값을 가져올 수 있습니다.

```php
$value = Status::PUBLISHED->value; // 2
```

또한 기본적으로 제공되는 `from` 메소드를 이용해 값으로 부터 `enum` 객체를 가져올 수 있습니다.

```php
$status = Status::from(2); // Status::PUBLISHED
```

물론 `tryFrom` 메소드를 이용해 그 반대도 가능합니다.

```php
$status = Status::tryFrom('PUBLISHED'); // Status::PUBLISHED
```

하지만 각 메소드는 값이 없을때 다르게 반응하는데, `tryFrom` 은 `null`를 반환하지만,  
`from`은 `ValueError` 예외를 발생 시킵니다.

```php
$status = Status::from('unknown'); // ValueError
$status = Status::tryFrom('unknown'); // null
```

# Enum 값 리스트

`Enum::cases()` static 메소드를 이용해서 사용 가능한 `case` 들이 담긴 array을 가져올 수 있습니다.

```php
Status::cases();

/* [
    Status::DRAFT,
    Status::PUBLISHED,
    Status::ARCHIVED
] */
```

만약 "Backed Enums" 이라면 array의 키로 value를 넣어 반환 됩니다.

```php
Status::cases();

/* [
    'draft' => Status::DRAFT,
    'published' => Status::PUBLISHED,
    'archived' => Status::ARCHIVED,
] */
```

# Enum과 비교 연산자

Enum은 객체이지만, 싱글턴 처럼 동작되기에 아래처럼 비교 될 수 있습니다.

```php
$statusA = Status::PENDING;
$statusB = Status::PENDING;
$statusC = Status::ARCHIVED;

new ObjectClass === new ObjectClass; // false
$statusA === $statusB; // true
$statusA === $statusC; // false
$statusC instanceof Status; // true
```

# array의 키로써의 Enum

enum은 열거형이기 때문에 배열의 키로 사용할 수 없습니다.

```php
$list = [
    Status::DRAFT => 'draft', // Fatal error: Uncaught TypeError ...
];
```

하지만 [이 RFC](https://wiki.php.net/rfc/object_keys_in_arrays)가 통과 된다면, 가능해질 수 도 있습니다.


# 클래스와의 차이점

|     |Class|Enum|
|---:|:---:|:---:|
|문법|`class Foo {}`|`enum Foo {}`|
|속성 (property)|✅|❌|
|static 속성|✅|❌|
|메소드|✅|✅|
|static 메소드|✅|✅|
|오토로딩|✅|✅|
|`new` 키워드|✅|❌|
|인터페이스 사용|✅|✅|
|상속 `Foo extends Bar`|✅|❌|
|마법 상수 `::class`, `__CLASS__`, etc|✅|✅|
|마법 메소드 `__get`, `__set`, etc|✅|❌|
|비교 `Foo === Foo`|`false`|`true`|
|Trait |✅|✅ (단, 속성 없이)|

# 그 외 추가 되는 기능들
## `enum_exists` 함수가 추가됩니다.
```php
function enum_exists(string $enum, bool $autoload = true): bool {}
```

## Reflection 클래스가 추가됩니다.

`ReflectionEnum`, `ReflectionEnumUnitCase`, `ReflectionEnumBackedCase` 등의 [Reflection 클래스](https://www.php.net/manual/en/book.reflection.php)가 추가됩니다.

## Enum 과 관련된 `UnitEnum`, `ScalarEnum` 인터페이스가 추가됩니다.

Enum은 기본적으론 클래스로 취급되기에 인터페이스를 가질 수 있습니다.  
그에 따른 Enum과 관련된 PHP 기본 인터페이스가 추가됩니다.

```php
interface UnitEnum {
    public static function cases(): array;
}

interface ScalarEnum extends UnitEnum {
    public static function from(int|string $value): static;
    public static function tryFrom(int|string $value): ?static;
}
```


# 마치며

이번 글에선 추가되는 Enum의 기능을 위주로 담았습니다만, 메직메소드를 사용하지 못한다던지 많은 제약사항들이 존재합니다.
그치만, 2021년 11월에 릴리즈 되기도하고, 아직 구현단계이기에 어떻게 진행될지는 계속 지켜봐야 할 것 같습니다.  
그리고, [오브젝트 array 키 RFC](https://wiki.php.net/rfc/object_keys_in_arrays)와 같은 다른 RFC의 진행에따라 구현이 달라질 겁니다.

하지만 이번 기능에 대해서 들여다볼 가치는 충분하다고 생각합니다.

# 참조

1. [https://wiki.php.net/rfc/enumerations](https://wiki.php.net/rfc/enumerations)
2. [https://stitcher.io/blog/php-enums](https://stitcher.io/blog/php-enums)
3. [https://php.watch/versions/8.1](https://php.watch/versions/8.1#enums)