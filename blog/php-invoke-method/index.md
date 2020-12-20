---
layout: post
title: "__invoke PHP 매직 메소드"
subtitle: "__invoke php magic method"
type: "PHP"
blog: true
text: true
draft: true
author: "silnex"
post-header: true
header-img: "img/magic.jpg"
order: 3
tags: ['PHP', 'Magic method', '__invoke']
series: 'php-magic-method-1'
---

# PHP __invoke 매직 메소드
PHP에는 다양한 매직 메소드 들이 있습니다.  
이는 PHP의 유연함을 대표하기도 하면서,
IDE의 정적 분석을 어렵게 하기에 많은 말이 있습니다만, 이에 대한 부분은 다른 포스트에서 다뤄보도록 하고  이번 포스트에선 `__invoke` 매직 메소드를 다뤄보려고 합니다.

## Form
`public __invoke(...$values) : mixed`  
 - `__invoke` 메소드는 반드시 public visibility를 가져야합니다.  
[Document](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke)

### Detail
`__invoke`는 그 단어의 뜻처럼 "호출"을 담당하고 있습니다. 정확히는 "객체"의 호출을 담당하고 있습니다.  
기존의 객체 호출을 담당하는 `__construct`와는 다르게 `new` 키워드 없이 호출 될때 를 의미합니다.

## Example
```php
class CallableClass
{
    public function __construct($x)
    {
        var_dump($x);
    }

    public function __invoke($x)
    {
        var_dump($x);
    }
}
$obj = new CallableClass(1);
$obj(2);
$obj(3);
echo "\n";
(new CallableClass(1))(2);
echo "\n";
var_dump(is_callable($obj));
```

## Result
```
int(1) int(2) int(3)
int(1) int(2)
bool(true)
```

## Description
