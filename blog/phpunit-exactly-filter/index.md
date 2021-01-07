---
layout: post
id: 6
title:  "PHPunit filter 사용 시 완벽히 일치할 때만 테스트"
subtitle: "PHPunit의 filter 팁"
description: "PHPunit의 filter 팁"
type: "PHP"
created_at: "2021-01-05"
updated_at: "2021-01-05"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/phpunit.png"
order: 6
tags: ['phpunit', 'laravel', 'tip', 'short-article']
comments: true
---

# TR;TL
`phpunit --filter 'regex'`

# PHPunit Filter
phpunit을 사용하면서 특정 메소드, 혹은 클래스만 테스트 하고 싶을 땐 아래 처럼 `filter`를 줘서 실행합니다.
```bash
phpunit --filter test_something
```
물론 Laravel을 쓰는 경우엔 artisan에서 제공하는 test 명령어를 사용할 수도 있습니다.
```bash
artisan test --filter test_something
```

# 문제점
다만, test의 메소드 이름이 `test_something`, `test_something_1`처럼 동일한 부분이 있을 경우 문제가 생기는데, 

내가 하고싶은 테스트는 `test_something` 뿐임에도 `test_something_1`까지 모두 실행되는 문제가 있습니다.

# 해결
이때 --filter 로 넘기는 파라미터에 단순한 텍스트 말고도 정규식을 전달하는 방식으로 해결 할 수 있습니다.

```bash
phpunit --filter 'test_something$' # '$' 표시는 문장의 마지막을 의미합니다.
```
물론 laravel 의 `artisan test`도 동일하게 사용할 수 있습니다.

```bash
artisan test --filter 'test_something$' # '$' 표시는 문장의 마지막을 의미합니다.
```