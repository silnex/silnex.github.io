---
layout: post
id: 5
title:  "20가지 Laravel eloquent 팁과 트릭"
subtitle: "'20 Laravel Eloquent Tips and Tricks'의 번역 글입니다."
description: "라라벨 엘로퀀트의 20가지 팁과 트릭들에 대해서 알아봅니다."
type: "Laravel"
created_at: "2021-01-04"
updated_at: "2021-01-06"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/database.jpg"
order: 5
tags: ['laravel', 'eloquent', 'tip', 'translate']
comments: true
---
# 들어가며
라라벨의 모델은 정말 다양한 방법으로 사용할 수 있고, 또한 많은 부분을 자동으로 처리해줍니다.  
이러한 특성을 최대한으로 살리는 아래의 팁들은 라라벨로 개발할 때 편리한 도구가 되어줄 겁니다.

원본글 "[20 Laravel Eloquent Tips and Tricks](https://laravel-news.com/eloquent-tips-tricks)"

# 20가지 엘로퀀트 팁들

엘로퀀트 ORM은 단순한 매커니즘으로만 보이지만, 그 아래에선 많은 감춰진 함수들과 덜 알려진 방법들로 원하는 목표에 다다를 수 있습니다. 이 글에선 몇 가지 트릭들에 대해서 알려 드리겠습니다.

## 1. 증가와 감소
아래 예제 대신에
```php
$article = Article::find($article_id);
$article->read_count++;
$article->save();
```

이렇게 사용할 수 있습니다.
```php
$article = Article::find($article_id);
$article->increment('read_count');
```

이런 식으로도 바로 사용할 수 있습니다.
```php
Article::find($article_id)->increment('read_count'); // +1
Article::find($article_id)->increment('read_count', 10); // +10
Product::find($produce_id)->decrement('stock'); // -1
```

## 2. XorY 메소드들
엘로퀀트는 두 메소드를 합쳐놓은 함수르들 가지고 있습니다. 예를 들어 "X를 하고 안되면, Y를 하세요" 식으로 할 수 있습니다.

예제 1 - `findOrFail()`:
아래 방법 대신에
```php
$user = User::find($id);
if (!$user) {
  abort (404);
}
```

이렇게 할 수 있습니다.
```php
$user = User::findOrFail($id);
```

예제 2 - `firstOrCreate()`:
아래 방법 대신에
```php
$user = User::where('email', $email)->first();
if (!$user) {
  User::create([
    'email' => $email
  ]);
}
```

이렇게 할 수 있습니다.
```php
$user = User::firstOrCreate(['email' => $email]);
```

## 3. Model boot() 메소드
엘로퀀트 모델에는 기본 동작을 재정의 할 수 있는 `boot()`라는 마법의 장소가 있습니다.

```php
class User extends Model
{
  public static function boot()
  {
    parent::boot();
    static::updating(function($model) {
      // 뭔가 로그를 남기는 로직
      // $model->something = transform($something);처럼 몇 가지 속성을 재정의
    });
  }
}
```
대게 모델 객체를 생성할 때 몇몇의 필드의 값을 설정하는 예제가 가장 많지 않을까 싶습니다.  
모델이 생성되는 순간 [UUID 필드](https://github.com/webpatser/laravel-uuid)를 생성하고 싶을 때 아래 예제 처럼 만들 수 있습니다.

```php
public static function boot()
{
  parent::boot();
  self::creating(function ($model) {
    $model->uuid = (string) Uuid::generate();
  });
}
```

## 4. Relationship의 조건과 정렬
이 방법은 relationship을 정의하는 전형적인 방법입니다.
```php
public function users() {
  return $this->hasMany('App\User');    
}
```
이 상황에서 항상 `where`이나 `orderBy`를 적용할 수 있습니다.
예를 들어, 만약 특정 relationship을 가진 유저이면서, email로 정렬된 상태로 가져오고 싶다면 아래처럼 할 수 있습니다.

```php
public function approvedUsers() {
  return $this->hasMany('App\User')->where('approved', 1)->orderBy('email');
}
```

## 5. Model의 속성: timestamps, appends 등.
엘로퀀트 모델에는 속성의 형태로 몇가지 "파라미터들"이 있습니다.
아래의 속성들은 가장 유명한 속성들입니다.
```php
class User extends Model {
  protected $table = 'users';
  protected $fillable = ['email', 'password']; // User::create() 할때 입력가능한 필드들
  protected $dates = ['created_at', 'deleted_at']; // Carbon 클래스로 랩핑될 필드들
  protected $appends = ['field1', 'field2']; // JSON등으로 return 될 때 포함될 필드들
}
```

하지만 더 많은 속성들이 있습니다.
```php
protected $primaryKey = 'uuid'; // 반드시 "id"일 필요가 없습니다.
public $incrementing = false; // auto-incrementing 또한 필수일 필요가 없습니다.
protected $perPage = 25; // 페이지네이션을 사용할 때 PER MODEL를 재정의합니다. (default 15)
const CREATED_AT = 'created_at';
const UPDATED_AT = 'updated_at'; // 시간 필드의 이름을 재정의할 수 있습니다.
public $timestamps = false; // 또는 시간 필드를 사용하지 않을 수 있습니다.
```
그리고 더 많은 것들이 있습니다. 여기선 가장 흥미로운 것들만 나열 했습니다. 더 많은 것들을 확인하고 싶으면, [abstract Model class](https://github.com/laravel/framework/blob/5.6/src/Illuminate/Database/Eloquent/Model.php)와 사용된 trait 들을 확인할수 있습니다.

## 6. 여러개의 항목 찾기
`find()`메소드는 모두가 알고 있을 겁니다.
```php
$user = User::find(1);
```

다만, 많은 사람이 ID를 array로 여러개를 조회 할 수 있다는 것을 모르는것에 대해 놀랐습니다.
```php
$users = User::find([1,2,3]);
```

## 7. WhereX
아래의 예제를
```php
$users = User::where('approved', 1)->get();
```

이렇게 우아하게 바꿀 수 있는 방법이 있습니다.
```php
$users = User::whereApproved(1)->get();
```

"where"과 필드의 이름을 접미사로 더하면 마술같이 동작합니다.
또한 엘로퀀트에는 날짜/시간에 관련된 몇가지 메소드 들이 존재합니다.
```php
User::whereDate('created_at', date('Y-m-d'));
User::whereDay('created_at', date('d'));
User::whereMonth('created_at', date('m'));
User::whereYear('created_at', date('Y'));
```

## 8. Order by relationship
이번엔 좀 복잡한 트릭입니다. 포럼 주제가 있지만 최신 게시물로 정렬하려면 어떻게해야합니까?
마지막으로 업데이트 된 주제가 맨 위에있는건 포럼에선 일반적입니다.

First, describe a separate relationship for the latest post on the topic:

먼저, relationship에 최신순으로 정렬 하도록 수정합니다.
```php
public function latestPost()
{
  return $this->hasOne(\App\Post::class)->latest();
}
```

그리고 컨트롤러에선 "마술"을 부릴수 있습니다.
```php
$users = Topic::with('latestPost')->get()->sortByDesc('latestPost.created_at');
```

## 9. if-else’s 대신에 Eloquent::when()
많은 사람들이 예시처럼 조건 쿼리를 "if-else" 과 함께사용합니다.
```php
if (request('filter_by') === 'likes') {
  $query->where('likes', '>', request('likes_amount', 0));
}
if (request('filter_by') === 'date') {
  $query->orderBy('created_at', request('ordering_rule', 'desc'));
}
```
하지만 더 나은 방법인 `when()`을 사용할 수 있습니다.
```php
$query = Author::query();
$query->when(request('filter_by') === 'likes', function ($q) {
  return $q->where('likes', '>', request('likes_amount', 0));
});
$query->when(request('filter_by') === 'date', function ($q) {
  return $q->orderBy('created_at', request('ordering_rule', 'desc'));
});
```
아마 짧아지거나 우아해보지 않을 수 있습니다. 하지만 파라미터를 전달하면 그 위력을 알수 있습니다.
```php
$query = User::query();
$query->when(request('role', false), function ($q, $role) { 
  return $q->where('role_id', $role);
});
$authors = $query->get();
```

###### (필자) 또는 PHP 7.4에 추가된 fn() => 을 사용해 더 단축시킬 수 있습니다.
```php
$query = User::query();
$query->when(request('role', false), fn ($q, $role) => $q->where('role_id', $role));
$authors = $query->get();
```

## 10. BelongsTo 기본 모델
포스트가 작성자에 속해있다고 할때 블레이드 코드는 다음과 같습니다.
```blade
{% raw %}{{ $post->author->name }}{% endraw %}
```

하지만 만약 작성자가 삭제되거나, 다른이유로 설정되지 않는다면? 아마 “property of non-object” 같은 에러가 날것입니다.

물론 아래 처럼 처리 할 수 도 있습니다.
```blade
{% raw %}{{ $post->author->name ?? '' }}{% endraw %}
```

하지만 엘로퀀트 relationship 딴에서 처리할 수 있습니다.
```php
public function author()
{
  return $this->belongsTo('App\Author')->withDefault();
}
```
이 예시에서 `author()`는 만약 post에 Author가 없다면 빈 Author 모델을 반환합니다.

게다가, 기본 모델에 기본 속성값을 할당 할 수 있습니다.
```php
public function author()
{
  return $this->belongsTo('App\Author')->withDefault([
    'name' => 'Guest Author'
  ]);
}
```

## 11. Order by Mutator
이런 상황이 있다고 생각봅시다.
```php
function getFullNameAttribute()
{
  return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
}
```

`full_name`으로 정렬하고 싶다면 아래 코드는 동작하지 않습니다.
```php
$clients = Client::orderBy('full_name')->get(); // 작동안함
```

해답은 단순합니다. 우리는 데이터를 가져온 후 정렬 해야 합니다.
```php
$clients = Client::get()->sortBy('full_name'); // works!
```
함수이름이 다른것에 대해서 주의하세요, `orderBy`가 아니라 `sortBy`입니다.

## 12. 전역 범위에서의 기본 순서
What if you want to have User::all() always be ordered by name field? You can assign a global scope. Let’s go back to the boot() method, which we mentioned already above.

만일 `User::all()`을 호출할때 항상 `name`필드로 정렬된 값을 같고 싶다면, 전역 범위에서 할당할 수 있습니다. 위에서 이야기한 `boot()`메소드로 가 봅시다.
```php
protected static function boot()
{
    parent::boot();

    // Order by name ASC
    static::addGlobalScope('order', function (Builder $builder) {
        $builder->orderBy('name', 'asc');
    });
}
```
더 자세한 내용은 [Query Scopes](https://laravel.com/docs/eloquent#query-scopes)를 확인해보세요

## 13. Raw query 메소드
종종 엘로퀀트 대신에 Raw 쿼리들을 추가해야 할 때가 있습니다. 다행이도, 이를 위한 함수가 있습니다.
```php
// whereRaw
$orders = DB::table('orders')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();

// havingRaw
Product::groupBy('category_id')->havingRaw('COUNT(*) > 1')->get();

// orderByRaw
User::where('created_at', '>', '2016-01-01')
  ->orderByRaw('(updated_at - created_at) desc')
  ->get();
```

## 14. 복제: 새로운 복재 행을 만듭니다.
Short one. Without deep explanations, here’s the best way to make a copy of database entry:
짧게 설명하면, 여기 데이터베이스 엔트리를 복사하는 가장 좋은 방법이 있습니다.
```php
$task = Tasks::find(1);
$newTask = $task->replicate();
$newTask->save();
```

## 15. 큰 테이블을 위한 Chunk() 메소드
엘로퀀트에 보단 Collection에 관련된 이야기지만, 매우 유용합니다. - 무거운 데이터 셋을 관리할 때, 작은 조각 들로 쪼개어 관리 할 수 있습니다.

아래 예제 보단,
```php
$users = User::all();
foreach ($users as $user) {
  // ...
```

이렇게 사용할 수 있습니다.
```php
User::chunk(100, function ($users) {
  foreach ($users as $user) {
    // ...
  }
});
```

## 16. 모델이 만들어 질 때 추가 항목들도 같이 만들기
다음과 같은 커맨드는 이미 알고 있을겁니다.
```bash
php artisan make:model Company
```

하지만, 모델과 관련된 것들을 생성해주는 유용한 플래그들이 있습니다.
```bash
php artisan make:model Company -mcr
```
-m 마이그레이션 파일을 생성합니다.
-c 컨트롤러 파일을 생성합니다.
-r resourceful한 컨트롤러를 생성합니다.

## 17. 저장할 때 updated_at 덮어쓰기(Override)
Did you know that ->save() method can accept parameters? As a result, we can tell it to “ignore” updated_at default functionality to be filled with current timestamp. See this:
`->save()`메소드가 파라미터를 받는다는것을 알고 계셧나요? 결과적으로 우리는 현재 시간틀 기본으로 채워주는 `updated_at` 를 "무시" 할 수 있습니다.

```php
$product = Product::find($id);
$product->updated_at = '2020-01-01 00:00:00';
$product->save(['timestamps' => false]);
```
이제 `updated_at` 사전에 재정의한 것으로 정의 되어집니다.

## 18. update()의 결과는 무엇인가요?
`update()` 실제 반환 값에 대해서 생각해 본적이 있으신가요?
```php
$result = $products->whereNull('category_id')->update(['category_id' => 2]);
```
I mean, the update is performed in the database, but what would that $result contain?
내가 말하는건, 업데이트는 데이터베이스에서 실행됬지만, `$result`에는 무엇이 포함되어 있나요?

정답은 영향을 받은 열입니다. 만약 여러분이 영향을 받은 행을 확인하고 싶다면, 따로 else 를 호출할 필요없이 `updated()`의 반환 값을 통해 알 수 있습니다.
## 19. 엘로퀀트 쿼리에서 괄호를 변경
만약 SQL 쿼리에서 and-or을 섞어야 할땐 다음과 같이 할 수 있습니다.
```sql
... WHERE (gender = 'Male' and age >= 18) or (gender = 'Female' and age >= 65)
```
How to translate it into Eloquent? This is the wrong way:

엘로퀀트로는 어떻게 구현할 수 있을가요? 여기 잘못된 방법이 있습니다.
```php
$q->where('gender', 'Male');
$q->orWhere('age', '>=', 18);
$q->where('gender', 'Female');
$q->orWhere('age', '>=', 65);
```

위에순서는 잘못 되었습니다. 올바른 방법은 조금 더 복잡합니다. 클로저 함수를 사용해 서브 쿼리를 이용하여 작성할 수 있습니다.
```php
$q->where(function ($query) {
  $query->where('gender', 'Male')
    ->where('age', '>=', 18);
})->orWhere(function($query) {
  $query->where('gender', 'Female')
    ->where('age', '>=', 65); 
})
```
## 20. orWhere와 멀티 파라미터

마지막으로 `orWhere()`에 배열을 전달 할 수 있습니다.

보통은 아래와 같이 작성하는데,
```php
$q->where('a', 1);
$q->orWhere('b', 2);
$q->orWhere('c', 3);
```
이렇게 작성 할 수 있습니다.
```php
$q->where('a', 1);
$q->orWhere(['b' => 2, 'c' => 3]);
```

# 마치며
마지막에 홍보 부분은 따로 번역 하지 않았습니다 <del>ㅎㅎ</del>  

엘로퀀트를 사용하면서 가장 많이 사용했던 팁들이 아닌가 싶습니다.  
만약 엘로퀀트를 좀 더 잘 다루고 싶으시다면, 이 글을 작성하신 분의 [Eloquent: Expert Level](https://laraveldaily.teachable.com/p/laravel-eloquent-expert-level) 강좌를 들어보시는 것도 좋을 것 같습니다.  

개인적으로 추천하는 공부법(?)은 Eloquent를 구현한 Code를 직접 보시는 걸 추천드립니다.  
또 git 로그와 함께 보면 만들어진 코드는 잘 수정되지도 않고 확장만으로 추가적인 기능들이 쌓여 가는 모습도 볼 수 있습니다!