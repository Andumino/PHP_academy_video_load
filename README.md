Загрузка видео с сайта php-academy.kiev.ua

В файле конфигурации "config.php" необходимо заполнить:
  - имя пользователя (константа "LOGIN");
  - пароль (константа "PASSWORD");
  - папку, в которую складывать видео (константа "DESTINATION_FOLDER");
  - массив страниц, с которых нужно загрузить видео (константа "URL_VIDEO_PAGES").
  
В массиве можно задать несколько страниц. 
Можно указывать как страницы с несколькими видео, так и конкретные страницы просмотра.
Например:
  define('URL_VIDEO_PAGES', [
      'https://php-academy.kiev.ua/video?group_id=111&page=1',
      'https://php-academy.kiev.ua/video?group_id=111&page=2',
      'https://php-academy.kiev.ua/video?page=2',
      'https://php-academy.kiev.ua/video/view/984-symfony-basics-1',
      'https://php-academy.kiev.ua/video/view/984-symfony-basics-2',
      'https://php-academy.kiev.ua/video/view/984-symfony-basics-3'
      ]);
В таком случае будут загружаться все видео с указанных страниц.

Если, например, оставить массив ссылок пустым:
  define('URL_VIDEO_PAGES', []);
то будут загружаться все видео группы в которой состоит указанный пользователь.  

Далее запустить скрипт из консоли: php video_load.php 
