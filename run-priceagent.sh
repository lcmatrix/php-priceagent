#!/bin/bash
#
# Shell script to run price agent from a console
#
# version 0.2
#
##################################################

# set the path to the priceagent.php
export PATH_TO_PRICEAGENT=

cd $PATH_TO_PRICEAGENT
php -f priceagent.php
