#!/bin/bash

if [ ! -d "/Volumes/ramdisk" ]; then
    diskutil erasevolume HFS+ "ramdisk" `hdiutil attach -nomount ram://100000`
fi

