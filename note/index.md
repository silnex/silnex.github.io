---
layout: default
type: Notice
title: "매일 Note"
post-header: true
header-img: img/note-mail.jpg
---


<ul class="catalogue">
{% assign sorted = site.pages | sort: 'order' | reverse %}

{% for page in sorted %}
  {% if page.hidden == true %}{% continue %}{% endif %}
  {% if page.note == true %}
    {% include note-list.html %}
  {% endif %}
{% endfor %}
</ul>
