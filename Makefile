VERSION?=latest

export DOCKER_TAG=${VERSION}
DOCKER_IMAGE=npopov-karma8-test
MIGRATION_USERS?=1000
WORKER_ID?=1000
CREATE_CHECK_EMAIL_TASKS_LIMIT?=1000

.PHONY: up
up: ## Up service
	@docker-compose up -d

.PHONY: down
up: ## Up service
	@docker-compose down

.PHONY: run-migration
run-migration: ## Run recreate db data and fixtures
	@docker-compose run -e --rm MIGRATION_USERS=${MIGRATION_USERS} migration

.PHONY: run-create-check-email-tasks
run-create-check-email-tasks: ## Run create tasks for checking emails
	@docker-compose run --rm -e LIMIT=${CREATE_CHECK_EMAIL_TASKS_LIMIT} php /app/check-email/create-check-email-task.php

.PHONY: run-check-email-tasks-worker
run-check-email-tasks-worker: ## Run worker for checking emails
	@docker-compose run --rm -e WORKER_ID=${WORKER_ID} php /app/check-email/check-email-worker.php

.PHONY: run-create-send-email-tasks
run-create-send-email-tasks: ## Run create tasks for sending emails
	@docker-compose run --rm -e LIMIT=${CREATE_CHECK_EMAIL_TASKS_LIMIT} php /app/send-email/create-send-email-task.php

.PHONY: run-send-email-worker
run-send-email-worker: ## Run worker for checking emails
	@docker-compose run --rm -e WORKER_ID=${WORKER_ID} php /app/send-email/send-email-worker.php

.PHONY: run-supervisord
run-supervisord: ## Run supervisor
	@docker-compose run --rm supervisor

.PHONY: start-supervisord-create-check-email-task
start-supervisord-create-check-email-task: ## Start create tasks for checking emails in supervisor
	@docker-compose exec -it supervisor sh -c "supervisorctl start create-check-email-task:*"

.PHONY: start-supervisord-check-email-workers
start-supervisord-check-email-workers: ## Start workers for checking emails in supervisor
	@docker-compose exec -it supervisor sh -c "supervisorctl start check-email-worker:*"

.PHONY: start-supervisord-create-send-email-task
start-supervisord-create-send-email-task: ## Start create tasks for checking emails in supervisor
	@docker-compose exec -it supervisor sh -c "supervisorctl start create-send-email-task:*"

.PHONY: start-supervisord-send-email-workers
start-supervisord-send-email-workers: ## Start workers for checking emails
	@docker-compose exec -it supervisor sh -c "supervisorctl start send-email-worker:*"

.PHONY: supervisor-status
supervisor-status: ## Check status of programs in supervisor
	@docker-compose exec -it supervisor sh -c "supervisorctl status"

.PHONY: run-tests
run-tests: ## Run recreate db data and fixtures
	@docker-compose -f docker-compose.test.yaml run tests