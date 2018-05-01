#!/bin/bash

echo Generating table of contents
php build-toc.php > toc.json
echo Generating book.html
php book.php > book.html
echo Converting to PDF
prince -s css/book.css book.html -o api-security.pdf
#rm book.html

echo Done
