<?php

return [
    // Komunikaty względnego czasu
    'just now' => 'właśnie teraz',
    '{delta, plural, =1{# second} other{# seconds}} ago' => '{delta, plural, =1{# sekundę} few{# sekundy} other{# sekund}} temu',
    '{delta, plural, =1{# minute} other{# minutes}} ago' => '{delta, plural, =1{# minutę} few{# minuty} other{# minut}} temu', 
    '{delta, plural, =1{# hour} other{# hours}} ago' => '{delta, plural, =1{# godzinę} few{# godziny} other{# godzin}} temu',
    '{delta, plural, =1{# day} other{# days}} ago' => '{delta, plural, =1{# dzień} other{# dni}} temu',
    '{delta, plural, =1{# week} other{# weeks}} ago' => '{delta, plural, =1{# tydzień} few{# tygodnie} other{# tygodni}} temu',
    '{delta, plural, =1{# month} other{# months}} ago' => '{delta, plural, =1{# miesiąc} few{# miesiące} other{# miesięcy}} temu',
    '{delta, plural, =1{# year} other{# years}} ago' => '{delta, plural, =1{# rok} few{# lata} other{# lat}} temu',
    
    'in {delta, plural, =1{# second} other{# seconds}}' => 'za {delta, plural, =1{# sekundę} few{# sekundy} other{# sekund}}',
    'in {delta, plural, =1{# minute} other{# minutes}}' => 'za {delta, plural, =1{# minutę} few{# minuty} other{# minut}}',
    'in {delta, plural, =1{# hour} other{# hours}}' => 'za {delta, plural, =1{# godzinę} few{# godziny} other{# godzin}}',
    'in {delta, plural, =1{# day} other{# days}}' => 'za {delta, plural, =1{# dzień} other{# dni}}',
    'in {delta, plural, =1{# week} other{# weeks}}' => 'za {delta, plural, =1{# tydzień} few{# tygodnie} other{# tygodni}}',
    'in {delta, plural, =1{# month} other{# months}}' => 'za {delta, plural, =1{# miesiąc} few{# miesiące} other{# miesięcy}}',
    'in {delta, plural, =1{# year} other{# years}}' => 'za {delta, plural, =1{# rok} few{# lata} other{# lat}}',
    
    // Nazwy dni i miesięcy
    'January' => 'Styczeń',
    'February' => 'Luty', 
    'March' => 'Marzec',
    'April' => 'Kwiecień',
    'May' => 'Maj',
    'June' => 'Czerwiec',
    'July' => 'Lipiec',
    'August' => 'Sierpień',
    'September' => 'Wrzesień',
    'October' => 'Październik',
    'November' => 'Listopad',
    'December' => 'Grudzień',
    
    'Monday' => 'Poniedziałek',
    'Tuesday' => 'Wtorek',
    'Wednesday' => 'Środa',
    'Thursday' => 'Czwartek',
    'Friday' => 'Piątek',
    'Saturday' => 'Sobota',
    'Sunday' => 'Niedziela',
    
    // Skrócone wersje
    'Jan' => 'Sty',
    'Feb' => 'Lut',
    'Mar' => 'Mar',
    'Apr' => 'Kwi',
    'Jun' => 'Cze',
    'Jul' => 'Lip',
    'Aug' => 'Sie',
    'Sep' => 'Wrz',
    'Oct' => 'Paź',
    'Nov' => 'Lis',
    'Dec' => 'Gru',
    
    'Mon' => 'Pon',
    'Tue' => 'Wto',
    'Wed' => 'Śro',
    'Thu' => 'Czw',
    'Fri' => 'Pią',
    'Sat' => 'Sob',
    'Sun' => 'Nie',
    
    // Podstawowe komunikaty
    'Today' => 'Dzisiaj',
    'Yesterday' => 'Wczoraj',
    'Tomorrow' => 'Jutro',
    
    // GridView
    'No results found.' => 'Brak wyników.',
    'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.' => 'Wyświetlono <b>{begin, number}-{end, number}</b> z <b>{totalCount, number}</b> {totalCount, plural, one{wpisu} few{wpisów} other{wpisów}}.',
    'Total <b>{count, number}</b> {count, plural, one{item} other{items}}.' => 'Łącznie <b>{count, number}</b> {count, plural, one{wpis} few{wpisy} other{wpisów}}.',
    'Page <b>{page, number}</b> of <b>{pageCount, number}</b>' => 'Strona <b>{page, number}</b> z <b>{pageCount, number}</b>',
    
    // Przyciski
    'Update' => 'Aktualizuj',
    'Delete' => 'Usuń',
    'Create' => 'Utwórz',
    'Save' => 'Zapisz',
    'Cancel' => 'Anuluj',
    'Reset' => 'Resetuj',
    'Submit' => 'Wyślij',
    'Search' => 'Szukaj',
    'View' => 'Zobacz',
    'Edit' => 'Edytuj',
    
    // Walidacja
    '{attribute} cannot be blank.' => 'Pole {attribute} nie może być puste.',
    '{attribute} is invalid.' => 'Pole {attribute} jest nieprawidłowe.',
    '{attribute} must be a string.' => 'Pole {attribute} musi być tekstem.',
    '{attribute} must be an integer.' => 'Pole {attribute} musi być liczbą całkowitą.',
    '{attribute} is too long (maximum is {max} characters).' => 'Pole {attribute} jest za długie (maksymalnie {max} znaków).',
    '{attribute} is too short (minimum is {min} characters).' => 'Pole {attribute} jest za krótkie (minimalnie {min} znaków).',
    
    // Potwierdzenia
    'Are you sure you want to delete this item?' => 'Czy na pewno chcesz usunąć ten element?',
    
    // Paginacja
    'First' => 'Pierwsza',
    'Last' => 'Ostatnia',
    'Next' => 'Następna',
    'Previous' => 'Poprzednia',
    
    // Sortowanie
    'Sort ascending' => 'Sortuj rosnąco',
    'Sort descending' => 'Sortuj malejąco',
    
    // Errory HTTP
    '404' => '404',
    'The requested page does not exist.' => 'Żądana strona nie istnieje.',
    'You are not allowed to access this page.' => 'Nie masz uprawnień do tej strony.',
];