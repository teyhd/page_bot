# ВКбот расписание для ИАТУ

Бот на vk CallBackAPI v 5.80

# Новые функции!

  - Предполагает вашу учебную группу.
  - Скидывает расписание >7 пар

### Установка

```sh
$ apt-get install apache2 php mysql
$ git clone https://github.com/teyhd/TimeTable_VKBot.git
```
- Заполнить config.php.example

База данных, имеющая таблицу следующего вида:

| id | input | output | freq | 
| ------  | ------  | ------  | ------  | 
| 0 | привет | привет | 5 |
| 1 | как дела | как дела | 3 |
