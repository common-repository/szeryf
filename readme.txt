=== Plugin Name ===
Contributors: LukaszWiecek
Tags: szeryf, wordpress, hotlink, protection
Requires at least: 2.7
Tested up to: 2.8.6
Stable tag: 0.1.3

Zadaniem Szeryfa jest maskowanie rzeczywistego adresu wszystkich publikowanych załączników. Część ścieżki do każdego z załączników jest zastępowana przez losowo wygenerowany ciąg znaków.

== Description ==
Wtyczka w żaden sposób nie ingeruje w strukturę plików na serwerze, a jedynie podmienia ścieżki do załączników w trakcie generowania strony. Następnie dzięki jednej regułce dodanej w pliku .htaccess klucz jest zamieniany na pierwotną ścieżkę i załącznik jest ładowany. Oczywiście etap podmieniania klucza na prawdziwą ścieżkę odbywa się po stronie serwera i jest niewidoczny dla użytkownika przeglądającego stronę.

== Installation ==

1. Skopiuj wszystkie pliki do katalogu **/wp-content/plugins/szeryf/**
2. Aktywuj plugin w panelu administracyjnym
3. Skonfiruruj ustawienia pluginu w zakładce **Ustawienia/Szeryf**

== Screenshots ==

1. Strona wstępnej konfiguracji wtyczki Szeryf

== Other Notes == 

**Historia wersji:**

* 0.1.3 Pierwsza publicznie dostępna wersja wtyczki

== Frequently Asked Questions ==

= Gdzie mogę dowiedzieć się o wtyczce Szeryf? =
Po szczegóły zapraszam na oficjanlą stronę wtyczki [http://więcek.pl/projekty/szeryf](http://xn--wicek-k0a.pl/projekty/szeryf)
