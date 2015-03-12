Руководство по использованию Migration Tool 0.3.1 (далее mtool)
=====

1. Подготовка к работе
---

Перед началом работы с mtool необходимо его скофигурировать, используя команду: 
```
php mtool.php config
```

Доступные параметры: 

* настройка бд
 - adapter  - адаптер бд, может принимать значения: `mysql` (по-умолчанию), `pgsql`;
 - host 	 - хост бд, может принимать любые значения;
 - username - пользователь бд, может принимать любые значения;
 - password - пароль пользователя бд, может принимать любые значения;
 - dbname 	 - имя бд, может принимать любые значения;
 - charset  - кодировка соединения с бд, значение по-умолчанию: `utf8` (в данный момент параметр не используется);
* настройка путей
 - path 	 - путь для хранения файлов миграций и снепшотов, может принимать любые значения, по-умолчанию: `../migrations/`;
* доп. настройки
 - table 	 - таблица в бд хранящая имена примененных миграций, может принимать любые значения, по-умолчанию: `~migrations`.
	
2. Начало работы
---

Для создания новой миграции необходимо вызвать команду:
```
php mtool.php create XXX
```
, где XXX - номер версии в числовом варианте. В результате чего будет создан каталог указанный в настройках (если он не был создан заранее) и два файла миграции:

```
XXX_********_******_**_down.sql
XXX_********_******_**_up.sql
```
, где XXX_********_******_** - имя миграции, соответствующее её версии и времени создания.


3. Применение и отмена миграций
---

После того, как созданные в п.2 файлы, были заполнены запросами, миграцию можно применить используя команду:
```
php mtool.php upgrade
```
> Вместо `upgrade` допустимо применение алиаса `up`

Для отмены миграции необходимо использовать комманды:
```
php mtool.php downgrade
```
> Вместо `downgrade` допустимо применение алиаса `down`

Необходимо заметить, что если количество не примененных миграций больше одного, то привызове команды:
```
php mtool.php upgrade
```
будут применены все не примененных миграции.

Если же количество примененных миграций больше одной, то вызов команды
```
php mtool.php downgrade
```
отменит только последнюю примененную миграцию.

3.1. Применение/отмена миграций по версии
---

Можно указывать для команд применения/отмены миграций параметр `-v XXX`, где XXX - номер версии.

При этом применение/отмена миграций будет происходить только в рамках миграций для текущей весии.

> Внимание! Будьте внимательны, если отменяете миграции предыдущих версий, и в применённых миграциях следующих версий используются те же таблицы - тогда возможны конфликты по этим таблицам в будущем.

3.2. Применение одной отдельной миграции
---

Для команды применения миграций можно указать параметр `-u XXX_********_******_**`, где XXX_********_******_** - номер конкретной миграции.

При этом будет применена только одна эта миграция, где бы она не находилась.

Допустимо использование команд применения/отмены миграций с параметром (имя миграции вида `XXX_********_******_**`).

Примеры:

Существуют такие миграции: 
```
00000000_000000_10[+]
00000000_000000_20[+]
00000000_000000_30[-]
00000000_000000_40[-]
00000000_000000_50[-]
```
> Примечание. "+" - примененная миграция, "-" - не примененная миграция

* 
```
php mtool.php upgrade
```

будут применены миграции: `00000000_000000_30`, `00000000_000000_40`, `00000000_000000_50`.

* 
```
php mtool.php upgrade 00000000_000000_40
```
будут применены миграции: `00000000_000000_30`, `00000000_000000_40`.

* 
```
php mtool.php downgrade
```
будут отменены миграции: `00000000_000000_20`.

* 
```
php mtool.php downgrade 00000000_000000_10
```
будут отменены миграции: `00000000_000000_20`, `00000000_000000_10`.

> При этом миграции будут применяться и отменяться все предыдущие или последующие, вне зависимости от версий !!!
	
4. Получение информации о текущих мирациях
---

Для получения имени текущей миграции команда:
```
php mtool.php current
```

Для получения списка миграций команда:
```
php mtool.php list
```
Данная команда выводит подсвеченый разными цветами список миграций.

Используются такие цвета:

 * голубой - миграция не применена и доступна в файловой системе;
 * зеленый - миграция применена и доступна в файловой системе;
 * красный - миграция применена, но не доступна в файловой системе.
	
5. Создание и применение снепшотов
---

Для создания снепшота текущего состояния бд команда:
```
php mtool.php snapshot
```
в результате данной команды будет создан файл снепшота: `********_******_**_snapshot.sql`
, где ********_******_** - имя снепшота, соответствующее времени его создания.

При создании снепшотов используются внешние приложения: для mysql - mysqldump, для pgsql - pg_dump.

Для применения снепшота необходимо воспользоваться командой:
```
php mtool.php deploy
```
будет вызван диалог со списком доступных снепшотов и возможностью выбрать один из них для применения.

Также существуют такие варианты использования данной команды:

*  
```
php mtool.php deploy last
```
будет применен последний созданный снепшот.

* 
```
php mtool.php deploy ********_******_**
```
будет применен выбранный снепшот.


6. Справка
---

Для получения короткой справочной информации:
```
php mtool.php help
```

Вопросы, замечания, предложения: [dmitriy.britan@nixsolutions.com](mailto:dmitriy.britan@nixsolutions.com)