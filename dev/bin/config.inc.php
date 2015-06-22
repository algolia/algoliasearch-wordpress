<?php

$cfg['blowfish_secret'] = '';

$i = 1;

/* Authentication type */
$cfg['Servers'][$i]['auth_type'] = 'config';
/* Server parameters */
$cfg['Servers'][$i]['host'] = '127.0.0.1';
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['connect_type'] = 'tcp';
$cfg['Servers'][$i]['AllowNoPassword'] = true;

$cfg['Servers'][$i]['AllowRoot'] = true;

$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';

$cfg['Servers'][$i]['user'] = 'root';
$cfg['Servers'][$i]['password'] = 'P4ssw0rd';