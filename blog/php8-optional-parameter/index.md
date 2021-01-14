---
layout: post
id: 11
title:  "PHP8 에서 파라미터 기본값 선언"
subtitle: "PHP8 에서 파라미터 기본값을 선언할 때의 주의할 점"
description: "PHP8 에서 파라미터 기본값을 선언할 때의 주의할 점에 대해서 알아봅니다."
type: "PHP"
created_at: "2021-01-14"
updated_at: "2021-01-14"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/php8.png"
order: 11
tags: ['php', '8.0', 'deprecated-feature', 'short-article']
comments: true
---

PHP에선 다음과 같이 함수에 기본값을 설정할 수 있습니다.
```php
function myFunction($a = 'default')
{
    echo $a;
}
myFunction(); // default
myFunction('new one'); // new one
```

`myFunction()`과 같이 파라미터 없이 호출하면 기본값 `default`가 표시되고,  
`myFunction('new one')`과 같이 파라미터와 함께 보내면 전달된 `new one`이 표시됩니다.

# PHP 8 optional parameter issue
위와 같은 특징을 이용해서 함수를 작성할 때 다음과 같이 작성할 수 있다.
```php
function myFunction($a = 'default', $b)
{
    echo $a . $b;
}
```

## PHP ~7.x
PHP 7.x 이하의 버전에선 다음과 같은 결과가 나옵니다.
```php
myFunction() // Fatal error: Uncaught ArgumentCountError: Too few arguments ...
myFunction('x') // Fatal error: Uncaught ArgumentCountError: Too few arguments ...
myFunction('x', 'y') // xy
```
기본값이 선언되지 않은 두 번째 파라미터로 인해 `Fatal error` 에러가 나게 됩니다.  
하지만 함수의 선언에는 전혀 문제가 되지 않습니다.

## PHP 8.x
하지만 PHP 8 이후의 버전에선 `myFunction($a = 'default', $b)`위와 같은 함수가 선언된다면 
`Deprecated` 메시지가 출력됩니다.
```bash
Deprecated: Required parameter $b follows optional parameter $a in on line 2
```
함수 자체는 실행이 가능하기 때문에 `myFunction('x', 'y')`의 결괏값은 `xy`가 출력됩니다.

### 허용 되는 사항
위와 같은 상황은 허용되지 않지만, 아래와 같은 경우엔 허용됩니다.
```php
function myFunction(?string $a = null, $b) { /* ... */ }
function myFunction(A $a = null, $b) { /* ... */ }
```
즉, 타입이 있고 `null`을 허용하면서 초깃값을 `null`로 사용할 땐 아직까진 허용된다고 합니다.  
다만 `function myFunction(A $a = null, $b) { /* ... */ }` 같은 경우엔  
`function myFunction(?A $a, $b) { /* ... */ }`을 권장한다고 이 부분에 맞춰서 미리 순서를 맞춰 놓는 편이 좋을 것 같습니다.

# 마치며
PHP 8 업그레이드 후 TEST에서 어마어마한 Test fail이 떠서 확인해보니 해당 이슈였습니다.  
어찌 생각해 보면, named parameter가 존재하니 단순히 가독성을 위해서 파라미터의 순서를 변경할 필요가 없으니 성능 향상이든, 가독성을 위해서든 위와 같이 바뀌는 것도 이해가 가네요.

다만, 아직 `?Type $val = null`에 대해선 허용되고 있으나 이 부분이 앞으로 어떻게 바뀔지가 궁금해집니다.

더 자세한 사항들은 [PHP: Function arguments](https://www.php.net/manual/en/functions.arguments.php) 매뉴얼에 잘 나와 있으니 한 번쯤 확인해 보시는 것을 추천합니다.