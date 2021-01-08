---
layout: post
id: 8
title:  "PHP key를 가진 배열의 spread 연산자 RFC"
subtitle: "PHP key를 가진 배열의 spread 연산자에 작동 방식에 대한 RFC입니다."
description: "PHP key를 가진 배열의 spread 연산자에 작동 방식에 대한 RFC입니다."
type: "Laravel"
created_at: "2021-01-08"
updated_at: "2021-01-08"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/unpacking.png"
order: 8
tags: ['php', 'rfc', 'array', 'operator', 'translate', 'short-article']
comments: true
---

> 기존의 글을 그대로 번역하지 않고 제가 필요한 부분을 추가하고, 재편집했습니다. 원본 RFC은 [여기](https://wiki.php.net/rfc/array_unpacking_string_keys)서 확인하실 수 있습니다.

# PHP Array spread operator
기존 5.6 부터 함수 인자를 전달([rfc](https://wiki.php.net/rfc/argument_unpacking)) 할 때 사용되던 spread 연산자(`...`)가 
7.4에서 부턴([rfc](https://wiki.php.net/rfc/spread_operator_for_array)) Array에 사용이 가능해졌습니다.

아래의 예제 처럼 배열안에 `[...[1,2,3]]` 과 같이 입력되면, `[1,2,3]` 이렇게 전개해주는 연산자입니다.
```php
$arr1 = [1, 2, 3];
$arr2 = [...$arr1]; //[1, 2, 3]
$arr3 = [0, ...$arr1]; //[0, 1, 2, 3]
$arr4 = array(...$arr1, ...$arr2, 111); //[1, 2, 3, 1, 2, 3, 111]
$arr5 = [...$arr1, ...$arr1]; //[1, 2, 3, 1, 2, 3]
 
function getArr() {
  return ['a', 'b'];
}
$arr6 = [...getArr(), 'c']; //['a', 'b', 'c']
 
$arr7 = [...new ArrayIterator(['a', 'b', 'c'])]; //['a', 'b', 'c']
 
function arrGen() {
	for($i = 11; $i < 15; $i++) {
		yield $i;
	}
}
$arr8 = [...arrGen()]; //[11, 12, 13, 14]
```

다만, `['key' => 'value']`처럼 string key를 가지는 Array를 spread 연산자를 통해 전개하려고 하면 다음과 같은 오류가 발생합니다.
```bash
FATAL ERROR Cannot unpack array with string keys on line number 3
```

이에 대해 이번 [RFC](https://wiki.php.net/rfc/spread_operator_for_array)는 `['key' => 'value']`의 배열도 사용 가능하게 하자는 의견입니다.

## 제안
기존에 PHP의 함수인 `array_merge()`의 기능과 거의 유사한 기능을 한다고 합니다.

```php
$array = [...$array1, ...$array2];
// 아래와 동일한 결과 값을 가짐:
$array = array_merge($array1, $array2);
```

이는 만약 뒤에 `$array2`의 동일한 `key`를 가진 값이 있다면, 값이 덮어씌워지는 기능을 합니다.
```php
$array1 = ["a" => 1];
$array2 = ["a" => 2];
$array = ["a" => 0, ...$array1, ...$array2];
var_dump($array); // ["a" => 2]
```

정수키들은 영향을 받지않고 그대로 유지 됩니다.
```php
$array1 = [1, 2, 3];
$array2 = [4, 5, 6];
$array = [...$array1, ...$array2];
var_dump($array); // [1, 2, 3, 4, 5, 6]
// 결과: [0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6]
// 기존에는 정수키의 영향을 받지 않고 전개 되었습니다.
```

만일 정수도, 문자열도 아닌 key를 가진 경우 `TypeError`에러를 발생시킵니다.

## 대안
이 RFC에서 제안되지 않은 두가지 대안이 있습니다.

### 1. 배열 연산자의 의미를 따르는 것입니다.

Array에서 `+` 연산을 할 경우 앞의 `key` 값을 유지 시킵니다.
```php
$array1 = ["a" => 1];
$array2 = ["a" => 2];
var_dump($array1 + $array2); // ["a" => 1]
```

기존의 `+`연산자와 `array_merge()`함수가 존재함으로, Key가 있는 배열의 `...` 연산자는 순수한 벡터 병합을 만들 수 있는 좋은 기회입니다.

```php
$array = [...[1, 2, 3], ...[4, 5, 6]];
// 대략 다음과 같이 동작해야합니다.
$array = [1, 2, 3, 4, 5, 6];
 
// 마찬가지로 문자열 키의 경우 :
$array = [...["a" => 1], ...["a" => 2]];
// 대략 다음과 같이 동작해야합니다.
$array = ["a" => 1, "a" => 2];
// 이는 아래와 같습니다.
$array = ["a" => 2];
```
위와 같이 동작하는 것이 좀 더 직관적으로 동작하는 것 같습니다.

### 2. PHP 8.0부터 추가된 전개된 Array는 문자열에 따라 맵핑됩니다.
```php
call(...["a" => 1]);
// 아래와 같이 동작합니다.:
call(a: 1);
// Not:
call(1);
```