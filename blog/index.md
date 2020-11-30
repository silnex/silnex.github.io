---
layout: default
title: "Blog"
description: 제가 관심있는 것들이 올라와요
main: true
post-header: true
header-img: img/space.jpg
header-class: space-background
---

<ul class="catalogue">
{% assign sorted = site.pages | sort: 'order' | reverse %}
{% for page in sorted %}
{% if page.blog == true %}
{% include post-list.html %}
{% endif %}
{% endfor %}
</ul>
