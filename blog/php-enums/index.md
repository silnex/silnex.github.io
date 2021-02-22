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

