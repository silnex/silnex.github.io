---
layout: post
id: 30
title: "OAuth Study with Laravel Passport"
subtitle: "라라벨 Passport 를 이용한 OAuth 스터디"
description: "라라벨 Passport 를 이용한 OAuth 스터디를 진행합니다. OAuth 의 개념과 라라벨에서 Passport 를 이용한 구현 방법을 정리합니다."
type: "OAuth"
created_at: "2025-06-21"
updated_at: "2025-06-28"
blog: true
text: true
author: "silnex"
post-header: true
header-img: "img/oauth-study.png"
order: 30
tags: ["laravel", "OAuth", "passport", "api"]
comments: true
---

# OAuth 란?

OAuth는 사용자가 구글/네이버/카카오와 계정으로 내 서비스에 로그인(및 구글/네이버/카카오 리소스에 접근) 할 수 있도록 해주는 인증 프로토콜입니다.

그래서 보통은 Client 의 기능을 구현하여 연동을 하게되는데, 이번엔 OAuth 서버(OAuth Provider)를 구현하는 방법을 알아보겠습니다.

## OAuth 인증 흐름

![OAuth Flow](https://upload.wikimedia.org/wikipedia/commons/7/72/Abstract-flow.png)

출처: [위키피디아](https://en.wikipedia.org/wiki/OAuth)

OAuth Flow는 다음과 같은 단계로 진행됩니다:

1. [**Authorization Request**](#authorize-요청): 클라이언트가 사용자에게 권한을 요청합니다.
2. [**Authorization Grant**](#authorize-요청-response): 사용자가 권한을 승인하면, 클라이언트는 `Authorization Code`를 받습니다.
3. [**Access Token Request (Authorization Grant)**](#access-token-요청): 클라이언트는 `Authorization Code`를 사용하여 `Access Token`을 요청합니다.
4. [**Access Token Response (Access Token)**](#access-token-요청-resposne): 서버는 `Access Token`을 클라이언트에게 반환합니다.
5. [**Resource Access**](#api-요청): 클라이언트는 `Access Token`을 사용하여 보호된 리소스에 접근합니다.

또한 이번엔 OAuth 의 Proof Key for Code Exchange (PKCE) 를 이용하는 방법을 알아보겠습니다.

## PKCE란?

3번째 단계에서 **Access Token Request** 요청시 **client_id** 와 **client_secret** 를 이용하여 인증을 진행했지만,
PKCE는 **code_challenge** 와 **code_verifier** 를 이용하여 인증을 진행해  **client_secret** 을 사용하지 않음으로써  **client_secret** 이 노출되지 않게 보안을 강화합니다.

즉, `code_verifier` 를 Hashing 한 `code_challenge` 를 이용하여 각 요청시마다 `code_verifier` 를 이용해 1회용 인증을 진행하는 방식입니다.

---
# Laravel Passport

Laravel Passport는 Laravel 애플리케이션에서 OAuth2 서버를 쉽게 구현할 수 있도록 도와주는 패키지입니다.

```bash
laravel new laravel-passport-oauth # Laravel 프로젝트 생성
cd laravel-passport-oauth

php artisan install:passport --install # Passport 설치 및 기본 설정
```

이렇게 간단한 입력 만으로 Laravel Passport를 설치하고 기본적인 설정을 완료할 수 있습니다.

```bash
# php artisan tinker
> User::factory()->create();
```

그리고 테스트를 위해 `User` 를 생성합니다.

---
# OAuth Server 구성

> Laravel 12 업데이트 이후 몇 가지 변경사항이 있어 Laravel 11 이전에 알려진 설정과 다를 수 있습니다.

Passport를 설치한 후 Laravel에서 OAuth 서버를 구성하기 위해 다음 단계를 진행합니다:

- [로그인 라우트](#login-라우트-설정): 예시를 위해 최소한의 로그인 페이지와 로그아웃 페이지 설정
- Passport 설정 
  - [AuthServiceProvider](#authserviceprovider-생성): Passport 설정을 위한 서비스 프로바이더
  - [config/auth.php](#configauthphp-설정): 인증 설정 파일에 passport Guard 추가
  - [Models/User](#user-모델-설정): User 모델에 Passport 구현 추가
- [Authorize 를 위한 View (Blade 템플릿) 생성](#authorize-view-생성): OAuth 권한 요청을 위한 view 생성 (기존 Laravel 11 이전에는 기본 제공되었으나, Laravel 12 이후에는 직접 생성해야 합니다.)

### login 라우트 설정

간단한 예시를 위해 로그인 페이지와 로그아웃 페이지를 설정합니다.  
`POST /login` 제출하면, 로그인 처리하고 원래 가려고 했던 페이지로 리다이렉트(`intended`)하는 기능만 구현합니다.

```php
// routes/web.php
/**
 * Login 페이지 표시
 */
Route::get('/login', function () {
    return '<form method="POST" action="/login">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <button type="submit">Login</button>
            </form>';
})->name('login');

/**
 * Login 처리
 */
Route::post('/login', function () {
    Auth::loginUsingId(1); // 예시를 위해 ID가 1인 사용자로 로그인합니다.

    return redirect()->intended(); // 로그인 후 "원래" 가려고 했던 페이지로 리다이렉트합니다.
});

/*
 * 로그아웃 페이지
 */
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
```

### AuthServiceProvider 생성

```bash
php artisan make:provider AuthServiceProvider
```

```php
// app/Providers/AuthServiceProvider.php
namespace App\Providers;

class AuthServiceProvider extends ServiceProvider
{
    // ...
    public function boot(): void
    {
        // Laravel 12 이후 부턴 authorize view 가 제공되지 않기에 직접 설정/생성해야 합니다.
        Passport::authorizationView('auth.oauth.authorize');

        // Passport는 기본적으로 1년 동안 액세스 토큰을 유지하에 이를 일반적인 사용 사례에 맞게 조정합니다.
        Passport::tokensExpireIn(CarbonInterval::days(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));

        // tokensCan으로 토큰 권한의 범위(scope)를 정의할 수 있습니다.
        Passport::tokensCan([
            'user:read'  => '사용자 정보 조회',
            'user:write' => '사용자 정보 수정',
        ]);
    }
}

```

### config/auth.php 설정

```php
// config/auth.php
[
    // ...

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'passport', // Passport 드라이버를 추가합니다.
            'provider' => 'users',
        ],
    ],

    // ...
]
```

### User 모델 설정

```php
// app/Models/User.php
namespace App\Models;

// ...
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable // 인터페이스 추가
{
    // ...
    use HasApiTokens; // Passport 토큰 기능을 사용
}
```

### authorize view 생성
{% raw %}
```html
<h1>Authorize Application</h1>

<p>
  This application is requesting access to your account. Please review the
  permissions it is requesting.
</p>

<h2>{{ $client->name }}</h2>

@unless (empty($scopes))
<h3>Requested Scopes</h3>
<ul>
  @foreach ($scopes as $scope)
  <li>{{ $scope->description }}</li>
  @endforeach
</ul>
@endunless

<form method="POST" action="{{ route('passport.authorizations.approve') }}">
  @csrf
  <input type="hidden" name="client_id" value="{{ $client->id }}" />
  <input type="hidden" name="auth_token" value="{{ $authToken }}" />

  <button type="submit">Authorize</button>
</form>
<form method="POST" action="{{ route('passport.authorizations.deny') }}">
  @csrf
  <input type="hidden" name="client_id" value="{{ $client->id }}" />
  <input type="hidden" name="auth_token" value="{{ $authToken }}" />
  <input type="hidden" name="_method" value="DELETE" />

  <button type="submit">Deny</button>
</form>

<a href="{{ route('logout') }}">Logout</a>
```
{% endraw %}

Authorize view 에 전달 되는 파라미터:

| 파라미터    | 설명                      | 타입                       |
| ----------- | ------------------------- | -------------------------- |
| `client`    | 클라이언트 정보           | `\Laravel\Passport\Client` |
| `user`      | 현재 로그인한 사용자 정보 | `\App\Models\User`         |
| `scopes`    | 요청된 권한 범위          | `array`                    |
| `request`   | 요청 정보                 | `\Illuminate\Http\Request` |
| `authToken` | 인증 토큰                 | `string`                   |

이렇게 **Laravel Passport** 를 사용해 OAuth 서버를 구성할 수 있습니다.

---
# OAuth Client 구현

*Next-Auth*, *openid-connect*와 같은 OAuth 클라이언트 라이브러리를 사용해 Passport OAuth 서버와 연결할 수 있지만,  
이번 글에선 Client를 직접 하나씩 리퀘스트 단위로 따라가 면서 테스트 해보겠습니다.

### OAuth Client 생성

```bash
php artisan passport:client --public # PKCE 를 사용하는 클라이언트 생성

  What should we name the client? [Laravel]
❯ OAuth Client               

  Where should we redirect the request after authorization? [http://localhost:8000/auth/callback]
❯ http://localhost:8000/client/callback # 동작하지 않지만 이후 수기로 하기에 아무렇게 설정

 Would you like to enable the device authorization flow for this client? (yes/no) [no]:
 > no

   INFO  New client created successfully.  

  Client ID ............................................... 0197b528-aa82-73c1-b5f9-044fd1f5d17f
```

### code_verifier 및 code_challenge 생성

PKCE를 사용을 사용하면 `client secret` 을 사용하지 않고,   
`code_challenge`와 `code_verifier`를 이용해 요청의 무결성을 검증을 합니다.  

예시로는 PHP 를 사용했지만 다른 언어에서도 비슷한 방식으로 구현할 수 있습니다.

```php
function make_pkce_challenge(): array
{
    // 32바이트 랜덤 문자열 생성
    $code_verifier = bin2hex(random_bytes(32)); 
    // SHA-256 해시를 Base64 URL 인코딩하여 code_challenge 생성 (RFC 7636)
    $code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '='); 

    return [
        'code_verifier' => $code_verifier,
        'code_challenge' => $code_challenge,
    ];
}
```

예제로 생성된 `client_id`, `code_verifier`와 `code_challenge`는 다음과 같습니다:
  
> client_id: `0197b528-aa82-73c1-b5f9-044fd1f5d17f`  
> code_verifier: `bce329b68638c0c5d7abf2a77a47b1199aad34099f06ee34ee6613211aa0ad53`   
> code_challenge: `xeuNegOLLBD2GfkSUh81Kwyx9O0jsmgR1NwZ729RdL4`

## OAuth 요청 및 Access Token 요청

이제 OAuth 서버에 요청을 보내고, Access Token을 요청하는 과정을 살펴보겠습니다.

### Authorize 요청

> 이 요청은 브라우저로 직접 호출할 수 있습니다.

```http
GET http://localhost:8000/oauth/authorize HTTP/1.1
```

Parameters:
```
client_id=0197b528-aa82-73c1-b5f9-044fd1f5d17f
    &response_type=code
    &redirect_uri=http://localhost:8000/client/callback
    &code_challenge=xeuNegOLLBD2GfkSUh81Kwyx9O0jsmgR1NwZ729RdL4
    &code_challenge_method=S256
    &scope=user:read+user:write
    &state=xyz123
```

![OAuth 로그인 페이지](img/oauth-login.png)

파라미터를 포함한 요청을 보내면, 먼저 로그인이 되지 않았기에 로그인 페이지로 리다이렉트됩니다.  
`Login` 버튼을 눌러 로그인을 진행합니다.  
만일 실제 서비스의 경우 아이디와 비밀번호나 2FA 인증등을 통해 로그인을 진행하게 됩니다.

![OAuth 권한 요청 페이지](img/oauth-authorize.png)

[Authorize view](#authorize-view-생성) 에서 생성한 페이지가 표시되며, 사용자 권한을 요청하는 페이지가 뜨게되고,  
`Authorize` 버튼을 눌러 권한을 승인하면 클라이언트 생성시 전달받은 Redirect URL 로 `code`와 `state` 파라미터가 포함된 URL로 리다이렉트됩니다.

<div id="authorize-요청-response"></div>
Response:
```http
HTTP/1.1 302 Found
Location: http://localhost:8000/client/callback?code=def50200bc922aee7135594e81395daed8626092b5779f2e9bf717a4ae13b3412d1266771144a330b29d0e5ed222632f4ee73b2b91b6109f6a68448abc3c03d0f3d21c8b4341e56169288a1de9b1ab9fac55f12296f66d01a8ced0efdce4f8f1a2edf706d5500149a948f1b13e6816ea9185be5986b1220b71e573ffa573d1124445e8d0adfa6093103aa0c601e8145b69c06e20e2a5896d12d2265fef08d8652f495b5d24e9dc8bbbc3dda9518a693a4a855e8f9efdcc0d8755e3485b1543d73f45aa8f977d3b9132f0e3b153bdf8a6d860a8956204991b231a827d69ea7f28b221124d163947c70e231b6789c3ad047371a50beed65be16f10f68a5a676e157754567200b642dcd4a6c1069fee5d898ec0e92d328beca853fca18bf17ee7f83a494def53df5c512eac4a7a1ca2cd21ea4f7bb90a4baf3cb465ddbc7ce687510be85400b56a9a7b06fe7963d8524455c6dae47ec220ae4fef33cfc69dd1380b87f3ff2b26ec21b4c47abaa48d514ff52f74f6c10df759f85044b68871f808c0ee94a03ffd6529b1bb07ec245e1570e50029d087c6502d43ca8e41f7de9aac308730ec308f5a10b5e707d1e5808e87211ff9353a1ede381063bdc48e49fa64cb8825aa9f3a5980687406b17e5bb4&state=xyz123
```

![OAuth Client Callback](img/oauth-client-callback.png)
지금은 client 측에 callback 이 구현되지 않았지만,  
이 페이지에서 code 값을 추출해 직접 Access Token을 요청해 보겠습니다.

### Access Token 요청

> 이 요청은 Postman과 같은 HTTP 클라이언트 도구를 사용하여 진행하는게 편합니다.

```http
POST http://localhost:8000/oauth/token HTTP/1.1
```
Parameters:
```
grant_type=authorization_code
    &client_id=0197b528-aa82-73c1-b5f9-044fd1f5d17f
    &code=def50200bc922aee7135594e81395daed8626092b5779f2e9bf717a4ae13b3412...
    &redirect_uri=http://localhost:8000/client/callback
    &code_verifier=bce329b68638c0c5d7abf2a77a47b1199aad34099f06ee34ee6613211aa0ad53
```
<div id="access-token-요청-resposne"></div>
Response:
```json
{
  "token_type": "Bearer",
  "expires_in": 1296000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIwMTk3YjUyOC1hYTgyL...",
  "refresh_token": "def5020014a353d0c1727d405fe3643afa88ce777bae873732f337c0e8af5913f..."
}
```

Access Token을 요청하면, Bearer Token과 Refresh Token을 포함한 응답을 받게 됩니다.

### API 요청

> 이 요청은 Postman과 같은 HTTP 클라이언트 도구를 사용하여 진행하는게 편합니다.

```http
GET http://localhost:8000/api/user HTTP/1.1
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIwMTk3YjUyOC1hYTgyL...
```
Response:
```json
{
  "id": 1,
  "name": "Blanca Bergstrom III",
  "email": "wferry@example.net",
  "email_verified_at": "2025-06-19T09:37:35.000000Z",
  "created_at": "2025-06-19T09:37:35.000000Z",
  "updated_at": "2025-06-19T09:37:35.000000Z"
}
```

#### 추가+

만일 토큰을 생성할때 사용한 `scope`를 사용하고 싶다면, `routes/api.php`의 미들웨어로 아래와 같이 추가해주면 됩니다.

```php
// routes/api.php
use Laravel\Passport\Http\Middleware\CheckToken;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:api'], CheckToken::using('user:read')); // scopes:user:read 는 Laravel 11 이전에 사용되던 방식입니다.

```

이렇게 지정하면, `user:read` 권한이 있는 토큰만 `/api/user` 엔드포인트에 접근할 수 있습니다.

# 마치며

~~1달 못 채운~~ 2년만에 새로운 글입니다. OAuth Server 에 대한 필요가 생겨 공부를 해보는 중에 OAuth 서버를 구현하는 글이 얼마 없고,  
Laravel Passport 를 사용해 구현하는 글은 더욱이 없어 이후의 저를 위해 구현 및 테스트를 진행한 과정 그대로 따라할 수 있게 정리해봤습니다.

OAuth 자체가 복잡한 프로토콜이지만, 가능한 따라하면서 해볼수 있도록 글의 순서나 내용을 조합하고 NextAuth 와 같은 추가적인 라이브러리 없이 직접 플로우를 따라갈 수 있게 했습니다만, HTTP 요청을 할때는 Postman 과 같은 도구를 사용하면 더 편하게 테스트할 수 있습니다.

이 글이 OAuth를 이해하고 Laravel Passport를 사용하는 데 도움이 되길 바랍니다.

#### Resources

- [OAuth Wikipedia](https://en.wikipedia.org/wiki/OAuth)
- [Laravel Passport Documentation](https://laravel.com/docs/passport)
- [What is OAuth 2.0?](https://stytch.com/blog/what-is-OAuth-2-0/)
