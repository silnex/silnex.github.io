---
layout: default
type: Notice
title: "매일 Note"
post-header: true
header-img: img/note-mail.jpg
---

<div class="catalogue-head">
    <p class="catalogue-introduce">알게된 내용들을 가볍게 정리하는 Note Catalogue</p>
    <small class="text-muted">작은 Note들이 모여 하나의 글이 되겠죠</small>
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
