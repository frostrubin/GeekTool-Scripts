#!/bin/bash
# This Script allocates a part of the RAM as a normal hard drive.
# This makes for super-fast file access on this drive, but comes at the cost
# of complete data loss on power-out. So copy everything back over after accessing
# the fast-disk.

# You have to multiply the amount of MB you wish to allocate with 2000.
# The command below creates a RAM disk with a size of 50MB.
if [ ! -d "/Volumes/ramdisk" ]; then
    diskutil erasevolume HFS+ "ramdisk" `hdiutil attach -nomount ram://100000`
fi


# Here are the steps one after another:
# Make part of RAM a Block device
# hdid -nomount ram://4096

# Initialize file system
# newfs_hfs /dev/disk1

# Mount RAM disk
# diskutil mount /dev/disk1

# Unmount RAM disk
# hdiutil detach /dev/disk1
