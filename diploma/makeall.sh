#!/bin/sh

cp back.pdf output/
cd output
for x in *.tex; do rubber -d $x; done
cd ..
