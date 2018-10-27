CREATE TABLE `phppos_stock_package` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`package_by_id`  int(11) NULL ,
`package_by_type`  char(30) NULL ,
`package_code`  varchar(50) NULL ,
`package_slug`  varchar(50) NULL ,
PRIMARY KEY (`id`)
)
;