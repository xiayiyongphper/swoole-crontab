<?php
date_default_timezone_set('UTC');
Yii::setAlias('common', dirname(__DIR__));
Yii::setAlias('service', dirname(dirname(__DIR__)) . '/service');
Yii::setAlias('console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('generator', dirname(dirname(__DIR__)) . '/generator');