update:
	git ls-files | grep -Ev 'justfile|robots\.txt|favicon\.ico|\.git.*' | xargs rm -f
	find . -type d -empty -delete
	curl -s https://wordpress.org/latest.tar.gz | tar -xz --strip-components=1
	rm -rf wp-content/plugins/{akismet,hello.php}
	curl -so wp-cli https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	chmod +x wp-cli

deploy:
	./wp-cli core update-db

# vim: set ft=make :
