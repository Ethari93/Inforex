INSERT INTO `tagsets` (`tagset_id`, `name`) VALUES ('2', 'English'), ('3', 'German');
ALTER TABLE `reports` CHANGE `tokenization` `tokenization` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_polish_ci NULL DEFAULT NULL;