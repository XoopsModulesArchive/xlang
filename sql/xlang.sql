# $Id: xlang.sql,v 1.2 2007/12/19 17:10:24 ohwada Exp $

# =========================================================
# XOOPS Language Translration Support
# 2007-12-01 K.OHWADA
# =========================================================

#
# Table structure for table `xlang_group`
#

CREATE TABLE xlang_group (
  id   int(5) unsigned NOT NULL auto_increment,
  time int(5) unsigned NOT NULL default 0,
  dirname  varchar(255) NOT NULL default '',
  language varchar(255) NOT NULL default '',
  file     varchar(255) NOT NULL default '',
  word     varchar(255) NOT NULL default '',
  mail     varchar(255) NOT NULL default '',
  kind     tinyint(2) unsigned NOT NULL default 0,
  PRIMARY KEY (id),
  KEY time  (time),
  KEY dir   (dirname),
  KEY lang  (language),
  KEY file  (file),
  KEY word  (word),
  KEY mail  (mail),
  KEY kind  (kind)
) TYPE=MyISAM;

#
# Table structure for table `xlang_file`
#

CREATE TABLE xlang_file (
  id   int(5) unsigned NOT NULL auto_increment,
  gid  int(5) unsigned NOT NULL default 0,
  time int(5) unsigned NOT NULL default 0,
  f_content text NOT NULL,
  f_note    text NOT NULL,
  PRIMARY KEY (id),
  KEY gid  (gid),
  KEY time (time)
) TYPE=MyISAM;

#
# Table structure for table `xlang_word`
#

CREATE TABLE xlang_word (
  id   int(5) unsigned NOT NULL auto_increment,
  gid  int(5) unsigned NOT NULL default 0,
  time int(5) unsigned NOT NULL default 0,
  w_act tinyint(2) unsigned NOT NULL default '0',
  w_content text NOT NULL,
  w_note    text NOT NULL,
  PRIMARY KEY (id),
  KEY gid  (gid),
  KEY time (time),
  KEY act  (w_act)
) TYPE=MyISAM;

#
# Table structure for table `xlang_mail`
#

CREATE TABLE xlang_mail (
  id  int(5) unsigned NOT NULL auto_increment,
  gid int(5) unsigned NOT NULL default 0,
  time int(5) unsigned NOT NULL default 0,
  m_content text NOT NULL,
  m_note    text NOT NULL,
  PRIMARY KEY (id),
  KEY gid  (gid),
  KEY time (time)
) TYPE=MyISAM;

#
# Table structure for table `xlang_template`
#

CREATE TABLE xlang_template (
  id  int(5) unsigned NOT NULL auto_increment,
  gid int(5) unsigned NOT NULL default 0,
  time int(5) unsigned NOT NULL default 0,
  t_content text NOT NULL,
  PRIMARY KEY (id),
  KEY gid  (gid),
  KEY time (time)
) TYPE=MyISAM;

#
# Table structure for table `xlang_log`
#

CREATE TABLE xlang_log (
  id   int(5) unsigned NOT NULL auto_increment,
  gid  int(5) unsigned NOT NULL default 0,
  time int(5) unsigned NOT NULL default 0,
  uid  int(5) unsigned NOT NULL default 0,
  l_op tinyint(2) unsigned NOT NULL default 0,
  l_content  text NOT NULL,
  PRIMARY KEY (id),
  KEY gid  (gid),
  KEY time (time)
) TYPE=MyISAM;

