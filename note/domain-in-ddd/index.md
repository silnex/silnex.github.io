---
type: "Dev Methodology"
title: DDD에서의 Domain은?
subtitle: Domain-Driven Design의 "Domain" 이란?
note: true
article: false
order: 3
created_at: "2021-03-02"
updated_at: "2021-03-02"
---

Domain-Driven Design (aka. DDD)에서의 도메인은 "사용되는 것"을 의미한다.  

Ex. "회원가입, 로그인"은 "회원" 도메인 안에 있다.

But. "회원 도메인"을 "등록 도메인"과 "로그인 도메인"으로 쪼개면,  
"등록 도메인"에는 "회원가입", "회원탈퇴" 가 있고,  
"로그인 도메인"에는 "로그인", "로그아웃" 가 있다.

즉, 쪼개는 어떻게 쪼개어 하나의 "도메인"으로 바라보는지는 개발자의 선택에 달렸다.