.PHONY: dev stop
.DEFAULT_GOAL = help

CONTAINERS = `docker ps -a -q`

help: ## List all available commands
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-10s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## LAUNCH RULES
##

dev: ## Launch docker containers in development
	docker-compose up

launch: 
	docker exec -it aubind97-php-validator /bin/sh

stop: ## Stop all running docker containers
	docker stop $(CONTAINERS)

test: 
	composer run-script test