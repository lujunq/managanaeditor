ALTER TABLE `dis_font` ADD `fnt_type` TEXT NOT NULL ,ADD `fnt_for` TEXT NOT NULL ;
UPDATE dis_font SET fnt_for='flash' WHERE 1;