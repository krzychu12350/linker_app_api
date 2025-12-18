TO DO:

1. Endpoint do zmiany nazwy grupy edycja V

2. Preferencja tabelka z jakimi religami wiekami itd. ma wyswiela w niezmatchowanych

3. uzytkownicy z rola moderator z paginacja (wyszukiwanie nazawa koleumny i query), 
endpoint do dodania moderatora, usuniecie moderatora

4. endpoint do pobierania wszystkich zgloszen z paginacja, wyswietlenie pojedynczego zgloszenia 
(dane zglaszengo usera, pliki zgloszenia, tytul, opis),
endpoint do edycji zgloszenia gdzie mozna.

Obsluga edycji zgloszenia
1. Odrzucenia - zamiasna statusu na odrzucone
2. Zbanowanie uzytkownika na czas okreslony i permanentne zbanowanie (soft delete, nie da sie juz zrobic na ten mobile) 
- akutalizacja endpointu do rejestracji wykluczenie uzytkownik zbannowych na nieokreslony czas 

dodanie przy podjeciu decyzji ewent o wyniku zgloszenia (zgloszony uzytkownik zostal zbanowany, zgloszenie odrzucone)
tj. dodanie do bazy nowego powiadomienia i wysylnie przez pushera do fronetu przez triggerowania zdarzenia

Endpoint do wysylania maila z przypomniem hasla
endpoint do restowania hasla z token, nowym haslem i potrawierdzeniem hasla

endpoint do usuwania konta z powiazanymi danymi

blokowanie usera przez usera do sprawdzenia
dodanie usueniecia uzytkownikow z grupy

dodanie dokumentacji ala swagger do api zeby wkleic screena do worda i opisac

FRONTEND NOTATKI
zamiana spa na pwa

zostalo
1. odfiltrowanie w swipach po preferencjach V
2. poprawa walidacja preferencji (szczegółów) moga przychodzic tylko detale, ktore nie maja parenta V
3. dodać w rejestracji/logowaniu7 czy ktoś nie ma permanentnie zbanowanego konta
4. obsługa sytuacja jak ktoś ma czasowo zbanowane konto to jest zwracane info ile jeszcze czasu ma zbanowane V
5. dodanie powiadomien do bazy jak zostanie podjęta decyzja odnośnie zgłoszenia i ztriggerowanie pushera i ta sama sytuacja przy dodanie eventu/głosu
6. przy dodaniu moderatora przychodzi email potwierdzeniem konta i potem haslem startowym
7. Usuwanie matcha V
8. Doddanie id uzytkownika zglaszanego do raportu V
9. Zmiana admina grupy jesli opusci on grupe, na losowego usera, typ uzytkownika w grupie user/admin, endpoint do zmiany grupy admina V