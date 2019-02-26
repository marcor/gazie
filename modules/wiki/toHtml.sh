#!/bin/bash


# Questo script genera un unico file html
# di tutte le pagine wiki
# che può essere letto ed inserito via web

# Lo script genera un file zip
# che si trova nella directory library

FILEHTML=GAzie.html
FILEZIP=GAzie-Documentation.zip

cd library

pandoc -f markdown -t html -o $FILEHTML 001..\ Sommario.md **/*.md

zip -vr $FILEZIP $FILEHTML _contenuti/

rm $FILEHTML

cd ..


