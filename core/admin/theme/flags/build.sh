#!/bin/sh

# ImageMagick
for rep in 32 48; do
	convert "$rep/*.png" -append "flags-$rep.png"
done
