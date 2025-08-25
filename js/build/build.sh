#!/bin/bash

bun build ./js/build/uppy.js --outdir ./js/bundle --minify && \
    rm ./js/bundle/uppy.min.css && \
    cat ./node_modules/@uppy/*/dist/*.min.css >> ./js/bundle/uppy.min.css

mkdir -p ./js/recordrtc &&
    cp ./node_modules/recordrtc/RecordRTC.min.js ./js/recordrtc

mkdir -p ./js/h5p-standalone && \
    cp -r ./node_modules/h5p-standalone/dist/{*.js,fonts,images,styles} ./js/h5p-standalone

rm -rf ./js/mathjax && \
    cp -r ./node_modules/mathjax ./js/mathjax

rm -rf ./js/video.js && \
    mkdir -p ./js/video.js && \
    cp -r ./node_modules/video.js/dist/{*.min.js,*.min.css,font,lang} ./js/video.js
