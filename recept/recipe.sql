
use robsite;

CREATE TABLE viewed (
  viewid int(9) NOT NULL auto_increment,
  viewdate int(8) NOT NULL default '',
  receptid int(9) NOT NULL default '1',
  PRIMARY KEY  (viewid)
);

