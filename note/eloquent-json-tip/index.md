---
layout: note
type: "TIP"
title: Eloquent Json 데이터 팁
subtitle: Laravel의 ORM인 Eloquent로 Json 데이터를 다루는 팁
note: true
article: false
order: 5
created_at: "2021-04-25"
updated_at: "2021-04-25"
comments: true
---

라라벨의 Eloquent ORM은 아래 예시처럼 Json 컬럼에 `컬럼명->키` 검색을 지원합니다.

```php
User::where('column->key', 'data')->get();
```

또 Laravel 테스트 메소드중 `assertDatabaseHas`에서도 다음과 같이 사용 할 수 있습니다.

`$this->assertDatabaseHas('users', ['config->key' => 'data'])`