#!/bin/sh

cd $(dirname $0)

# ImageMagick
for rep in $(ls -d); do
	convert "$rep/*.png" -append "flags-$rep.png"
done

cd ${OLDPWD}
