---
layout: post
title: "Github Action SQLITE \"ON CONFLICT, DO UPDATE\" Syntax error"
subtitle: "SQLITE UPSERT"
type: "CI/CD"
created_at: "2020-12-27"
updated_at: "2020-12-27"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/github.jpg"
order: 4
tags: ['github', 'github-action', 'troubleshooting', 'ci/cd']
---

## TL;TR
> SQLite 에선 `ON CONFLICT, DO UPDATE`(일명 [Upsert](https://www.sqlite.org/lang_upsert.html)) 는 `3.24.0 (2018-06-04)`버전에서 추가 되었으나, `ubuntu-18.04`에선 SQLite 버전이 낮아 문법 오류가 난다. [laravel.yml](#최종-laravel.yml)

----

# Github Action
Github Action은 Github에서 제공하는 [CI/CD](https://en.wikipedia.org/wiki/CI/CD)도구로 미리 입력된 workflow에 따라서 push이벤트를 감지해 "설치-테스트-배포"를 자동으로 해주는 도구입니다.  

이번 글에선 Laravel8의 upsert를 설명을 준비하던 도중 Github에서 기본으로 제공해주는 [laravel action workflow](https://github.com/actions/starter-workflows/blob/e9e00b017736d3b3811cedf1ee2e8ceb3c48e3dd/ci/laravel.yml)에선 sqlite의 upsert문법이 작동하지 않아 test가 깨지는 문제해결에 대한 내용을 남길 예정입니다.

## Laravel.yml
아래 yml은 Laravel Repo일때 자동으로 추천해주는 laravel action입니다.  

```yml
name: Laravel

on:
  push:
    branches: [ $default-branch ]
  pull_request:
    branches: [ $default-branch ]

jobs:
  laravel-tests:

    runs-on: ubuntu-latest

    steps:
    - uses: shivammathur/setup-php@b7d1d9c9a92d8d8463ce36d7f60da34d461724f8
      with:
        php-version: '8.0'
    - uses: actions/checkout@v2
    - name: Copy .env
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"
    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
    - name: Generate key
      run: php artisan key:generate
    - name: Directory Permissions
      run: chmod -R 777 storage bootstrap/cache
    - name: Create Database
      run: |
        mkdir -p database
        touch database/database.sqlite
    - name: Execute tests (Unit and Feature tests) via PHPUnit
      env:
        DB_CONNECTION: sqlite
        DB_DATABASE: database/database.sqlite
      run: vendor/bin/phpunit
```

위에서 부터 순서대로 해석하면,  
 1. `ubuntu-latest` 위에서 테스트를 진행합니다.
 2. `shivammathur/setup-php`에서 `php-version: '8.0'`을 사용합니다.
 3. `actions/checkout@v2`을 사용해 checkout을 사용합니다.
 4. `.env.example`을 `.env`로 복사합니다.
 5. `composer`를 이용해 종속된 패키지를 설치합니다.
 6. `php artisan key:generate`으로 `APP_KEY`를 생성합니다.
 7. `chmod -R 777 storage bootstrap/cache`으로 캐시 폴더의 권한을 변경합니다.
 8. `mkdir -p database && touch database/database.sqlite`으로 SQLite DB파일을 생헝합니다.
 9. 환경변수 `DB_CONNECTION`을 `sqlite`으로, `DB_DATABASE`을 `database/database.sqlite`으로 설정하고 `vender/bin/phpunit`으로 TEST를 진행합니다.

위에 `step`다음에 deploy를 위한 flow를 적어주면 위에 내용중 fail이 발생한 순간 flow는 중단되고 deploy는 이루어지 않게 됩니다.

이런식으로 사용하면 자동을 테스트를 실행해주고 deploy까지 진행되다보니 개발자는 좀 더 코드에만 집중 할 수 있게 해줍니다.

# 그런데,, 문제가 생겼습니다.

```bash
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

...................F...........................................  63 / 113 ( 55%)
..................................................              113 / 113 (100%)

Time: 00:07.325, Memory: 46.00 MB
```

분명 로컬 환경에선 test가 통과하는데 Action의 테스트에선 Fail이 생겼습니다. <del>컴퓨터가 거짓말을 한다...</del>

## 로그,,, 로그를 보자,,
그닥 내기지는 않지만 테스트 코드에 `dd()`를 찍어서 에러 메시지를 Github actions상에 띄워보니 뜬금없는 SQL syntax 에러가 납니다.

```bash
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

...................{#4610
  +"message": 'SQLSTATE[HY000]: General error: 1 near "on": syntax error (SQL: insert into "table_name" (...) values (...) on conflict ... do update set ...)'
  +"exception": "Illuminate\Database\QueryException"
...
  ```

## Googling
생각보다 가까운곳에서 해결 방법을 찾았다. [laravel-upsert](https://github.com/staudenmeir/laravel-upsert/issues/28)이슈를 보면 github action을 통해 테스트를 진행하는데, 문법 에러가 났다는 것이다.

내용을 보니 결국 버전이 맞지 않아 발생한 문제란것을 알게 되어 여기에 나온 방법으로 진행하려고 했다가. 먼가 쎄한 기분에 [Action에서 제공하는 가상 환경](https://github.com/actions/virtual-environments)에서 `ubuntu-latest`가 몇 버전인지 확인했는데..

| Environment | YAML Label |
| --- | --: |
| Ubuntu 20.04 | `ubuntu-20.04` |
| Ubuntu 18.04 | `ubuntu-latest` or `ubuntu-18.04` |

<p style="text-align:center;font-size:12px"><del>속앗다...</del></p>

`ubuntu-latest`가 18.04이다. laravel-upsert의 이슈에서 나온 방법데로 해도 되지만, [Laravel.yml](#Laravel.yml)에서 단순히 `runs-on: ubuntu-latest` 를 `runs-on: ubuntu-20.04`로 바꿔주면 해결된다.

# 최종 laravel.yml

```yml
name: Laravel

on:
  push:
    branches: [ $default-branch ]
  pull_request:
    branches: [ $default-branch ]

jobs:
  laravel-tests:

    runs-on: ubuntu-20.04 # 여기가 달라졌어요

    steps:
    - uses: shivammathur/setup-php@b7d1d9c9a92d8d8463ce36d7f60da34d461724f8
      with:
        php-version: '8.0'
    - uses: actions/checkout@v2
    - name: Copy .env
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"
    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
    - name: Generate key
      run: php artisan key:generate
    - name: Directory Permissions
      run: chmod -R 777 storage bootstrap/cache
    - name: Create Database
      run: |
        mkdir -p database
        touch database/database.sqlite
    - name: Execute tests (Unit and Feature tests) via PHPUnit
      env:
        DB_CONNECTION: sqlite
        DB_DATABASE: database/database.sqlite
      run: vendor/bin/phpunit
```