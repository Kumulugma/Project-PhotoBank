<?php

return [
    // Podstawowe komunikaty
    'successfully' => 'pomyślnie',
    'error' => 'błąd',
    'warning' => 'ostrzeżenie',
    'info' => 'informacja',
    
    // Komunikaty kolejki
    'Job deleted successfully.' => 'Zadanie zostało pomyślnie usunięte.',
    'Job has been queued for retry.' => 'Zadanie zostało dodane do kolejki ponownego przetwarzania.',
    'Error resetting job: {error}' => 'Błąd resetowania zadania: {error}',
    'This job is already being processed.' => 'To zadanie jest już przetwarzane.',
    'This job has already been completed.' => 'To zadanie zostało już zakończone.',
    'Error updating job status: {error}' => 'Błąd aktualizacji statusu zadania: {error}',
    'Job processed successfully.' => 'Zadanie zostało pomyślnie przetworzone.',
    'Error processing job: {error}' => 'Błąd przetwarzania zadania: {error}',
    '{count} completed jobs cleared successfully.' => '{count} zakończonych zadań zostało pomyślnie usuniętych.',
    '{count} failed jobs cleared successfully.' => '{count} błędnych zadań zostało pomyślnie usuniętych.',
    '{count} przetwarzanych zadań zostało usuniętych.' => '{count} przetwarzanych zadań zostało usuniętych.',
    'Queue processor ran successfully. Processed {processed} jobs: {successful} succeeded, {failed} failed.' => 'Procesor kolejki zakończył pracę pomyślnie. Przetworzono {processed} zadań: {successful} pomyślnych, {failed} błędnych.',
    
    // Statusy zadań
    'Pending' => 'Oczekujące',
    'Processing' => 'Przetwarzane',
    'Completed' => 'Zakończone',
    'Failed' => 'Błędne',
    
    // Typy zadań
    'S3 Sync' => 'Synchronizacja S3',
    'Regenerate Thumbnails' => 'Regeneracja miniatur',
    'Analyze Photo' => 'Analiza zdjęcia',
    'Analyze Batch' => 'Analiza wsadowa',
    'Import Photos' => 'Import zdjęć',
    
    // Czasy względne
    'just now' => 'właśnie teraz',
    '{delta, plural, =1{# second} other{# seconds}} ago' => '{delta, plural, =1{# sekundę} few{# sekundy} other{# sekund}} temu',
    '{delta, plural, =1{# minute} other{# minutes}} ago' => '{delta, plural, =1{# minutę} few{# minuty} other{# minut}} temu',
    '{delta, plural, =1{# hour} other{# hours}} ago' => '{delta, plural, =1{# godzinę} few{# godziny} other{# godzin}} temu',
    '{delta, plural, =1{# day} other{# days}} ago' => '{delta, plural, =1{# dzień} other{# dni}} temu',
    '{delta, plural, =1{# week} other{# weeks}} ago' => '{delta, plural, =1{# tydzień} few{# tygodnie} other{# tygodni}} temu',
    '{delta, plural, =1{# month} other{# months}} ago' => '{delta, plural, =1{# miesiąc} few{# miesiące} other{# miesięcy}} temu',
    '{delta, plural, =1{# year} other{# years}} ago' => '{delta, plural, =1{# rok} few{# lata} other{# lat}} temu',
    
    // Dni tygodnia
    'Monday' => 'Poniedziałek',
    'Tuesday' => 'Wtorek', 
    'Wednesday' => 'Środa',
    'Thursday' => 'Czwartek',
    'Friday' => 'Piątek',
    'Saturday' => 'Sobota',
    'Sunday' => 'Niedziela',
    
    // Miesiące
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
    
    // Skrócone nazwy miesięcy
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
    
    // Skrócone dni tygodnia
    'Mon' => 'Pon',
    'Tue' => 'Wto',
    'Wed' => 'Śro',
    'Thu' => 'Czw',
    'Fri' => 'Pią',
    'Sat' => 'Sob',
    'Sun' => 'Nie',
    
    // Komunikaty uploadowania
    'Photo updated successfully.' => 'Zdjęcie zostało pomyślnie zaktualizowane.',
    'Photo has been approved and moved to main gallery.' => 'Zdjęcie zostało zatwierdzone i przeniesione do głównej galerii.',
    'Photo has been successfully deleted.' => 'Zdjęcie zostało pomyślnie usunięte.',
    'Successfully approved {count} photos.' => 'Pomyślnie zatwierdzono {count} zdjęć.',
    'Successfully deleted {count} photos.' => 'Pomyślnie usunięto {count} zdjęć.',
    'Successfully updated {count} photos.' => 'Pomyślnie zaktualizowano {count} zdjęć.',
    
    // Formatowanie czasu
    'Today' => 'Dzisiaj',
    'Yesterday' => 'Wczoraj',
    'Tomorrow' => 'Jutro',
    'in {delta, plural, =1{# second} other{# seconds}}' => 'za {delta, plural, =1{# sekundę} few{# sekundy} other{# sekund}}',
    'in {delta, plural, =1{# minute} other{# minutes}}' => 'za {delta, plural, =1{# minutę} few{# minuty} other{# minut}}',
    'in {delta, plural, =1{# hour} other{# hours}}' => 'za {delta, plural, =1{# godzinę} few{# godziny} other{# godzin}}',
    'in {delta, plural, =1{# day} other{# days}}' => 'za {delta, plural, =1{# dzień} other{# dni}}',
    
    // Inne przydatne komunikaty
    'No files found' => 'Nie znaleziono plików',
    'File uploaded successfully' => 'Plik został pomyślnie przesłany',
    'Error uploading file' => 'Błąd przesyłania pliku',
    'Invalid file type' => 'Nieprawidłowy typ pliku',
    'File too large' => 'Plik jest zbyt duży',
    'Processing...' => 'Przetwarzanie...',
    'Please wait...' => 'Proszę czekać...',
    'Operation completed' => 'Operacja zakończona',
    'Operation failed' => 'Operacja nieudana',
];