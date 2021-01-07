---
layout: post
id: 7
title:  "Artisan 명령 목록에서 Laravel Artisan 명령어 숨기기"
subtitle: "'Hiding Laravel Artisan Console Commands from the list of available commands'의 번역 글입니다."
description: "Artisan 명령 목록에서 Laravel Artisan 명령어 숨기기"
type: "Laravel"
created_at: "2021-01-07"
updated_at: "2021-01-07"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/console.jpg"
order: 7
tags: ['laravel', 'laravel-news', 'artisan', 'tip', 'translate', 'short-article']
comments: true
---
만일 제품이나 패키지는 릴리즈 할 때, 설치만을 위해 사용되거나 특정 상황에서만 사용되는 Artisan 커맨드를 `php artisan`를 실행할 때 나오는 리스트에 표시하고 싶지 않을 수 있습니다.

# Command 의 hidden 속성을 이용해 감추기
라라벨에선 간단히 `hidden`속성을 설정하는 방법으로 커맨드를 숨길 수 있습니다.
```php
class DestructiveCommand extends Command
{
  protected $signature = 'db:resetdb';
  protected $description = 'DESTRUCTIVE! do not run this unless you know what you are doing';

  // Hide this from the console list.
  protected $hidden = true;
```

# setHidden 메소드를 이용해 감추기

한단계 더 나아가고 싶다면, `Brian Dillingham`가 트위터에 공유한 팁을 보면, `setHidden`메소드를 이용해 프로그래밍식으로 설정할 수 도 있습니다.
<blockquote class="twitter-tweet"><p lang="en" dir="ltr">✨Laravel Package Tip: Hide your install commands from `php artisan` after installed to keep things tidy. <a href="https://t.co/o4PK8xXkIk">pic.twitter.com/o4PK8xXkIk</a></p>&mdash; Brian Dillingham (@im_brian_d) <a href="https://twitter.com/im_brian_d/status/1345499284523855879?ref_src=twsrc%5Etfw">January 2, 2021</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

복사 붙여 넣기 가능한 버전:
```php
class Install extends Command
{
    protected $signature = 'package:install';
    protected $description = 'Install Package';

    public function __construct() 
    {
        parent::__construct();
        if (file_exists(config_path('package.php'))) {
            $this->setHidden(true);
        }
    }
```
With either of these set your console command will no longer show when running php artisan in the console, of course, someone can still run it manually if they know the signature so this is only useful for hiding in the list, not from stopping it from actually running.

어느 쪽이든 여러분의 콘솔 커맨드를 `php artisan` 리스트에서 숨길 수 있습니다. 물론 실행을 중지 시기는 게 아니기 때문에 직접 실행이 가능하므로 목록에서 숨길 때만 유용합니다.