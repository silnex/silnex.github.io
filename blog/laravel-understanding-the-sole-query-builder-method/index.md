---
layout: post
id: 13
title: "[번역] 쿼리빌더 sole() 메소드 이해하기"
subtitle: "Understanding the sole() Query Builder Method의 번역글입니다."
description: "[번역] 쿼리빌더 sole() 메소드 이해하기"
type: "Laravel"
created_at: "2021-01-22"
updated_at: "2021-01-22"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/laravel-sole-featured.png"
order: 13
tags: ["laravel", "laravel-news", "query-builder", "translate", "short-article"]
comments: true
---

> [원본글 - Understanding the sole() Query Builder Method](https://laravel-news.com/understanding-the-sole-query-builder-method)

라라벨 8.23에서 쿼리빌더 `sole()` 메소드가 소개 되었습니다. 이 메소드는 단일 레코드를 검색하지만 추가적인 단언(assertions)도 가지고 있습니다.

Sole는 단일 행이 필요하고 쿼리가 하나의 레코드와만 일치한다고 단언(Assert)할 때와 하나의 레코드만 있다는 것을 절대적으로 보장하길 바랄때 유용하게 사용할 수 있습니다.  
만일 레코드가 없거나 하나보다 많을 때 라라벨은 예외를 발생 시킵니다.

이 글에선 이 메소드를 사용하는 방법과 3가지 가능한 시나리오에 대해서 보여 줍니다.

1. 하나의 레코드만 일치하는 쿼리
2. 하나도 일치하지 않는 쿼리
3. 2개 이상의 레코드와 일치하는 쿼리

# 데모

원하는 경우에 따라 테스트 할 수 있도록 데모앱을 만들어 보겠습니다.

먼저 laravel 프로젝트를 만듭니다:

```bash
laravel new sole-demo
cd sole-demo
```

다음엔 `sole()`이 어떻게 동작하는지 보여주는 모델을 만듭니다.

```bash
php artisan make:model -m Book
```

마지막으로 위에서 생성한 모델의 `books` 데이터 베이스를 정의합니다.

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
```

우리가 만든 필드를 체울 수 있도록 잊지말고 fillable을 추가합니다.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    public $fillable = ['title', 'summary'];
}
```

쿼리 빌더 테스트를 위한 마이그래이션을 실행합니다.

```bash
php artisan migrate:fresh
```

이제 몇가지 예를 보여주기 위해 `books` 테이블이 있어야 합니다.

# sole() 메소드 사용하기

`sole()` 메소드를 테스트 하기위해 `php artisan tinker` 명령어를 사용해 레코드를 만듭니다.  
먼저 `first()`메소드와 `get()` 메소드를 사용할 때 어떤 일이 발생하는지 살펴 보겠습니다.

```php
use App\Models\Book;

Book::create([
    'title' => 'War of the Worlds',
    'summary' => 'Example summary'
]);

// All the records that match the query
Book::where('title', 'like', '%War%')->get();
/*
Illuminate\Database\Eloquent\Collection {#4264
 all: [
   App\Models\Book {#4209
     id: 1,
     title: "War of the Worlds",
     summary: "Example summary",
     published_at: null,
     created_at: "2021-01-21 22:14:29",
     updated_at: "2021-01-21 22:14:29",
   },
 ],
}
*/

// Get the first record in the query
// Even if the query has multiple matches, return the first one
Book::where('title', 'like', '%War%')->first();

/*
=> App\Models\Book {#4210
     id: 1,
     title: "War of the Worlds",
     summary: "Example summary",
     published_at: null,
     created_at: "2021-01-21 22:14:29",
     updated_at: "2021-01-21 22:14:29",
   }
*/
```

`get()`메소드와 `first()` 메소드는 Laravel에서 일반적으로 사용되지만, `sole()`은 하나의 레코드만 존재하는 것을 보장하거나 기대 하는 경우에 유용하게 사용되어집니다.

```php
Book::where('title', 'like', '%War%')->sole();
/*
App\Models\Book {#3647
  id: 1,
  title: "War of the Worlds",
  summary: "Example summary",
  published_at: null,
  created_at: "2021-01-21 22:14:29",
  updated_at: "2021-01-21 22:14:29",
}
*/
```

만약 데이터베이스 테이블에 데이터가 없다면 `ModelNotFoundException` 에러를 발생시킵니다.

```php
Book::where('title', 'like', '%The War')->sole();
// => Illuminate\Database\Eloquent\ModelNotFoundException
```

만약 두개 이상의 레코드를 가지고 있다면, `MultipleRecordsFoundException` 에러를 발생시킵니다.

```php
// Create a second title with the word `War` in it.
Book::create([
    'title' => 'War and Peace',
    'summary' => 'Example summary'
]);

Book::where('title', 'like', '%War%')->sole();
// => Illuminate\Database\MultipleRecordsFoundException
```

# 더 알아보기

이 짧은 글이 `sole()`을 사용할 때 예상되는 것을 시각화하는데 도움이 되었으면 좋겠습니다. `sole()`이 어떻게 구현되어있는지 자세히 알고 싶다면 Laravel [8.23.0](/blog/laravel-release-8-23/) 릴리즈 노트에 정보와 링크가 나와있습니다.

# 마치며
어제 `sole()`메소드가 저만 유용해 보였던것은 아니였군요. 이번 글에서 짧게 나마 `sole()` 어떻게 사용하면 좋을지에 대해서 좋은예를 볼 수 있어서 좋았던것같습니다.

번역하면서 Assert를 어떻게 번역해야 할지 애매해서 조금이나마 헤메었습니다만, 번역한 글이 이해 하시는데 도움이 되었으면 좋겠습니다.
