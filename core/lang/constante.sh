#!/bin/sh

for lang in de en es it nl oc pl pt ro ru; do 
	sed -E -i '/\);/d; /\$LANG/d' $lang/*.php
	sed -E -i "s/'(\w+)'/const \1/" $lang/*.php
	sed -E -i 's/,$/;/; s/=>/=/' $lang/*.php
done

