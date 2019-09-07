.EXPORT_ALL_VARIABLES:
.PHONY: ci-update ci-update-commit deploy

ci-update-commit:
	git config --local user.name "$${GH_NAME}"
	git config --local user.email "$${GH_EMAIL}"
	git add -A
	git commit -m"Update to WordPress $$(php -r 'require "wp-includes/version.php";echo $$wp_version;') and WP-CLI $$(./wp-cli cli version)"
	git remote add origin-push https://$${GH_USER}:$${GH_TOKEN}@github.com/$${GITHUB_REPOSITORY}.git
	git push --set-upstream origin-push master

ci-update:
	git ls-files | grep -Ev 'Makefile|robots\.txt|favicon\.ico|\.git.*' | xargs rm -f
	find . -type d -empty -delete
	curl -s https://wordpress.org/latest.tar.gz | tar -xz --strip-components=1
	curl -so wp-cli https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	chmod +x wp-cli
	git checkout master
	if ! git diff-index --quiet HEAD; then ${MAKE} ci-update-commit; fi

deploy:
	./wp-cli core update-db
