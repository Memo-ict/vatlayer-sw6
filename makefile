#
# Makefile
#

.PHONY: help
.DEFAULT_GOAL := help

PLUGIN_VERSION=`php -r 'echo json_decode(file_get_contents("MemoVatlayer6/composer.json"))->version;'`

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# ------------------------------------------------------------------------------------------------------------

release: ## Creates a new ZIP package
	@cd .. && zip -qq -r -0 MemoVatlayer6-$(PLUGIN_VERSION).zip MemoVatlayer6/ -x '*.git*' '*.reports*' '*.travis.yml*' '*/tests*' '*/makefile' '*.DS_Store'
