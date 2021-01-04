---
layout: post
id: 3
title: "__invoke PHP 매직 메소드"
subtitle: "__invoke php magic method"
description: "PHP의 매직 메소드인 __invoke에 대해서 알아보고 몇가지 활용 방법에 대해서 알아보는 포스트입니다."
type: "PHP"
created_at: "2020-12-21"
updated_at: "2020-12-22"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/magic.jpg"
order: 3
tags: ['PHP', 'Magic method', '__invoke']
series: 'php-magic-method-1'
comments: true
---

# PHP __invoke 매직 메소드
PHP에는 다양한 매직 메소드 들이 있습니다.  
이는 PHP의 유연함을 대표하기도 하면서,
IDE의 정적 분석을 어렵게 하기에 많은 말이 있습니다만, 이에 대한 부분은 다른 포스트에서 다뤄보도록 하고  이번 포스트에선 `__invoke` 매직 메소드를 다뤄보려고 합니다.

## Form
`public __invoke(...$values) : mixed`  
 - `__invoke` 메소드는 반드시 public visibility를 가져야합니다.
 - `__invoke` 메소드는 `static` 을 가질 수 없습니다.  
[Document](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke)

### Detail
`__invoke`는 그 단어의 뜻처럼 "호출"을 담당하고 있습니다. 정확히는 "객체"의 호출을 담당하고 있습니다.  
기존의 객체 호출을 담당하는 `__construct`와는 다르게 `new` 키워드 없이 호출 될때 를 의미합니다.

## Example
```php
class CallableClass
{
    public function __invoke($x)
    {
        var_dump($x);
        return $this;
    }
    
    public function method()
    {
        var_dump('called class method');
    }
}

$obj = new CallableClass(1);
$obj('call invoke')->a();
var_dump(is_callable($obj));
```

## Result
```
string(11) "call invoke"
string(1) "called class method"
bool(true)
```

## Description
 - `__invoke` 는 `__construct`와 다르게 `new`키워드로 선언할 때는 실행 되지 않습니다.  
 - `__invoke` 메소드가 선언된 클래스는 [`is_callable`](https://www.php.net/manual/en/function.is-callable.php)함수로 호출 가능하다 라고 표시됩니다.  
 - `$this`를 반환해 클래스 메소드들을 호출 할 수 있습니다. 