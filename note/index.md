---
layout: default
type: Notice
title: "매일 Note"
post-header: true
header-img: img/note-mail.jpg
---

<div class="catalogue-head">
    <p class="catalogue-introduce">가볍게 알게된 내용들을 정리하는 Note입니다.</p>
    <small class="text-muted">여기 내용들이 모여 하나의 글이 되죠</small>
</div>

<ul class="catalogue">
{% assign sorted = site.pages | sort: 'order' | reverse %}

{% for page in sorted %}
  {% if page.hidden == true %}{% continue %}{% endif %}
  {% if page.note == true %}
    {% include note-list.html %}
  {% endif %}
{% endfor %}
</ul>
