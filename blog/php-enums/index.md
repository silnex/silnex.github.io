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

# PHP Enum
PHP에서는 기존까지 별도의 Enum class를 지원하지 안호 따로 아래와 같이 class의 const(상수)를 선언해 사용했었습니다.

```php
class Status
{
    const NOT_WORKING = 0;
    const WORKING = 1;
}

if ($status === Status::WORKING) {
    // ...
}
```

하지만 며칠 전 투표를 완료한 [PHP rfc:enumerations](https://wiki.php.net/rfc/enumerations)에서 최종적으로 PHP 8.1에 추가되기로 결정 되었습니다.

# PHP Enum

RFC에 올라온 Enum은 "~ 상황에서 어떻게 동작된다" 라고 정리된 내용 중에서 중요하다고 생각되는 내용들만 발췌해 정리한 내용들입니다.

## Basic enumerations

새로운 PHP Enum은 다음과 같이 선언할 수 있습니다.

```php
enum Status
{
    case Hearts;
    case Diamonds;
    case Clubs;
    case Spades;
}
```

별도의 값 없이 선언 되어지며, 아래와 같이 사용할 수 있습니다.

```php
function pick_a_card(Suit $suit) { ... }
 
$val = Suit::Diamonds;
 
pick_a_card($val);        // OK
pick_a_card(Suit::Clubs); // OK
pick_a_card('Spades');    // TypeError: pick_a_card(): Argument #1 ($suit) must be of type Suit, string given
```

기본적으로 각각의 케이스들은 스칼라(scalar) 값을 갖지 않습니다. 즉 `Suit::Hearts`는 `0`이 아닙니다.  
다만, 각 케스트는 해당 이름의 단일 객체로 취급되며 다음과 같이 사용됩니다.  
그렇기에 `<`과 `>`와 같은 비교는 항상 `false`를 반환합니다.

```php
$a = Suit::Spades;
$b = Suit::Spades;
$c = Suit::Diamonds;

$a === $b; // true

$a instanceof Suit;  // true

$a < $b; // false
$b < $c; // false
```

그리고 `enum`은 클래스로서 취급되며, 각각의 케이스는 `read-only`의 `name` 속성(property)를 가집니다.

```php
print Suit::Spades->name;
// prints "Spades"
```

## Backed Enums (지원 열거형)

물론 이번에 추가되는 Enum에도 스칼라를 직접 정의 할 수 있으며, 다음과 같이 정의 할 수 있습니다.

```php
enum Suit: string {
  case Hearts = 'H';
  case Diamonds = 'D';
  case Clubs = 'C';
  case Spades = 'S';
}
```

여기서 `enum`에 타입을 지정하여 type-safe하게 사용할 수 있습니다.  

그런데 여기서 타입은 `int|string`과 같이 union 타입을 지원하지 않습니다.  
또한 "지원 열거형"은 모든 케이스가 값을 가져야 하며, 자동으로 생성되지 않고, 고유 한 값을 가져야합니다.  

그리고, `1+1`과 같은 상수는 가능하지만 `1+SOME_CONST`와 같은 상수 표현식은 지원되지 않습니다.  
다만, 구현이 어려우나 향우에 지원될 수 있다고 합니다.

이렇게 값을 가지게 된다면, `read-only`의 `value` 속성(property)를 가집니다.

```php
print Suit::Clubs->value;
// Prints "C"
```

### from 과 tryFrom

enum에 값이 정해진 `backed enum`은 `from(int|string): self` 메소드와 `tryFrom(int|string): ?self`으로  
케이스를 찾을 수 있으며, 다음과 같이 사용할 수 있습니다. 

```php
$record = get_stuff_from_database($id);
print $record['suit'];
 
$suit =  Suit::from($record['suit']);
// Invalid data throws a ValueError: "X" is not a valid scalar value for enum "Suit"
print $suit->value;
 
$suit = Suit::tryFrom('A') ?? Suit::Spades;
// Invalid data returns null, so Suit::Spades is used instead.
print $suit->value;
```

## Enum method

Enum은 기본적으로 class 취급되기에 메소드를 추가할 수 있으며, `match`와 함께 사용하면 그 시너지가 더욱 살아납니다.

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

$status = Status::ARCHIVED;

$status->color(); // 'red'
```

