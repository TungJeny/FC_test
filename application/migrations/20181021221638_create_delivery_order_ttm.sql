CREATE TABLE `phppos_delivery_order_ttm` (
`id`  int NOT NULL AUTO_INCREMENT ,
`month`  varchar(255) NULL,
`pt_code`  varchar(255) NULL ,
`pt_name`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`cost`  decimal NULL ,
`qty`  int NULL ,
`province`  text NULL ,
`type_model`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`delivery_date`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`delivery_reason`  text NULL ,
PRIMARY KEY (`id`)
)
;