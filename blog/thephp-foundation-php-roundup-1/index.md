---
layout: post
id: 26
title: "PHP 라운드업 #1"
subtitle: "PHP 정기 정리글 #1"
description: "PHP 재단 블로그에서 연재되는 PHP 정리 글의 번역입니다."
type: "PHP"
created_at: "2022-05-11"
updated_at: "2022-05-11"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/php_foundation.png"
order: 26
tags: ["thephp-foundation", "php-roundup", "translate"]
comments: true
---

## 원문

> [PHP Roundup #1](https://thephp.foundation/blog/2022/04/28/php-roundup-1/)  
> Published on Apr 28, 2022 by [Ayesh Karunaratne](https://twitter.com/Ayeshlive)

첫 번째 PHP 라운드업(정리 글)에 오신 것을 환영합니다.
PHP 재단과 PHP 기여자 분들이 PHP를 개선한 내용을 정기적으로 업데이트할 예정입니다.

PHP 라운드업은 PHP 뉴스 및 향후 변경 사항에 대한 기사 출처인 [PHP.Watch](php.watch)의 Ayesh Karunaratne이 작성했습니다.

이 시리즈에선 PHP 언어의 흥미롭고 주요 개선 사항들에 대해 주목합니다.  
전통적으로 PHP 팀은 매년 말에 인터프리터의 새로운 버전을 공개합니다. 하지만 변경과 개선사항은 한 해에 걸쳐서 논의되고 구현됩니다.

You don’t necessarily have to be a PHP Foundation backer to follow the PHP Roundup. We’ll be publishing the posts on our website, and you can subscribe to a newsletter:

PHP 라운드업을 구독하려면 반드시 PHP 재단의 후원자가 될 필요는 없습니다.  
웹사이트에 게시물을 게시할 예정이며 뉴스레터를 구독 할수 있습니다.:

> 원문에 [뉴스레터 구독 하기](https://thephp.foundation/blog/2022/04/28/php-roundup-1/#subscribe-to-php-foundation-updates)부분에 이메일을 넣고 구독 하실 수 있습니다!

현재 PHP 재단은 PHP의 유지 관리 및 새로운 기능에 대해 작업하는 6명의 파트타임 PHP 기여자를 서포트 하고 있습니다.
유지 관리는 버그를 수정하는 것에서 그치지 않고 기술 부체를 줄이고, PHP로 작업하는 모든 사람의 삶을 더 쉽게 만들고 있습니다.
PHP 재단에서 자금을 지원하는 기여자 분들은 코드, 문서 및 토론에 대해 다른 기여자 분들과 함께 협력합니다.

💜가 마크된 내용들은 PHP 재단 팀에서 수행한 작업들입니다.

그럼 바로 업데이트 내용을 살펴보겠습니다!

# RFC 업데이트

매번 PHP의 주요 변경점들은 PHP 커뮤니티의 합의가 논의되고 구현됩니다.
각 RFC는 변경 사항을 제안하고 PHP Internals 커뮤니티에서 기본적은 2주 동안 투표를 진행합니다.

- Accepted: 독립적인 타입으로 `null` 및 `false` 허용 ([Allow null and false as stand-alone types](https://wiki.php.net/rfc/null-false-standalone-types)) 💜

George Peter Banyard는 38표 모두 찬성으로 만장일치로 승인되었으며 현재 PHP로 병합되었습니다.

이 변경 이전에는 null 및 false를 Union Type의 일부로만 사용할 수 있었지만, 독집 적인 타입으로는 사용할 수 없었습니다.
더 나아가 이번 변경은 PHP의 타입 시스템을 더 다양하고 안전하게 향상 시킵니다.
`true`를 타입으로 추가하는 것을 제안하는 RFC는 아래의 `true 타입 추가` RFC 를 참조해주세요.

Derick Rethans가 주최하는 [PHP Internals News 팟캐스트](https://phpinternals.news/97)에서 이 RFC에 대해 자세히 알아보세요.

- Implemented: 백 트레이스의 매개변수 수정 ([Redacting parameters in back traces](https://wiki.php.net/rfc/redact_parameters_in_back_traces))

Tim Düsterhus의 RFC는 24:1 찬성으로 승인됬으며, PHP 코어에 구현되어 있습니다.

This RFC proposed adding a #[SensitiveParameter] attribute that redacts the parameter's actual value when it is spewed out in stack traces and var_dump output.
The attribute can be used to prevent leaking sensitive information in debugging logs.

이 RFC에서 제안된 `#[SensitiveParameter]` 속성은 스택 트래킹과 var_dump 으로 출력할 때 파라미터의 실제 값을 수정합니다.
이 속성으로 디버깅 로그에 민감한 정보가 노출되는 것을 막을 수 있습니다.

Derick Rethans가 주최하는 [PHP Internals News 팟캐스트](https://phpinternals.news/97)에서 이 RFC에 대해 자세히 알아보세요.

- Discussion: true 타입 추가 ([Add true type](https://wiki.php.net/rfc/true-type)) 💜

PHP에 `true` 타입을 추가하는 George Peter Banyard의 RFC는 지금 논의 중에 있습니다.  
PHP 8.0의 `Union Type`에선 `false`를 `Union Type`중 하나로 추가 되었습니다. 하지만 `true`는 추가되지 않고 남은 상태입니다.  
이 RFC는 `true` 타입을 추가해 완성할 것을 제안합니다.

- Accepted: 정의되지 않은 변수 에러 승격 ([Undefined Variable Error Promotion](https://wiki.php.net/rfc/undefined_variable_error_promotion))

Mark Randall가 제안한 RFC는 33:8 찬성표로 승인 되었습니다.

이 제안은 지금의 PHP가 정의되지 않은 데이터에 접근할 때 Warning을 발생(PHP 8.0+)시키는 대신 PHP 9.0에선 에러 오류를 발생시키게 변경합니다.

- Voting: 정의되지 않은 속성 오류 프로모션 ([Undefined Property Error Promotion](https://wiki.php.net/rfc/undefined_variable_error_promotion))

Mark Randall의 또 다른 RFC는 정의되지 않은 변수로 현재 허용되는 변경 사항과 유사하게 정의되지 않은 클래스 속성 액세스에서 오류를 발생시킬 것을 제안합니다.  
특히, PHP 8.2는 동적으로 클래스 속성 생성 시 사용 중단 알림을 표시합니다(몇 가지 예외 제외).

- Voting: 읽기전용 클래스 ([Readonly classes](https://wiki.php.net/rfc/readonly_classes)) 💜

RFC by Máté Kocsis proposes to add support for readonly classes. In such a class, all properties are readonly and dynamic properties are forbidden. Voting is scheduled to start on April 27th.

Máté Kocsis의 RFC는 읽기 전용 클래스에 대한 지원을 추가할 것을 제안합니다.
이러한 클래스에서 모든 속성은 읽기 전용이며 동적 속성은 금지됩니다.
투표는 4월 27일부터 시작될 예정입니다.

- Accepted: `${}` 문자열 보간 사용 중단 ([Deprecate `${}` string interpolation](https://wiki.php.net/rfc/deprecate_dollar_brace_string_interpolation)) 💜

Ilija Tovilo의 RFC는 31:1의 찬성 투표로 승인되었습니다.

This RFC proposes to deprecate "${foo}" and "${(foo)}" string interpolation patterns. It does not deprecate the standard "{$foo}" pattern.

이 RFC는 `"${foo}"`와 `"${(foo)}"` 문자열 보간 패턴 사용 중단을 제안합니다.  
다만, `"{$foo}"` 문자열 보간 패턴을 사용 중지 하는 규칙은 아닙니다.

- Accepted: utf8_encode 및 utf8_decode 사용 중단 및 제거 ([Deprecate and Remove utf8_encode and utf8_decode](https://wiki.php.net/rfc/remove_utf8_decode_and_utf8_encode))

Derick Rethans가 주최하는 [PHP Internals News 팟캐스트](https://phpinternals.news/97)에서 이 RFC에 대해 자세히 알아보세요.

# 병합된 PRs 과 커밋

PHP에 사소한 변경 중 일부는 먼저 PHP GitHub 프로젝트에 대한 pull 요청으로 이루어집니다. 그리고 PHP 핵심 관리자가 수용할 수 있다고 판단되는 경우 공식적인 RFC 프로세스를 거치지 않고 병합됩니다.  
기존 기능을 손상시키는 PR에 플래그를 지정하는 자동 테스트가 있습니다.
커뮤니티에서 제안된 사소한 변경의 대부분은 PR을 통해 이루어집니다.

> [변경 사항 목록](https://thephp.foundation/blog/2022/04/28/php-roundup-1/#merged-prs-and-commits)

# 메일링 리스트 토론

- (Canonicalize "iterable" into "array|Traversable" and Reflection)[https://externals.io/message/117577], started by George Peter Banyard. 💜
- [MySQLi Execute Query RFC](https://externals.io/message/117486), started by Craig Francis.
- (NULL Coercion Consistency)[https://externals.io/message/117501], also started by Craig Francis.

# PHP Foundation을 지원해주세요!

PHP Foundation에서는 PHP 언어를 지원, 홍보 및 발전시킵니다. 
PHP 프로젝트에 기여할 6명의 시간제 PHP 핵심 개발자를 재정적으로 지원합니다. 
(OpenCollective)[https://opencollective.com/phpfoundation]에서 PHP Foundation 지원을 도울 수 있습니다.

모든 후원자에게 큰 감사를 드립니다. - PHP Foundation의 모든 팀원 드림

뉴스레터 플랫폼을 제공하기 위해 [mailcoach.app](mailcoach.app)을 특별히 언급했습니다.

재단의 최신 업데이트를 받으려면 Twitter [@ThePHPF](https://twitter.com/thephpf) 우리를 팔로우하세요!

💜️ 🐘

## 몇 줄 평

오랜만에 글 작성이네요!  
점검 PHP 커뮤니티의 활성화가 눈에 띌 정도로 활발해지고 있고 안정적인 재단 설립까지 이어질 수 있었던 것 같아요!

PHP Roundup 시리즈로 연재된다고 하니 앞으로 많이 지켜봐 주세요!