.PHONY: default
default: main

main: chatgpt.zip

chatgpt.zip: assets/* language/* media/* tmpl/* chatgpt.php chatgpt.xml
	zip -rq9 chatgpt.zip assets language media tmpl chatgpt.php chatgpt.xml
