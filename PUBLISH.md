# Инструкция по публикации на GitHub и Packagist

## Подготовка к публикации

### 1. Проверка файлов

Убедитесь, что все необходимые файлы присутствуют:

- ✅ `composer.json` - конфигурация пакета
- ✅ `README.md` - документация на русском
- ✅ `README-en.md` - документация на английском
- ✅ `LICENSE` - MIT лицензия
- ✅ `CHANGELOG.md` - история изменений
- ✅ `CONTRIBUTING.md` - руководство для контрибьюторов
- ✅ `EXAMPLES.md` - примеры использования
- ✅ `.gitignore` - игнорируемые файлы
- ✅ `phpunit.xml` - конфигурация тестов
- ✅ `src/` - исходный код
- ✅ `tests/` - тесты
- ✅ `config/` - конфигурационные файлы

### 2. Проверка composer.json

Убедитесь, что в `composer.json` указаны:

```json
{
    "name": "tigusigalpa/yandexcloud-s3-php",
    "description": "PHP SDK для интеграции с Yandex Cloud Object Storage с поддержкой Laravel 8-12",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Igor Sazonov",
            "email": "sovletig@gmail.com"
        }
    ],
    "homepage": "https://github.com/tigusigalpa/yandexcloud-s3-php"
}
```

## Публикация на GitHub

### 1. Инициализация Git репозитория

```bash
cd public_html/packages/yandexcloud-s3-php
git init
git add .
git commit -m "Initial commit: Yandex Cloud S3 PHP SDK v1.0.0"
```

### 2. Создание репозитория на GitHub

1. Перейдите на https://github.com/new
2. Название репозитория: `yandexcloud-s3-php`
3. Описание: `PHP SDK for Yandex Cloud Object Storage with Laravel 8-12 support`
4. Публичный репозиторий
5. НЕ добавляйте README, .gitignore, LICENSE (они уже есть)
6. Создайте репозиторий

### 3. Подключение удаленного репозитория

```bash
git remote add origin https://github.com/tigusigalpa/yandexcloud-s3-php.git
git branch -M main
git push -u origin main
```

### 4. Создание релиза

1. Перейдите в раздел "Releases" на GitHub
2. Нажмите "Create a new release"
3. Tag version: `v1.0.0`
4. Release title: `v1.0.0 - Initial Release`
5. Описание:

```markdown
## Yandex Cloud S3 PHP SDK v1.0.0

Первый релиз полнофункционального PHP SDK для Yandex Cloud Object Storage.

### Основные возможности

- ✅ S3-совместимый API через AWS SDK for PHP
- ✅ Поддержка Laravel 8-12
- ✅ REST API управление бакетами
- ✅ Управление IAM ролями и доступом
- ✅ Автоматическое управление токенами
- ✅ Presigned URLs
- ✅ Полная документация на русском и английском

### Установка

```bash
composer require tigusigalpa/yandexcloud-s3-php
```

### Документация

- [README на русском](README.md)
- [README in English](README-en.md)
- [Примеры использования](EXAMPLES.md)

### Требования

- PHP >= 8.0
- Laravel >= 8.0 (опционально)
```

6. Нажмите "Publish release"

## Публикация на Packagist

### 1. Регистрация на Packagist

1. Перейдите на https://packagist.org/
2. Зарегистрируйтесь или войдите через GitHub
3. Подтвердите email

### 2. Отправка пакета

1. Нажмите "Submit" в верхнем меню
2. Введите URL репозитория: `https://github.com/tigusigalpa/yandexcloud-s3-php`
3. Нажмите "Check"
4. Если все в порядке, нажмите "Submit"

### 3. Настройка автоматического обновления

1. Перейдите в настройки пакета на Packagist
2. Скопируйте URL для GitHub webhook
3. Перейдите в настройки репозитория на GitHub
4. Settings → Webhooks → Add webhook
5. Вставьте URL из Packagist
6. Content type: `application/json`
7. Выберите события: "Just the push event"
8. Сохраните webhook

Теперь при каждом push в репозиторий Packagist будет автоматически обновляться.

## Проверка установки

После публикации проверьте установку:

```bash
composer require tigusigalpa/yandexcloud-s3-php
```

## Обновление версий

### Для новой версии:

1. Обновите `CHANGELOG.md`
2. Обновите версию в коде (если есть)
3. Создайте commit:

```bash
git add .
git commit -m "Release v1.1.0"
git push
```

4. Создайте новый tag:

```bash
git tag -a v1.1.0 -m "Version 1.1.0"
git push origin v1.1.0
```

5. Создайте релиз на GitHub
6. Packagist обновится автоматически через webhook

## Badges для README

Добавьте badges в README.md:

```markdown
[![Latest Version](https://img.shields.io/packagist/v/tigusigalpa/yandexcloud-s3-php.svg)](https://packagist.org/packages/tigusigalpa/yandexcloud-s3-php)
[![Total Downloads](https://img.shields.io/packagist/dt/tigusigalpa/yandexcloud-s3-php.svg)](https://packagist.org/packages/tigusigalpa/yandexcloud-s3-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![PHP: 8.0+](https://img.shields.io/badge/PHP-8.0+-blue.svg)](https://www.php.net/)
[![Laravel: 8-12](https://img.shields.io/badge/Laravel-8--12-blue.svg)](https://laravel.com/)
```

## Продвижение

### 1. Социальные сети и сообщества

- Опубликуйте в Twitter/X с хештегами #PHP #Laravel #YandexCloud
- Поделитесь в Reddit: r/PHP, r/laravel
- Опубликуйте на dev.to или Medium
- Добавьте в awesome-списки на GitHub

### 2. Документация

- Создайте wiki на GitHub с дополнительными примерами
- Добавьте видео-туториалы (опционально)
- Создайте демо-проект

### 3. Поддержка

- Отвечайте на issues
- Принимайте pull requests
- Обновляйте документацию по запросам пользователей

## Чеклист перед публикацией

- [ ] Все тесты проходят
- [ ] Документация полная и актуальная
- [ ] CHANGELOG.md обновлен
- [ ] composer.json корректен
- [ ] LICENSE файл присутствует
- [ ] .gitignore настроен
- [ ] README содержит примеры использования
- [ ] Код соответствует PSR-12
- [ ] Нет hardcoded credentials
- [ ] Версия указана корректно

## Полезные ссылки

- [Packagist](https://packagist.org/)
- [GitHub](https://github.com/)
- [Composer Documentation](https://getcomposer.org/doc/)
- [Semantic Versioning](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)
