ALTER TABLE `dis_stream` ADD `str_mousewup` TEXT NOT NULL AFTER `str_functiond` ,ADD `str_mousewdown` TEXT NOT NULL AFTER `str_mousewup` ;
ALTER TABLE `dis_community` ADD `com_css` TEXT NOT NULL ;
CREATE TABLE IF NOT EXISTS `dis_font` (  `fnt_name` text NOT NULL,  `fnt_file` text NOT NULL,  `fnt_about` text NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `dis_font` (`fnt_name`, `fnt_file`, `fnt_about`) VALUES ('Gentium', 'Gentium.swf', 'Gentium, by J. Victor Gaultney and Annie Olsen. Font license: OFL - http://scripts.sil.org/OFL'),('Free Universal', 'FreeUniversal.swf', 'Free Universal, by Stephen Wilson. Font license: OFL - http://scripts.sil.org/OFL'),('Marvel', 'Marvel.swf', 'Marvel, by Carolina Trebol. Font license: OFL - http://scripts.sil.org/OFL');
ALTER TABLE `dis_instance` ADD `ins_cssclass` TEXT NOT NULL AFTER `ins_textalign` ;
INSERT INTO `dis_options` (`opt_name` ,`opt_value`) VALUES ('CIRRUS', '');