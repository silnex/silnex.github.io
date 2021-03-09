---
layout: note
type: "TIP"
title: Laravel 실행 중 기본 guard 변경 방법
subtitle: laravel의 기본 guard (app.defaults.guard) 변경 방법
note: true
article: false
order: 4
created_at: "2021-03-09"
updated_at: "2021-03-09"
comments: true
---

1. Config::set('app.defaults.guard', 'admin')
2. config(['app.defaults.guard' => 'admin'])
3. Auth::setDefaultDriver('admin')