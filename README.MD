Запуск

Поднять окружение
```bash
make up
```

Запустить демона по созданию задач на проверку емейлов
```bash
make start-supervisord-create-check-email-task
```

Запустить воркеров на проверку емейлов
```bash
make start-supervisord-check-email-workers
```

Запустить демона по созданию задач на отправку емейлов
```bash
make start-supervisord-create-send-email-task
```

Запустить воркеров на отправку емейлов
```bash
make start-supervisord-send-email-workers
```

TODO

- Тесты
- Использовать RabbitMQ как очередь
- Попробовать docker scale для воркеров, кронов вместо supervisor-a
- Попробовать блокировку задачи в бд без поля воркер
- Попробовать отдельную очередь для зафейленных задач для ретраев